<?php
    require("../include/connect.php");
    require("../include/essential.php");
    adminLogin();

    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execCRUD($query, "isssis", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"]);
        
        $_SESSION["adding-student"] = $filterData["roll"];
        
        while ($_SESSION["adding-student"]) {
            $_SESSION["sleep-time"] = sleep(1);
            // $_SESSION["sleep-time"] == 192 (value of WAIT_IO_COMPLETION constant within the Windows API)
            if ($_SESSION["sleep-time"])
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }

        echo $result;
    }
?>