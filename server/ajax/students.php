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
    if(isset($_POST["get-student"])){
        $filterData = filteration($_POST);    // Filter the submitted form data for security
        $query = "SELECT * FROM `users_all` WHERE `roll_no`=?";    // Construct the SQL query to select student data based on roll number
        $result = execCRUD($query, "i", $filterData["get-student"]);    // Execute the SQL query using execCRUD function with the roll number parameter
        $studentdata = mysqli_fetch_assoc($result);    // Fetch the student data from the query result
        $data = json_encode($studentdata);    // Convert the student data to JSON format
        echo $data;    // Echo the JSON data representing the student information
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