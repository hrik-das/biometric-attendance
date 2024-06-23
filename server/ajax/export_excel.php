<?php
opcache_compile_file("dashboard.php");
require_once("../include/connect.php");
require_once("../include/essential.php");
adminLogin();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["status"])) {
    $status = $_POST["status"];

    // SQL query based on status
    if ($status === "enlist") {
        $sql = "SELECT * FROM `users_all` WHERE `delist_date` IS NULL";
    } elseif ($status === "delist") {
        $sql = "SELECT * FROM `users_all` WHERE `delist_date` IS NOT NULL";
    } else {
        $sql = "SELECT * FROM `users_all`";
    }
    $result = mysqli_query($connect, $sql);
    if(mysqli_num_rows($result) > 0){
        $csv = "Roll Number, Full Name, Email Address, Phone Number, Semester, Enlist Date, Delist Date\n";
        while ($row = $result->fetch_assoc()) {
            $delist_date = $row["delist_date"] ?: "NIL";
            $csv .= $row["roll_no"] . "," . $row["full_name"] . "," . $row["email"] . "," . $row["contact"] . "," . $row["semester"] . "," . $row["enlist_date"] . "," . $delist_date . "\n";
        }
        // Set headers to download the file rather than display it
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename='student_details.csv'");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $csv;
    }else{
        echo "No records found";
    }
    mysqli_close($connect);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["start_date"]) && isset($_POST["end_date"])) {
    $startDate = $_POST["start_date"];
    $endDate = $_POST["end_date"];
    // SQL query based on date range
    $sql = "SELECT * FROM `attendance_log` WHERE `date` BETWEEN '$startDate' AND '$endDate'";
    $result = mysqli_query($connect, $sql);
    if (mysqli_num_rows($result) > 0) {
        $csv = "Roll Number, Full Name, Semester, Date\n";
        while ($row = mysqli_fetch_assoc($result)) {
            $csv .= $row["roll_no"] . "," . $row["full_name"] . "," . $row["semester"] . "," . $row["date"] . "," . "\n";
        }
        // Output the file to the browser
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename='attendance_details.csv'");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $csv;
    } else {
        echo "No records found";
    }
    mysqli_close($connect);    // Close the database connection
}
?>