<?php
require 'include/connect.php';

// ATTENDANCE
if (isset($_POST['fingerprint_id'])) {
    // check if student is enlisted or delisted
    $query = "SELECT `roll_no`
                FROM `users_all`
                WHERE `fingerprint_id` = ? AND `delist_date` IS NULL;";

    $users_all_arr = execCRUD($query, "i", $_POST['fingerprint_id']);

    if ( // student is delisted
        !($users_all_row = $users_all_arr->fetch_assoc())
    ) exit();
    
    // FOR THE MCU
    // student is enlisted, enough to greet the nigga "welcome"
    echo "login".$users_all_row['roll_no'];

    // check if student has already logged into the daily records
    $query = "SELECT *
                FROM `attendance_log`
                WHERE `roll_no` = ? AND `date` = CURRENT_DATE();";

    $attendance_log_arr = execCRUD($query, "i", $users_all_row['roll_no']);
    
    if ( // student student has already logged in
        !($attendance_log_row = $attendance_log_arr->fetch_assoc())
    ) exit();

    // otherwise, log student into the daily records
    $query = "INSERT INTO attendance_log (roll_no) VALUES (?)";

    $inserted_rows = execCRUD($query, "i", $attendance_log_row['roll_no']);

    // no rows inserted, something is wrong.
    if ($inserted_rows != 1) {
        die("Failure executing query: INSERT INTO attendance_log (roll_no) VALUES ("
        .$attendance_log_row['roll_no'].")");
    }
}

// ADD USER
else if (isset($_POST['Get_Fingerid'])) {
    
}

// ADD USER - LAST STEPS
else if (isset($_POST['confirm_id'])) {

}

// DELETE USER
else /*if (isset($_POST['DeleteID']))*/ { 
    
}
?>