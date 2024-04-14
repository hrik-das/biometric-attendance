<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require("./include/include.php"); ?>
    <title>Biometric Attendance - Students</title>
</head>
<body class="bg-light">
    <!-- Header -->
    <?php include("./include/header.php"); ?>

    <!-- All Students Information -->
    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-12 ms-auto p-4 overflow-hidden">
                <h3 class="ms-3 mb-4 fs-4 fw-500">All Students Information</h3>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="text-end mb-4">
                            <button type="button" class="btn btn-dark shadow-none btn-sm" data-bs-toggle="modal" data-bs-target="#add-student"><i class="bi bi-plus-square"></i> Add Student</button>
                        </div>
                        <div style="height: 400px; overflow-y: scroll;">
                            <table class="table table-hover border text-center">
                                <thead>
                                    <tr class="bg-dark text-white">
                                        <th scope="col">#</th>
                                        <th scope="col">Roll No.</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Phone</th>
                                        <th scope="col">Semester</th>
                                        <th scope="col">Enroll Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="student-data">
                                    <tr class="align-middle">
                                        <td>1</td>
                                        <td>1171</td>
                                        <td>Plaban Roy</td>
                                        <td>plabanroy69@gmail.com</td>
                                        <td>+911234567890</td>
                                        <td>6th Semester</td>
                                        <td>2024/01/12</td>
                                        <td>Actions</td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td>1</td>
                                        <td>1171</td>
                                        <td>Plaban Roy</td>
                                        <td>plabanroy69@gmail.com</td>
                                        <td>+911234567890</td>
                                        <td>6th Semester</td>
                                        <td>2024/01/12</td>
                                        <td>Actions</td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td>1</td>
                                        <td>1171</td>
                                        <td>Plaban Roy</td>
                                        <td>plabanroy69@gmail.com</td>
                                        <td>+911234567890</td>
                                        <td>6th Semester</td>
                                        <td>2024/01/12</td>
                                        <td>Actions</td>
                                    </tr>
                                    <tr class="align-middle">
                                        <td>1</td>
                                        <td>1171</td>
                                        <td>Plaban Roy</td>
                                        <td>plabanroy69@gmail.com</td>
                                        <td>+911234567890</td>
                                        <td>6th Semester</td>
                                        <td>2024/01/12</td>
                                        <td>Actions</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="add-student" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="" id="add-student-form" autocomplete="off">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Student</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student Roll Number</label>
                                <input type="number" name="roll" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student Name</label>
                                <input type="text" name="name" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="number" name="phone" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Semester</label>
                                <input type="number" name="sem" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Enroll Date</label>
                                <input type="date" name="date" class="form-control shadow-none" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-outline-dark btn-sm shadow-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark btn-sm text-white shadow-none">Add Student</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Details Modal -->
    <div class="modal fade" id="edit-student" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="" id="edit-student-form" autocomplete="off">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Student Details</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student Roll Number</label>
                                <input type="text" name="roll" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Student Name</label>
                                <input type="text" name="name" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="number" name="phone" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Semester</label>
                                <input type="number" name="adult" class="form-control shadow-none" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Children (Max.)</label>
                                <input type="number" name="children" class="form-control shadow-none" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-sm btn-outline-dark shadow-none" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark text-white shadow-none">Save Chnages</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include("./include/footer.php"); ?>
</body>
</html>