<?php
    require_once("../include/connect.php");
    require_once("../include/essential.php");
    adminLogin();

    // FOR ADDING NEW STUDENT
    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        // BLOCKING BEHAVIOUR ===================================================
        // START TRANSACTION (MIGHT COMMIT OR ROLLBACK)
        mysqli_query($connect, "START TRANSACTION;");
        // BLOCKING BEHAVIOUR ===================================================
        
        // add student data to `users_all` table
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execCRUD($query, "isssis", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"]);
        
        // BLOCKING BEHAVIOUR ===================================================
        // set to `true` in getdata.php in case of sucessful enlist/delist/update
        // set to `false` in getdata.php in case of any failures
        // NOT to be set as a sub index of `$_SESSION["esp-edu"]`
        $_SESSION["esp-edu-success"] = null;
        // `E` for add/enlist
        $_SESSION["esp-edu"]["mode"] = "E";
        $_SESSION["esp-edu"]["roll"] = $filterData["roll"];
        // get `fingerprint_id` of same student
        $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no` = ?";
        $enlist_id_rs = execCRUD($query, "i", $filterData["roll"]);
        // set `$_SESSION["esp-edu"]["id"]` and `$_SESSION["esp-edu"]["roll"]` to be sent to ESP by getdata.php
        $_SESSION["esp-edu"]["id"] = mysqli_fetch_assoc($enlist_id_rs)["fingerprint_id"];
        $_SESSION["esp-block"] = true;
        
        // fingerprint is being set at ESP-side
        while($_SESSION["esp-block"])
            sleep(1);
        // while(){} exits when ESP sends cofirmation
        // and then getdata/php: case 3 runs: `unset($_SESSION["esp-edu"])`

        // response finally sent to ajax call fom frontend
        if ($_SESSION["esp-edu-success"])
        // BLOCKING BEHAVIOUR ===================================================
            echo 1;
        // BLOCKING BEHAVIOUR ===================================================
        else
            echo 0;

        unset($_SESSION["esp-edu-success"]);
        // BLOCKING BEHAVIOUR ===================================================
    }

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

    // TO SHOW STUDENT PREVIOUS DATA ON THE INPUT FIELD
    if(isset($_POST["get-student"])){
        $filterData = filteration($_POST);    // Filter the submitted form data for security
        $query = "SELECT * FROM `users_all` WHERE `roll_no`=?";    // Construct the SQL query to select student data based on roll number
        $result = execCRUD($query, "i", $filterData["get-student"]);    // Execute the SQL query using execCRUD function with the roll number parameter
        $studentdata = mysqli_fetch_assoc($result);    // Fetch the student data from the query result
        $data = json_encode($studentdata);    // Convert the student data to JSON format
        echo $data;    // Echo the JSON data representing the student information
    }

    // FOR DELETING STUDENT
    if(isset($_POST["remove-student"])){
        $filterData = filteration($_POST);
        // BLOCKING BEHAVIOUR ===================================================
        // START TRANSACTION (MIGHT COMMIT OR ROLLBACK)
        mysqli_query($connect, "START TRANSACTION;");
        // BLOCKING BEHAVIOUR ===================================================
        
        // delist student
        $query = "UPDATE `users_all` SET `delist_date`=CURDATE() WHERE `roll_no`=?";
        $result = execCRUD($query, "i", $filterData["roll"]);

        // BLOCKING BEHAVIOUR ===================================================
        // set to `true` in getdata.php in case of sucessful enlist/delist/update
        // set to `false` in getdata.php in case of any failures
        // NOT to be set as a sub index of `$_SESSION["esp-edu"]`
        $_SESSION["esp-edu-success"] = null;
        // `D` for delete/delist
        $_SESSION["esp-edu"]["mode"] = "D";
        $_SESSION["esp-edu"]["roll"] = $filterData["roll"];
        // get `fingerprint_id` of same student
        $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no`=? AND `delist_date`=CURDATE()";
        $delist_id_rs = execCRUD($query, "i", $filterData["roll"]);
        // set `$_SESSION["esp-edu"]["id"]` and `$_SESSION["esp-edu"]["roll"]` to be sent to ESP by getdata.php
        $_SESSION["esp-edu"]["id"] = mysqli_fetch_assoc($delist_id_rs)["fingerprint_id"];
        $_SESSION["esp-block"] = true;

        // fingerprint is being deleted at ESP-side
        while($_SESSION["esp-block"])
            sleep(1);
        // while(){} exits when ESP sends cofirmation
        // and then getdata/php: case 3 runs: `unset($_SESSION["esp-edu"])`

        // response finally sent to ajax call fom frontend
        if ($_SESSION["esp-edu-success"])
        // BLOCKING BEHAVIOUR ===================================================
            echo 1;
        // BLOCKING BEHAVIOUR ===================================================
        else
            echo 0;

        unset($_SESSION["esp-edu-success"]);
        // BLOCKING BEHAVIOUR ===================================================
    }

    // FOR UPDATING STUDENT DETAILS
    if(isset($_POST["update-details"])){
        $filterData = filteration($_POST);
        $query = "UPDATE `users_all` SET `roll_no`=?, `full_name`=?, `email`=?, `contact`=?, `semester`=?,`enlist_date`=? WHERE `fingerprint_id`=?";
        $result = execCRUD($query, "isssisi", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"], $filterData["id"]);
        echo $result;   // $result = 1 (no. of affected rows = 1)
    }

    // FOR UPDATING STUDENT FINGERPRINT
    if(!empty($_POST["update-fingerprint"])){ // `$_POST["update-fingerprint"]` should be set to `fingerprint_id` of the student
        // START TRANSACTION (MIGHT COMMIT OR ROLLBACK)
        mysqli_query($connect, "START TRANSACTION;");
        // set to `true` in getdata.php in case of sucessful enlist/delist/update
        // set to `false` in getdata.php in case of any failures
        // NOT to be set as a sub index of `$_SESSION["esp-edu"]`
        $_SESSION["esp-edu-success"] = null;
        // `U` for update
        $_SESSION["esp-edu"]["mode"] = "U";
        $_SESSION["esp-edu"]["id"] = $_POST["update-fingerprint"];

        // get `roll_no` of same student
        $query = "SELECT `roll_no` FROM `users_all` WHERE `fingerprint_id`=?";
        $update_id_rs = execCRUD($query, "i", $_POST["update-fingerprint"]);

        $_SESSION["esp-edu"]["roll"] = mysqli_fetch_assoc($update_id_rs)["roll_no"];
        $_SESSION["esp-block"] = true;

        // fingerprint is being updated at ESP-side
        while($_SESSION["esp-block"])
            sleep(1);
        // while(){} exits when ESP sends cofirmation
        // and then getdata/php: case 3 runs: `unset($_SESSION["esp-edu"])`

        // response finally sent to ajax call fom frontend
        if ($_SESSION["esp-edu-success"])
            echo 1;
        else
            echo 0;

        unset($_SESSION["esp-edu-success"]);
    }

    if(isset($_POST["search-student"])){
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

    if(isset($_POST["dropdown-sem"])){
        $delist_date = "";
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

    if(isset($_POST["dropdown-reg-status"])){
        $filterData = filteration($_POST);
        $i = 1;
        $data = "";
        $delist_date = "";
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
?>