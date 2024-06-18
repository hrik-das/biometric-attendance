<?php
    opcache_compile_file("attendance.php");
    require_once("../include/connect.php");
    require_once("../include/essential.php");
    adminLogin();

    if(isset($_POST["date"])){
        
    }
?>