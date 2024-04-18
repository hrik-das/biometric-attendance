<?php
    require_once("../include/connect.php");
    require_once("../include/essential.php");
    adminLogin();

    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execCRUD($query, "isssis", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"]);
        $_SESSION["add-roll-no"] = $filterData["roll"];
        while($_SESSION["add-roll-no"]){
            $_SESSION["sleep-time"] = sleep(1);
            if($_SESSION["sleep-time"])    /* == WAIT_IO_COMPLETION (synchapi.h) */
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }
        echo $result;
    }

    if(isset($_POST["get-students"])){
        $i = 1;
        $data = "";
        $query = "SELECT `fingerprint_id`, `roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date` FROM `users_all` WHERE `delist_date` is NULL ORDER BY `fingerprint_id` DESC";
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
                        <button type='button' class='btn btn-dark shadow-none btn-sm me-1' data-bs-toggle='modal' data-bs-target='#edit-student'>
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

    if(isset($_POST["remove-student"])){
        $filterData = filteration($_POST);
        
        $query = "UPDATE `users_all` SET `delist_date` = CURDATE() WHERE `roll_no` = ?";
        $result = execCRUD($query, "i", $filterData["roll"]);

        $query = "SELECT `fingerprint_id` FROM `users-all`
                    WHERE `roll_no` = ? AND `delist_date` = CURDATE();";
        $delist_id_rs = execCRUD($query, "i", $filterData["roll"]);

        $_SESSION["rm-finger-id"] = $delist_id_rs->fetch_assoc()["fingerprint_id"];

        while ($_SESSION["rm-finger-id"]) {
            $_SESSION["sleep-time"] = sleep(1);
            if ($_SESSION["sleep-time"] /* == WAIT_IO_COMPLETION (synchapi.h) */)
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }

        // $result = 1 (no. of affected rows = 1)
        echo $result;
    }
?>