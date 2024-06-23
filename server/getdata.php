<?php
require_once("include/connect.php");

enum Action {
    case Attendance;
    case Enlist;
    case Delist;
}

// to send to the ESP
$payload = "";

// get total enlisted users
$query = "SELECT COUNT(*) FROM `users_all` WHERE `delist_date` IS NULL";
$total = mysqli_fetch_assoc(mysqli_query($connect, $query))["COUNT(*)"];

// set up `analytics` for the day
if (!mysqli_num_rows(mysqli_query($connect, "SELECT `date` FROM `analytics` WHERE `date` = CURDATE()"))) {
    // today's date NOT ALREADY in table, insert today's date
    $query = "INSERT INTO `analytics` (`date`, `present`, `absent`, `dar`, `mar`)
                SELECT CURDATE(), 0, $total, FORMAT(0, 2), FORMAT(0, 2)
                WHERE CURDATE() NOT IN(SELECT `holiday_date` FROM `holidays_2024`)
                ";
                // AND (DAYOFWEEK(CURDATE())) != 1"; // SUNDAYS.
                // =================================
                // ^^^^ CHANGE THIS BEFORE PROD ^^^^
                // =================================

    if (!mysqli_query($connect, $query))
        exit(1);
}

// ATTENDANCE
if (isset($_POST["log-user-at"])) {
    // get roll number of student
    $query = "SELECT `roll_no`, `full_name`, `semester` FROM `users_all` WHERE `fingerprint_id` = ?";
    $logged_rs = execCRUD($query, "i", $_POST["log-user-at"]);
    $logged_row = $logged_rs->fetch_assoc();
    // Send either `"L" . $_POST["log-user-at"] . "R" . $logged_row["roll_no"]`
    // or simply, `"R" . $logged_row["roll_no"]`
    $payload .= "R" . $logged_row["roll_no"];

    // check if student has already logged into the daily records
    // $logged_rs will have always have one row. A $_POST["log-user-at"]
    // sent by the ESP will always have a corresponding `users_all`
    // (`fingerprint_id` - 1) WHERE `fingerprint_id` = $_POST["log-user-at"]
    $query = "SELECT * FROM `attendance_log` WHERE `roll_no` = ? AND `date` = CURDATE();";
    if (mysqli_num_rows(execCRUD($query, "i", $logged_row["roll_no"]))) { /* == 1 */
        // student student has already logged in
        echo $payload;
        exit(0);
    }

    // otherwise, log student into the daily records
    $query = "INSERT INTO attendance_log (roll_no, full_name, semester) VALUES (?, ?, ?)";
    $inserted_rows = execCRUD($query, "isi", $logged_row["roll_no"], $logged_row["full_name"], $logged_row["semester"]);

    if ($inserted_rows != 1)
        // no rows inserted, something is wrong.
        exit(1);
    
    set_analytics(Action::Attendance);

    echo $payload;
    exit(0);
}

$result = mysqli_query($connect, "SELECT * FROM `esp_edu_state` WHERE `success` IS NULL");
if (mysqli_num_rows($result) != 1)
    exit;

$esp_edu_state = mysqli_fetch_assoc($result);

// Server sends (E|D|U){fingerprint_id}R{roll_no} of student.
// This `if` branch is connected with the next `else` branch where ESP
// sends confirmation regarding whether the enlisting, delisting, or update
// operation took place sucessfully.
if (isset($_POST["check-edu"])) {
    $payload .=
        $esp_edu_state["mode"] . $esp_edu_state["fingerprint_id"]
        . "R" . $esp_edu_state["roll_no"];

    echo $payload;
    exit(0);

} else switch ($_POST["confirm-edu"]) {
    // Successful Enlist / Delist / Update
    case 'ok':
        // modify analytics first
        switch ($esp_edu_state["mode"]) {
            case 'E':
                set_analytics(Action::Enlist);
                break;
                
            case 'D':
                set_analytics(Action::Delist);
                break;
        }

        // Set success flag to true
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `success` = 1");
        // Finally let `ajax/students.php` control pass through
        // `while(...) sleep(1);` part
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `server_block` = 0");
        exit(0);

    // Unsuccessful Enlist / Delist / Update
    case 'err':
        // revert changes made to `users_all` in case of failure at esp side.
        switch ($esp_edu_state["mode"]) {
                // failure during enlist
            case 'E':
                // revert "INSERT INTO users_all"
                $query = "DELETE FROM `users_all` WHERE `roll_no` = ?";
                $result = execCRUD($query, "i", $esp_edu_state["roll_no"]);
                break;

                // failure during delist
            case 'D':
                // revert "SET `delist_date` = curdate()"
                $query = "UPDATE `users_all` SET `delist_date` = NULL WHERE `roll_no`=?";
                $result = execCRUD($query, "i", $esp_edu_state["roll_no"]);
                break;
        }

        // Set success flag to false
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `success` = 0");
        // Finally let `ajax/students.php` control pass through
        // `while(...) sleep(1);` part
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `server_block` = 0");
        exit(0);
}

