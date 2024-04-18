<?php
    include("./include/essential.php");
    adminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require("./include/include.php"); ?>
    <script src="./js/students.js" defer></script>
    <title>Students - Biometric Attendance</title>
</head>
<body class="bg-light">
    <!-- Header -->
    <?php require("./include/header.php"); ?>

    <!-- Body -->
    <div class="container-fluid mx-auto px-5 mt-4">
        <div class="row">
            <div class="col-lg-12 ms-auto p-4 overflow-hidden">
                <h3 class="mb-4">Students Information</h3>
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-body">
                        <div class="text-end mb-4">
                            <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-student"><i class="bi bi-plus-square"></i> Add Student</button>
                        </div>
                        <div class="table-responsive-lg" style="height: 350px; overflow-y: scroll;">
                            <table class="table table-hover border text-center">
                                <thead class="bg-light">
                                    <tr class="text-dark">
                                        <th scope="col">#</th>
                                        <th scope="col">Roll No</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Semester</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="student-data">
                                    <tr>
                                        <td>1</td>
                                        <td>2023001</td>
                                        <td>John Doe</td>
                                        <td>john@example.com</td>
                                        <td>9876543210</td>
                                        <td>3rd</td>
                                        <td>03/09/2023</td>
                                        <td>2</td>
                                    </tr>   
                                    <tr  >
                                        < td>2</td>
                                        <td>2023001</td>
                                        <td>John Doe</td>
                                        <td>john@example.com</td>
                                        <td>9876543210</td>
                                        <td>3rd</td>
                                        <td>04/09/2023</td>
                                        <td>2</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>2023001</td>
                                        <td>John Doe</td>
                                        <td>john@example.com</td>
                                        <td>9876543210</td>
                                        <td>3rd</td>
                                        <td>31/04/2024</td>
                                        <td>2</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Students Modal -->
    <div class="modal fade" id="add-student" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="" id="add-student-form">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-fill-add"></i> Add Student Details</h5>
                    </div>
                    <div class="modal-body">
                        <marquee direction="left" class="badge bg-light text-dark mb-3 text-wrap lh-base rounded-pill">
                            Note: Student Details (Name, Email Address, Phone Number) must match with their ID (Adhaar Card, PAN Card) etc.
                        </marquee>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Student Roll Number</label>
                                <input type="number" name="roll" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Student Name</label>
                                <input type="text" name="name" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Student Email Address</label>
                                <input type="email" name="email" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Student Phone Number</label>
                                <input type="number" name="phone" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Semester</label>
                                <input type="number" name="sem" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Enrollment Date</label>
                                <input type="date" name="date" id="default-date" class="form-control shadow-none">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-sm btn-outline-dark shadow-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark text-white shadow-none">Add Student</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require("./include/footer.php"); ?>
</body>
</html>