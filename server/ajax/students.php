<?php
    require_once("../include/connect.php");
    require_once("../include/essential.php");
    adminLogin();

    // FOR ADDING NEW STUDENT
    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        // add student data to `users_all` table
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execCRUD($query, "isssis", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"]);
        // get `fingerprint_id` of same student
        // $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no` = ?";
        // $enlist_id_rs = execCRUD($query, "i", $filterData["roll"]);
        // // set `$_SESSION["add-finger-id"]` to be sent to mcu by getdata.php: case 2
        // $_SESSION["add-finger-id"] = mysqli_fetch_assoc($enlist_id_rs)["fingerprint_id"];
        // // fingerprint is being set at mcu-side
        // while($_SESSION["add-finger-id"]){
        //     $_SESSION["sleep-192"] = sleep(1);
        //     if($_SESSION["sleep-192"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
        //         die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        // }
        // while(){} exits when mcu sends cofirmation: `$_POST["confirm_id"] == $_SESSION["add-finger-id"]`
        // and then getdata/php: case 3 runs: `unset($_SESSION["add-finger-id"])`
        echo $result;   // response finally sent to ajax call fom frontend
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
        $query = "UPDATE `users_all` SET `delist_date`=CURDATE() WHERE `roll_no`=?";
        $result = execCRUD($query, "i", $filterData["roll"]);
        // $query = "SELECT `fingerprint_id` FROM `users-all` WHERE `roll_no`=? AND `delist_date`=CURDATE()";
        // $delist_id_rs = execCRUD($query, "i", $filterData["roll"]);
        // $_SESSION["rm-finger-id"] = mysqli_fetch_assoc($delist_id_rs)["fingerprint_id"];
        // while($_SESSION["rm-finger-id"]){
        //     $_SESSION["sleep-192"] = sleep(1);
        //     if($_SESSION["sleep-192"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
        //         die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        // }
        echo $result;    // $result = 1 (no. of affected rows = 1)
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
        $_SESSION["rm-finger-id"] = $_POST["update-fingerprint"];
        while($_SESSION["rm-finger-id"]){
            $_SESSION["sleep-192"] = sleep(1);
            if($_SESSION["sleep-192"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }
        $_SESSION["add-finger-id"] = $_POST["update-fingerprint"];
        while($_SESSION["add-finger-id"]){
            $_SESSION["sleep-192"] = sleep(1);
            if($_SESSION["sleep-192"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }
        echo 1;
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
        $filterData = filteration($_POST);
        $_SESSION["dropdown-sem"] = $filterData["dropdown-sem"];

        $query = "SELECT * FROM `users_all` WHERE `semester`";

        $query .= match ($filterData["dropdown-sem"]) {
            "all" => " LIKE \"_\"", // match any/all semesters
            default => " = " . $filterData["dropdown-sem"],
        };

        $query .= match ($_SESSION["dropdown-reg-status"]) {
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
            $i++;
        }
        echo $data;
    }

    if(isset($_POST["dropdown-reg-status"])){
        $filterData = filteration($_POST);
        $i = 1;
        $data = "";
        $date = "";
        $_SESSION["dropdown-reg-status"] = $filterData["dropdown-reg-status"];
        
        $query = "SELECT * FROM `users_all` WHERE `semester`";
        
        $query .= match ($_SESSION["dropdown-sem"]) {
            "all" => " LIKE \"_\"", // match any/all semesters
            default => " = " . $_SESSION["dropdown-sem"],
        };
        
        $query .= match ($filterData["dropdown-reg-status"]) {
            "enlist" => " AND `delist_date` IS NULL",
            "delist" => " AND `delist_date` IS NOT NULL",
            "all" => "",
        };
        
        $query .= " ORDER BY `fingerprint_id` DESC;";

        // if($filterData["regStatus"] == "all"){
        //     if($_SESSION["sem"] != null){
        //         $query = "SELECT * FROM `users_all` WHERE `semester`=? ORDER BY `fingerprint_id` DESC";
        //     }else{
        //         $query = "SELECT * FROM `users_all` ORDER BY `fingerprint_id` DESC";
        //     }
        // }else if($filterData["regStatus"] == "enlist"){
        //     if($_SESSION["sem"] != null){
        //         $query = "SELECT * FROM `users_all` WHERE `semester`=? AND `delist_date` IS NULL ORDER BY `fingerprint_id` DESC";
        //     }else{
        //         $query = "SELECT * FROM `users_all` WHERE `delist_date` IS NULL ORDER BY `fingerprint_id` DESC";
        //     }
        // }else{
        //     if($_SESSION["sem"] != null){
        //         $query = "SELECT * FROM `users_all` WHERE `semester`=? AND `delist_date` IS NOT NULL ORDER BY `fingerprint_id` DESC";
        //     }else{
        //         $query = "SELECT * FROM `users_all` WHERE `delist_date` IS NOT NULL ORDER BY `fingerprint_id` DESC";
        //     }
        // }
        // $result = ($_SESSION["sem"] != null) ? execCRUD($query, "i", $_SESSION["sem"]) : mysqli_query($connect, $query);
        $result = mysqli_query($connect, $query);
        if(mysqli_num_rows($result) == 0){
            echo "<b>No Data Found!</b>";
            exit();
        }
        while($row = mysqli_fetch_assoc($result)){
            if($row["delist_date"] != null){
                $date = $row["delist_date"];
            }else{
                $date = "NIL";
            }
            $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['semester']}</td>
                    <td>{$row['enlist_date']}</td>
                    <td>$date</td>
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