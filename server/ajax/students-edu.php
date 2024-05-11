<?php
// ==========================================================================================================
// ENLIST
// ==========================================================================================================
if (isset($_POST["add-student"])) {
    $filterData = filteration($_POST);

    // get `fingerprint_id` of same student
    $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no` = ?";
    $enlist_id_rs = execCRUD($query, "i", $filterData["roll"]);
    $ESP_E_id = mysqli_fetch_assoc($enlist_id_rs)["fingerprint_id"];

    $query = "INSERT INTO `esp_edu_state` (`mode`, `fingerprint_id`, `roll_no`, `server_block`)
                VALUES (?, ?, ?, ?)";
    $result = execCRUD($query, "siii", "E", $ESP_E_id, $filterData["roll"], 1);

    // START TRANSACTION (MIGHT COMMIT OR ROLLBACK)
    mysqli_begin_transaction($connect);

    // add student data to `users_all` table
    $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`)
                VALUES (?, ?, ?, ?, ?, ?)";
    $result = execCRUD(
        $query,
        "isssis",
        $filterData["roll"],
        $filterData["name"],
        $filterData["email"],
        $filterData["phone"],
        $filterData["sem"],
        $filterData["date"]
    );

    // fingerprint is being set at ESP-side
    while (mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT `server_block` FROM `esp_edu_state`"
        )
    )["server_block"])
        sleep(1);
    // while(){} exits when ESP sends cofirmation

    // response finally sent to ajax call fom frontend
    echo mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT `success` FROM `esp_edu_state`"
        )
    )["success"];

    mysqli_query($connect, "DELETE FROM `esp_edu_state`");
}

// ==========================================================================================================
// DELIST
// ==========================================================================================================
if (isset($_POST["remove-student"])) {
    $filterData = filteration($_POST);
    
    // get `fingerprint_id` of same student
    $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no`=? AND `delist_date`=CURDATE()";
    $delist_id_rs = execCRUD($query, "i", $filterData["roll"]);
    $ESP_D_id = mysqli_fetch_assoc($delist_id_rs)["fingerprint_id"];

    $query = "INSERT INTO `esp_edu_state` (`mode`, `fingerprint_id`, `roll_no`, `server_block`)
                VALUES (?, ?, ?, ?)";
    $result = execCRUD($query, "siii", "D", $ESP_D_id, $filterData["roll"], 1);

    // START TRANSACTION (MIGHT COMMIT OR ROLLBACK)
    mysqli_begin_transaction($connect);

    // delist student
    $query = "UPDATE `users_all` SET `delist_date`=CURDATE() WHERE `roll_no`=?";
    $result = execCRUD($query, "i", $filterData["roll"]);


    // fingerprint is being set at ESP-side
    while (mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT `server_block` FROM `esp_edu_state`"
        )
    )["server_block"])
        sleep(1);
    // while(){} exits when ESP sends cofirmation

    // response finally sent to ajax call fom frontend
    echo mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT `success` FROM `esp_edu_state`"
        )
    )["success"];

    mysqli_query($connect, "DELETE FROM `esp_edu_state`");
}

// ==========================================================================================================
// UPDATE DETAILS
// ==========================================================================================================
if (isset($_POST["update-details"])) {
    $filterData = filteration($_POST);
    $query = "UPDATE `users_all`
                SET `roll_no`=?, `full_name`=?, `email`=?, `contact`=?, `semester`=?,`enlist_date`=?
                WHERE `fingerprint_id`=?";
    $result = execCRUD(
        $query,
        "isssisi",
        $filterData["roll"],
        $filterData["name"],
        $filterData["email"],
        $filterData["phone"],
        $filterData["sem"],
        $filterData["date"],
        $filterData["id"]
    );
    echo $result;   // $result = 1 (no. of affected rows = 1)
}

// ==========================================================================================================
// UPDATE FINGERPRINT
// ==========================================================================================================
// `$_POST["roll"]` should contain `roll_no` of the student
if (!empty($_POST["update-fingerprint"])) {
    $filterData = filteration($_POST);
    
    // get `fingerprint_id` of student
    $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no`=?";
    $update_id_rs = execCRUD($query, "i", $filterData["roll"]);
    $ESP_U_id = mysqli_fetch_assoc($update_id_rs)["fingerprint_id"];
    $ESP_block = true;
    
    $query = "INSERT INTO `esp_edu_state` (`mode`, `fingerprint_id`, `roll_no`, `server_block`)
                VALUES (?, ?, ?, ?)";
    $result = execCRUD($query, "siii", "U", $ESP_U_id, $filterData["roll"], 1);
    
    // START EMPTY TRANSACTION (COMMIT OR ROLLBACK DOESN'T MATTER)
    mysqli_begin_transaction($connect);

    // fingerprint is being set at ESP-side
    while (mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT `server_block` FROM `esp_edu_state`"
        )
    )["server_block"])
        sleep(1);
    // while(){} exits when ESP sends cofirmation

    // response finally sent to ajax call fom frontend
    echo mysqli_fetch_assoc(
        mysqli_query(
            $connect,
            "SELECT `success` FROM `esp_edu_state`"
        )
    )["success"];

    mysqli_query($connect, "DELETE FROM `esp_edu_state`");
}
?>
