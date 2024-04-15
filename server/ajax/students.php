<?php
    require("../include/connect.php");
    require("../include/essential.php");
    adminLogin();

    if(isset($_POST["add-student"])){
        $filterData = filteration($_POST);
        $todayDate = new DateTime($filterData["date"]);
        $query = "INSERT INTO `users_all` (`roll_no`, `full_name`, `email`, `phone`, `semester`, `enroll_date`) VALUES (?, ?, ?, ?, ?, ?)";
        $values = [$filterData["roll"], $filterData["name"], $filterData["email"], $filterData["phone"], $filterData["sem"], $todayDate->format("Y-m-d")];
        $result = insertData($query, $values, "isssis");
        echo $result;
    }
?>