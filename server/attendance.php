<?php
    opcache_compile_file("attendance.php");
    require_once("./include/essential.php");
    adminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once("./include/include.php"); ?>
    <script src="./js/attendance.js" defer></script>
    <title>Daily Attendance - Biometric Attendance</title>
</head>
<body class="bg-light">
    <!-- Header -->
    <?php require_once("./include/header.php"); ?>

    <!-- Body -->
    <div class="container-fluid mx-auto px-5 mt-3">
        <div class="row">
            <div class="col-12 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">Daily Attendance</h3>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <input type="date" class="form-select-sm p-1" max="<?php echo date("Y-m-d"); ?>" name="date" id="date" onchange="selectDate(this.value)">
                                </div>
                                <select class="form-select-sm p-1" onchange="selectSemester(this.value)" id="semester">
                                    <option value="all" selected>Semester</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end">
                                <input type="text" class="form-control-sm shadow-none" oninput="searchStudent(this.value)" placeholder="Search Student">
                            </div>
                        </div>
                        <div class="table-responsive-lg" style="height: 350px; overflow-y: scroll;">
                            <table class="table table-hover border text-center">
                                <thead class="bg-light">
                                    <tr class="text-dark">
                                        <th scope="col">#</th>
                                        <th scope="col">Roll No</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Semester</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody id="attendance-data"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once("./include/footer.php"); ?>
</body>
</html>