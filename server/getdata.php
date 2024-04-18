<?php
require("./include/connect.php");
require("../include/essential.php");
adminLogin();

// ATTENDANCE
if (isset($_POST["fingerprint_id"])) {
    // get roll number of student
    $query = "SELECT `roll_no`
                FROM `users_all`
                WHERE `fingerprint_id` = ?;";
    $users_all_arr = execCRUD($query, "i", $_POST["fingerprint_id"]);
    // check if student has already logged into the daily records
    $query = "SELECT *
                FROM `attendance_log`
                WHERE `roll_no` = ? AND `date` = CURDATE();";
    // $users_all_arr will have always have one row. A $_POST["fingerprint_id"] sent by the mcu will
    // always have a `users_all` (`fingerprint_id`) WHERE `fingerprint_id` = $_POST["fingerprint_id"]
    $attendance_log_arr = execCRUD($query, "i", $users_all_arr->fetch_assoc()["roll_no"]);

    if ( // student student has already logged in
        !($attendance_log_row = $attendance_log_arr->fetch_assoc())
    ) {
        echo "login" . $users_all_row["roll_no"];
        exit($users_all_row["roll_no"] . " already logged in.");
    }

    // otherwise, log student into the daily records
    $query = "INSERT INTO attendance_log (roll_no) VALUES (?)";
    $inserted_rows = execCRUD($query, "i", $attendance_log_row["roll_no"]);

    // no rows inserted, something is wrong.
    if ($inserted_rows != 1) {
        die("Failure executing query: "
            . "INSERT INTO attendance_log (roll_no) VALUES ("
            . $attendance_log_row["roll_no"] . ")");
    }

    echo "login" . $users_all_row["roll_no"];
    exit($users_all_row["roll_no"] . " logged in just now.");
}

// ADD USER
else if (isset($_POST["Get_Fingerid"]) && !empty($_SESSION["adding-student"])) {
    $last_enlist_id_arr = mysqli_query($GLOBALS["connect"], "SELECT LAST_INSERT_ID();");
    echo "add-id" . $last_enlist_id_arr->fetch_assoc()["LAST_INSERT_ID()"];
}

// ADD USER - LAST STEPS
else if (isset($_POST["confirm_id"]) && !empty($_SESSION["adding-student"])) {
    unset($_SESSION["adding-student"]);
    echo "unset(\$_SESSION[\"adding-student\"])";
}

// DELETE USER
else if (/*isset($_POST["DeleteID"]) &&*/ !empty($_SESSION["deleting-student"])) {
    echo "del-id" . $_SESSION["deleting-student"];
    unset($_SESSION["deleting-student"]);

    // ajax backend for user delete (move to concerned file later)
    // hrik-das
    $query = "UPDATE `users_all` SET `delist_date` = CURDATE() WHERE `roll_no` = ?";
    $result = execCRUD($query, "i", $filterData["roll"]);

    $query = "SELECT `fingerprint_id` FROM `users-all` WHERE `roll_no` = ? AND `delist_date` = CURDATE();";
    $last_delist_id_arr = execCRUD($query, "i", $roll_no_from_form);

    $_SESSION["deleting-student"] = $last_delist_id_arr->fetch_assoc()["fingerprint_id"];
    
    while ($_SESSION["deleting-student"]) {
        $_SESSION["sleep-time"] = sleep(1);
        // $_SESSION["sleep-time"] == 192 (value of WAIT_IO_COMPLETION constant within the Windows API)
        if ($_SESSION["sleep-time"])
            die("Couldn't sleep(1): WAIT_IO_COMPLETION");
    }

    echo $result;
}