function set_analytics(Action $action) {
    global $connect, $total, $esp_edu_state;

    switch ($action) {
        case Action::Attendance:
            $dar_today = daily_attendance_rate_today(Action::Attendance);
            $mar_today = monthly_attendance_rate_today($dar_today);

            $query = "UPDATE `analytics` SET
                        `present` = `present` + 1,
                        `absent` = `absent` - 1,
                        `dar` = FORMAT($dar_today, 2),
                        `mar` = FORMAT($mar_today, 2)
                        WHERE `date` = CURDATE()";

            if (!mysqli_query($connect, $query))
                exit(1);
            
            break;

        case Action::Enlist:
            $dar_today = daily_attendance_rate_today(Action::Enlist);
            $mar_today = monthly_attendance_rate_today($dar_today);

            $query = "UPDATE `analytics` SET
                        `absent` = `absent` + 1,
                        `dar` = FORMAT($dar_today, 2),
                        `mar` = FORMAT($mar_today, 2)
                        WHERE `date` = CURDATE()";

            if (!mysqli_query($connect, $query))
                exit(1);

            break;
        
        case Action::Delist:
            $dar_today = daily_attendance_rate_today(Action::Delist);
            $mar_today = monthly_attendance_rate_today($dar_today);

            // check if delisted student was present or absent the current day
            $query = "SELECT COUNT(*) FROM `attendance_log` WHERE `roll_no` = ? AND `date` = CURDATE()";
            $is_user_present_today = mysqli_fetch_assoc(execCRUD($query, "i", $esp_edu_state["roll_no"]))["COUNT(*)"];

            $query = "UPDATE `analytics` SET" . (
                        $is_user_present_today ?
                        " `present` = `present` - 1, " :
                        " `absent` = `absent` - 1, "
                        ) . "`dar` = FORMAT($dar_today, 2),
                        `mar` = FORMAT($mar_today, 2)
                        WHERE `date` = CURDATE()";

            if (!mysqli_query($connect, $query))
                exit(1);

            break;
    }
}

function daily_attendance_rate_today(Action $action) {
    global $total, $connect, $esp_edu_state;

    $query = "SELECT `present`, `absent` FROM `analytics` WHERE `date` = CURDATE()";
    $analytics_today = mysqli_fetch_assoc(mysqli_query($connect, $query));

    switch ($action) {
        case Action::Attendance:
            return 100 * ($analytics_today["present"] + 1) / $total;
            break;
        
        case Action::Enlist:
            return 100 * $analytics_today["present"] / $total;
            break;
        
        case Action::Delist:
            // check if delisted student was present or absent the current day
            $query = "SELECT COUNT(*) FROM `attendance_log` WHERE `roll_no` = ? AND `date` = CURDATE()";
            $is_user_present_today = mysqli_fetch_assoc(execCRUD($query, "i", $esp_edu_state["roll_no"]))["COUNT(*)"];

            return 100 * ($is_user_present_today ? $analytics_today["present"] - 1 : $analytics_today["present"]) / $total;
            break;
    }
}

function monthly_attendance_rate_today($dar_today) {
    global $connect;

    // get daily attendance rates of all days for current month
    $query = "SELECT `dar` FROM `analytics`
        WHERE MONTH(`date`) = MONTH(CURDATE())
        AND NOT `date` = CURDATE()";
    $result = mysqli_query($connect, $query);

    $attendance_days = mysqli_num_rows($result);
    // for the currengt day
    ++$attendance_days;
    
    $dar_total = $dar_today;
    $dars_this_month = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    foreach ($dars_this_month as $this_day_dar) {
        $dar_total += $this_day_dar['dar'];
    }

    return $dar_total / $attendance_days;
}
