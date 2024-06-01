<?php
require_once("include/connect.php");

// to send to the ESP
$payload = "";

// ATTENDANCE
if (isset($_POST["log-user-at"])) {
    // get roll number of student
    $query = "SELECT `roll_no` FROM `users_all` WHERE `fingerprint_id` = ?;";
    $logged_rs = execCRUD($query, "i", $_POST["log-user-at"]);
    $logged_roll = $logged_rs->fetch_assoc()["roll_no"];
    // Send either
    // `"L" . $_POST["log-user-at"] . "R" . $logged_roll`
    // or simply,
    // `"R" . $logged_roll`
    $payload .= "R" . $logged_roll;

    // check if student has already logged into the daily records
    $query = "SELECT * FROM `attendance_log`
                WHERE `roll_no` = ? AND `date` = CURDATE();";
    // $logged_rs will have always have one row. A $_POST["log-user-at"]
    // sent by the ESP will always have a corresponding `users_all`
    // (`fingerprint_id`) WHERE `fingerprint_id` = $_POST["log-user-at"]
    $attendance_log_rs = execCRUD($query, "i", $logged_roll);
    if ($attendance_log_rs->num_rows /* == 1 */) {
        // student student has already logged in
        echo $payload;
        exit(0);
    }

    // otherwise, log student into the daily records
    $query = "INSERT INTO attendance_log (roll_no) VALUES (?)";
    $inserted_rows = execCRUD($query, "i", $logged_roll);

    if ($inserted_rows != 1)
        // no rows inserted, something is wrong.
        exit(1);

    echo $payload;
    exit(0);
}

$esp_edu_state_rs = mysqli_query($connect, "SELECT * FROM `esp_edu_state`");

if (mysqli_num_rows($esp_edu_state_rs) != 1)
    exit(1);

// Server sends (E|D|U){fingerprint_id}R{roll_no} of student.
// This `if` branch is connected with the next `else` branch where ESP
// sends confirmation regarding whether the enlisting, delisting, or update
// operation took place sucessfully.
if (isset($_POST["check-edu"])) {
    $esp_edu_state_row = mysqli_fetch_assoc($esp_edu_state_rs);
    $payload .=
        $esp_edu_state_row["mode"] . $esp_edu_state_row["fingerprint_id"]
        . "R" . $esp_edu_state_row["roll_no"];
    
    echo $payload;
    exit(0);
}

else switch ($_POST["confirm-edu"]) {
    // Successful Enlist / Delist / Update
    case 'ok':
        mysqli_commit($connect);
        // Set success flag to true
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `success` = 1");
        // Finally let `ajax/edu.php` control pass through
        // `while(...) sleep(1);` part
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `server_block` = 0");
        exit(0);

    // Unsuccessful Enlist / Delist / Update
    case 'err':
        mysqli_rollback($connect);
        // Set success flag to false
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `success` = 0");
        // Finally let `ajax/edu.php` control pass through
        // `while(...) sleep(1);` part
        mysqli_query($connect, "UPDATE `esp_edu_state` SET `server_block` = 0");
        exit(0);
}
?>
