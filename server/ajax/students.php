<?php
    require("../include/connect.php");
    require("../include/essential.php");
    adminLogin();

    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `contact`, `semester`, `enlist_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $result = execCRUD($query, "isssis", $filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $filterData["date"]);
        
        $_SESSION["add-roll-no"] = $filterData["roll"];
        
        while ($_SESSION["add-roll-no"]) {
            $_SESSION["sleep-time"] = sleep(1);
            if ($_SESSION["sleep-time"] /* == WAIT_IO_COMPLETION (synchapi.h) */)
                die("Couldn't sleep(1): WAIT_IO_COMPLETION");
        }

        echo $result;
    }
?>