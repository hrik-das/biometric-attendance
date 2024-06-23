<?php
opcache_compile_file("dashboard.php");
opcache_compile_file("getdata.php");
require_once("include/essential.php");
require_once("include/connect.php");
adminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once("./include/include.php"); ?>
    <link rel="stylesheet" href="./css/dashboard.css">
    <script src="./js/dashboard.js" defer></script>
    <title>Biometric Attendance - Dashboard</title>
</head>
<body class="bg-light">
    <!-- Header -->
    <?php require_once("./include/header.php"); ?>

    <!-- Body -->
    <div class="container-fluid mx-auto px-5 mt-5">
        <div class="bg-white shadow rounded pt-5 px-5 mb-4 pb-4">
            <h2 class="h2 fw-bold text-dark mb-4 fs-4">Dashboard Overview</h2>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="custom-success rounded-3 p-4">
                        <h3 class="fw-500 text-success fs-4">Total Students</h3>
                        <p class="text-success fw-bold" id="total">
                            <?php
                            $query = "SELECT COUNT(*) FROM `users_all` WHERE `delist_date` IS NULL";
                            echo mysqli_fetch_assoc(mysqli_query($connect, $query))['COUNT(*)'];
                            ?>
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="custom-primary rounded-3 p-4">
                        <h3 class="fw-500 text-primary fs-4">Present Today</h3>
                        <p class="text-primary fw-bold" id="present">Loading...</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="custom-danger rounded-3 p-4">
                        <h3 class="fw-500 text-danger fs-4">Absent Today</h3>
                        <p class="text-danger fw-bold" id="absent">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid mx-auto px-5 mb-5 pb-1">
        <div class="bg-white shadow rounded pt-5 px-5 mb-4 pb-4">
            <h2 class="h2 fw-bold text-dark mb-4 fs-4">Additional Insights</h2>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="custom-purple rounded-3 p-4">
                        <h3 class="fw-500 text-purple fs-4">Monthly Attendance Rate</h3>
                        <p class="text-purple fw-bold fs-5" id="m-a-r">Loading...</p>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="custom-orange rounded-3 p-4">
                        <h3 class="fw-500 text-orange fs-4">Daily Attendance Rate</h3>
                        <p class="text-orange fw-bold fs-5" id="d-a-r">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once("./include/footer.php"); ?>
</body>
</html>