<?php
require_once("./include/connect.php");
require_once("../include/essential.php");
adminLogin();

// to send to the ESP
$payload = "";

// ATTENDANCE
if (isset($_POST["log-user-at"])) {
    // get roll number of student
    $query = "SELECT `roll_no` FROM `users_all` WHERE `fingerprint_id` = ?;";
    $users_all_rs = execCRUD($query, "i", $_POST["log-user-at"]);
    $users_all_row = $users_all_rs->fetch_assoc();
    // Send either
    // `"L" . $_POST["log-user-at"] . "R" . $users_all_row["roll_no"]
    // or simply,
    // "R" . $users_all_row["roll_no"]
    $payload .= "R" . $users_all_row["roll_no"];

    // check if student has already logged into the daily records
    $query = "SELECT * FROM `attendance_log`
                WHERE `roll_no` = ? AND `date` = CURDATE();";
    // $users_all_rs will have always have one row. A $_POST["log-user-at"]
    // sent by the ESP will always have a corresponding `users_all`
    // (`fingerprint_id`) WHERE `fingerprint_id` = $_POST["log-user-at"]
    $attendance_log_rs = execCRUD($query, "i", $users_all_row["roll_no"]);
    if ($attendance_log_rs->num_rows /* == 1 */) {
        // student student has already logged in
        echo $payload;
        exit($users_all_row["roll_no"] . " already logged in.");
    }

    // otherwise, log student into the daily records
    $query = "INSERT INTO attendance_log (roll_no) VALUES (?)";
    $inserted_rows = execCRUD($query, "i", $users_all_row["roll_no"]);

    if ($inserted_rows != 1)
        // no rows inserted, something is wrong.
        exit("Failure executing query: "
            . "INSERT INTO attendance_log (roll_no) VALUES ("
            . $users_all_row["roll_no"] . ")");

    echo $payload;
    exit($users_all_row["roll_no"] . " logged in just now.");
}

// Response to when ESP sends `check-edu=` for checking if a user is to be
// enlisted, delisted, or updated. Server sends (E|D|U){fingerprint_id}R{roll_no}
// of student. This `else if` branch is connected with the next branch where ESP
// sends confirmatin regarding whether the enlisting, delisting, or update
// operation took place sucessfully.
else if (isset($_POST["check-edu"]) && !empty($_SESSION["esp-edu"])) {
    $payload .= $_SESSION["esp-edu"]["mode"] . $_SESSION["esp-edu"]["id"] .
                "R" . $_SESSION["esp-edu"]["roll"];
    
    echo $payload;
}

// Successful Enlist / Delist / Update
else if (($_POST["confirm-edu"]) == "ok") {
    // COMMIT TRANSACTION
    mysqli_query($connect, "COMMIT;");
    // Set success flag to true
    $_SESSION["esp-edu-success"] = true;
    // Finally let `ajax/students.php` control pass through
    // `while($_SESSION["esp-edu"]) sleep(1);` part
    unset($_SESSION["esp-edu"]);
}

// Unsuccessful Enlist / Delist / Update
else if (($_POST["confirm-edu"]) == "err") {
    // ROLLBACK TRANSACTION
    mysqli_query($connect, "ROLLBACK;");
    // Set success flag to false
    $_SESSION["esp-edu-success"] = false;
    // Finally let `ajax/students.php` control pass through
    // `while($_SESSION["esp-edu"]) sleep(1);` part
    unset($_SESSION["esp-edu"]);
}
?>
