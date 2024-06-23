<?php
opcache_compile_file("dashboard.php");
require_once("../include/connect.php");
require_once("../include/essential.php");
adminLogin();

// `$payload`'s formatting:
// PxxxxxxxxxxAxxxxxxxxxMARxxxxxxxxxxxxxxxxxxxxxDARxxxxxxxxxxxxxxxxxxx
//  presentNum absentNum   MonthlyAttendanceRate   DailyAttendanceRate
$payload = "";

$query = "SELECT * FROM `analytics` WHERE `date` = CURDATE()";
$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result)) {
    $analytics_today = mysqli_fetch_assoc($result);
    
    $payload .= "P" . $analytics_today['present'] .
                "A" . $analytics_today['absent'] .
                "M" . $analytics_today['mar'] .
                "D" . $analytics_today['dar'];
} else {
    $payload .= "P0A0M0D    0";
}

echo $payload;
exit(0);
?>