<?php
    require_once("../include/connect.php");
    require_once("../include/essential.php");
    adminLogin();

    if(isset($_POST["get-students"])){
        $i = 1;
        $data = "";
        $query = "SELECT * FROM `users_all` WHERE `delist_date` is NULL ORDER BY `fingerprint_id` DESC";
        $result = mysqli_query($connect, $query);
        while($row = mysqli_fetch_assoc($result)){
            // if($row["delist_date"] != null){
            //     $badge = "
            //         <span class='badge rounded-pill bg-light text-dark'>
            //             Delisted
            //         </span>";
            // }
            $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>$row[roll_no]</td>
                    <td>$row[full_name]</td>
                    <td>$row[email]</td>
                    <td>$row[contact]</td>
                    <td>$row[semester]</td>
                    <td>$row[enlist_date]</td>
                    <td>
                        <button type='button' onclick='editStudent($row[roll_no])' class='btn btn-dark shadow-none btn-sm me-1' data-bs-toggle='modal' data-bs-target='#edit-student'>
                            <i class='bi bi-pencil-square'></i>
                        </button>
                        <button type='button' onclick='removeStudent($row[roll_no])' class='btn btn-danger shadow-none btn-sm'>
                            <i class='bi bi-trash'></i>
                        </button>
                    </td>
                </tr>";
            $i++;
        }
        echo $data;
    }

    if(isset($_POST["get-student"])){
        $filterData = filteration($_POST);
        $query = "SELECT * FROM `users_all` WHERE `roll_no`=?";
        $result = execCRUD($query, "i", $filterData["get-student"]);
        $studentdata = mysqli_fetch_assoc($result);
        $data = json_encode($studentdata);
        echo $data;
    }

    // FOR ADDING NEW STUDENT
    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        
        // add student data to `users_all` table
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execCRUD($query, "isssis", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"]);

        // get `fingerprint_id` of same student
        $query = "SELECT `fingerprint_id` FROM `users_all` WHERE `roll_no` = ?";
        $enlist_id_rs = execCRUD($query, "i", $filterData["roll"]);

        // set `$_SESSION["add-finger-id"]` to be sent to mcu by getdata.php: case 2
        $_SESSION["add-finger-id"] = mysqli_fetch_assoc($enlist_id_rs)["fingerprint_id"];
        // fingerprint is being set at mcu-side
        while($_SESSION["add-finger-id"]){
            $_SESSION["sleep-192"] = sleep(1);
            if($_SESSION["sleep-192"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }
        // while(){} exits when mcu sends cofirmation: `$_POST["confirm_id"] == $_SESSION["add-finger-id"]`
        // and then getdata/php: case 3 runs: `unset($_SESSION["add-finger-id"])`
        
        echo $result;   // response finally sent to ajax call fom frontend
    }

    // FOR DELETING STUDENT
    if(isset($_POST["remove-student"])){
        $filterData = filteration($_POST);
        
        // set 
        $query = "UPDATE `users_all` SET `delist_date`=CURDATE() WHERE `roll_no`=?";
        $result = execCRUD($query, "i", $filterData["roll"]);
        $query = "SELECT `fingerprint_id` FROM `users-all` WHERE `roll_no`=? AND `delist_date`=CURDATE()";
        $delist_id_rs = execCRUD($query, "i", $filterData["roll"]);
        $_SESSION["rm-finger-id"] = mysqli_fetch_assoc($delist_id_rs)["fingerprint_id"];
        while($_SESSION["rm-finger-id"]){
            $_SESSION["sleep-192"] = sleep(1);
            if($_SESSION["sleep-192"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }
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
    if (!empty($_POST["update-fingerprint"])) { // `$_POST["update-fingerprint"]` should be set to `fingerprint_id` of the student
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
?>