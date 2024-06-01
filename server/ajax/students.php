<?php
    require_once("../include/connect.php");
    require_once("../include/essential.php");
    adminLogin();
    
    // TO SHOW ALL ENLISTED STUDENT DATA ON THE TABLE ON LOAD
    if(isset($_POST["get-all-enlisted"])){
        // On page load, set `$_SESSION["dropdown"]` to the same default values as
        // `/students.php:39:<select ... id="reg-status">` and
        // `/students.php:28:<select ... id="semester">`
        // "UTILITY" variable
        $_SESSION["dropdown-sem"] = "all";
        $_SESSION["dropdown-reg-status"] = "enlist";
        $i = 1;    // Initialize variable for showing table Serial Indexing
        $data = "";    // Initialize variable for append new student data with the previous student data
        $query = "SELECT * FROM `users_all` WHERE `delist_date` IS NULL ORDER BY `fingerprint_id` DESC";    // Construct the SQL query to select active students
        $result = mysqli_query($connect, $query);    // Execute the SQL query using mysqli_query with the database connection
        while($row = mysqli_fetch_assoc($result)){    // Iterate through the query results and format data into HTML table rows
            // Concatenate HTML table row for each student
            $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['enlist_date']}</td>
                    <td>NIL</td>
                    <td>
                        <button type='button' onclick='editStudent({$row['roll_no']})' class='btn btn-dark shadow-none btn-sm me-1' data-bs-toggle='modal' data-bs-target='#edit-student'><i class='bi bi-pencil-square'></i></button>
                        <button type='button' onclick='removeStudent({$row['roll_no']})' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>
                    </td>
                </tr>";
            $i++;    // Increasing by 1 for New Rows
        }
        echo $data;    // Echo the concatenated HTML table rows representing student information
    }

    // TO SHOW STUDENT'S PREVIOUS DATA ON THE INPUT FIELD
    else if(isset($_POST["get-student"])){
        $filterData = filteration($_POST);    // Filter the submitted form data for security
        $query = "SELECT * FROM `users_all` WHERE `roll_no`=?";    // Construct the SQL query to select student data based on roll number
        $result = execCRUD($query, "i", $filterData["get-student"]);    // Execute the SQL query using execCRUD function with the roll number parameter
        $studentdata = mysqli_fetch_assoc($result);    // Fetch the student data from the query result
        $data = json_encode($studentdata);    // Convert the student data to JSON format
        echo $data;    // Echo the JSON data representing the student information
    }

    else if(isset($_POST["search-student"])){
        $filterData = filteration($_POST);
        $searchTerm = "%{$filterData['value']}%"; // Add wildcards to the search term
        // Prepare the query
        $query = "SELECT * FROM `users_all` WHERE `full_name` LIKE ? OR `roll_no` LIKE ?";
        // Execute the query using execCRUD function
        $result = execCRUD($query, "ss", $searchTerm, $searchTerm);
        $i = 1;
        $data = "";
        while($row = mysqli_fetch_assoc($result)){
            $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['enlist_date']}</td>
                    <td>
                        <button type='button' onclick='editStudent({$row['roll_no']})' class='btn btn-dark shadow-none btn-sm me-1' data-bs-toggle='modal' data-bs-target='#edit-student'><i class='bi bi-pencil-square'></i></button>
                        <button type='button' onclick='removeStudent({$row['roll_no']})' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>
                    </td>
                </tr>";
            $i++;
        }
        echo $data;
    }

    else if(isset($_POST["dropdown-sem"])){
        $filterData = filteration($_POST);
        $_SESSION["dropdown-sem"] = $filterData["dropdown-sem"];
        $query = "SELECT * FROM `users_all` WHERE `semester`";
        $query .= match($filterData["dropdown-sem"]){
            "all" => " LIKE \"_\"",    // match any/all semesters
            default => " = ".$filterData["dropdown-sem"],
        };
        $query .= match($_SESSION["dropdown-reg-status"]){
            "enlist" => " AND `delist_date` IS NULL",
            "delist" => " AND `delist_date` IS NOT NULL",
            "all" => "",
        };
        $query .= " ORDER BY `fingerprint_id`;";
        $result = mysqli_query($connect, $query);
        $i = 1;
        $data = "";
        if(mysqli_num_rows($result) == 0){
            echo "<b>No data Found!</b>";
            exit();
        }
        while($row = mysqli_fetch_assoc($result)){
            $delist_date = $row["delist_date"] ?: "NIL";
            $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['enlist_date']}</td>
                    <td>{$delist_date}</td>
                    <td>
                        <button type='button' onclick='editStudent({$row['roll_no']})' class='btn btn-dark shadow-none btn-sm me-1' data-bs-toggle='modal' data-bs-target='#edit-student'><i class='bi bi-pencil-square'></i></button>
                        <button type='button' onclick='removeStudent({$row['roll_no']})' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>
                    </td>
                </tr>";
            $i++;
        }
        echo $data;
    }

    else if(isset($_POST["dropdown-reg-status"])){
        $filterData = filteration($_POST);
        $i = 1;
        $data = "";
        $_SESSION["dropdown-reg-status"] = $filterData["dropdown-reg-status"];
        $query = "SELECT * FROM `users_all` WHERE `semester`";
        $query .= match($_SESSION["dropdown-sem"]){
            "all" => " LIKE \"_\"",    // match any/all semesters
            default => " = " . $_SESSION["dropdown-sem"],
        };
        $query .= match($filterData["dropdown-reg-status"]){
            "enlist" => " AND `delist_date` IS NULL",
            "delist" => " AND `delist_date` IS NOT NULL",
            "all" => "",
        };
        $query .= " ORDER BY `fingerprint_id` DESC";
        $result = mysqli_query($connect, $query);
        if(mysqli_num_rows($result) == 0){
            echo "<b>No Data Found!</b>";
            exit();
        }
        while($row = mysqli_fetch_assoc($result)){
            $delist_date = $row["delist_date"] ?: "NIL";
            $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['enlist_date']}</td>
                    <td>{$delist_date}</td>
                    <td>
                        <button type='button' onclick='editStudent({$row['roll_no']})' class='btn btn-dark shadow-none btn-sm me-1' data-bs-toggle='modal' data-bs-target='#edit-student'><i class='bi bi-pencil-square'></i></button>
                        <button type='button' onclick='removeStudent({$row['roll_no']})' class='btn btn-danger shadow-none btn-sm'><i class='bi bi-trash'></i></button>
                    </td>
                </tr>";
            $i++;
        }
        echo $data;
    }

    // ==========================================================================================================
    // ENLIST
    // ==========================================================================================================
    if (isset($_POST["add-student"])) {
        $filterData = filteration($_POST);

        // get `fingerprint_id` of student to be entered
        $query = "SELECT `AUTO_INCREMENT`
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = 'attendance' AND TABLE_NAME = 'users_all'";
        
        $enlist_id_rs = mysqli_query($connect, $query);
        // because `fingerprint_id` is primary key
        $ESP_E_id = mysqli_fetch_assoc($enlist_id_rs)["AUTO_INCREMENT"];

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