<?php
require("./include/connect.php");
require("../include/essential.php");
adminLogin();

// ATTENDANCE
if (isset($_POST["fingerprint_id"])) {
    // get roll number of student
    $query = "SELECT `roll_no` FROM `users_all` WHERE `fingerprint_id` = ?;";
    $users_all_rs = execCRUD($query, "i", $_POST["fingerprint_id"]);
    $users_all_row = $users_all_rs->fetch_assoc();

    // check if student has already logged into the daily records
    $query = "SELECT * FROM `attendance_log`
                WHERE `roll_no` = ? AND `date` = CURDATE();";
    // $users_all_rs will have always have one row. A $_POST["fingerprint_id"]
    // sent by the mcu will always have a corresponding `users_all`
    // (`fingerprint_id`) WHERE `fingerprint_id` = $_POST["fingerprint_id"]
    $attendance_log_rs = execCRUD($query, "i", $users_all_row["roll_no"]);

    if ($attendance_log_rs->num_rows /* == 1 */) {
        // student student has already logged in
        echo "login" . $users_all_row["roll_no"];
        exit($users_all_row["roll_no"] . " already logged in.");
    }

    // otherwise, log student into the daily records
    $query = "INSERT INTO attendance_log (roll_no) VALUES (?)";
    $inserted_rows = execCRUD($query, "i", $users_all_row["roll_no"]);

    if ($inserted_rows /* != 1 */)
        // no rows inserted, something is wrong.
        die("Failure executing query: "
            . "INSERT INTO attendance_log (roll_no) VALUES ("
            . $users_all_row["roll_no"] . ")");

    echo "login" . $users_all_row["roll_no"];
    exit($users_all_row["roll_no"] . " logged in just now.");
}

// ADD USER
else if (isset($_POST["Get_Fingerid"]) && !empty($_SESSION["add-roll-no"])) {
    $last_enlist_id_arr = mysqli_query($connect, "SELECT LAST_INSERT_ID();");
    echo "add-id" . $last_enlist_id_arr->fetch_assoc()["LAST_INSERT_ID()"];
}

// ADD USER - LAST STEPS
else if (isset($_POST["confirm_id"]) && !empty($_SESSION["add-roll-no"])) {
    unset($_SESSION["add-roll-no"]);
    echo "unset(\$_SESSION[\"add-roll-no\"])";
}

// DELETE USER
else if (/* isset($_POST["DeleteID"]) && */!empty($_SESSION["rm-finger-id"])) {
    echo "del-id" . $_SESSION["rm-finger-id"];
    unset($_SESSION["rm-finger-id"]);

    // ajax backend for user delete (move to concerned file later)
    // hrik-das
    $query = "UPDATE `users_all` SET `delist_date` = CURDATE() WHERE `roll_no` = ?";
    $result = execCRUD($query, "i", $filterData["roll"]);

    $query = "SELECT `fingerprint_id` FROM `users-all` WHERE `roll_no` = ?
                AND `delist_date` = CURDATE();";
    $last_delist_id_rs = execCRUD($query, "i", $filterData["roll"]);

    $_SESSION["rm-finger-id"] = $last_delist_id_rs->fetch_assoc()["fingerprint_id"];

    while ($_SESSION["rm-finger-id"]) {
        $_SESSION["sleep-time"] = sleep(1);
        if ($_SESSION["sleep-time"] /* == WAIT_IO_COMPLETION (synchapi.h) */)
            die("Couldn't sleep(1): WAIT_IO_COMPLETION");
    }

    echo $result;
}
