<?php
    require_once("./include/connect.php");
    require_once("./include/essential.php");
    session_start();
    if((isset($_SESSION["adminLogin"]) && $_SESSION["adminLogin"] == true)){
        header("Location: dashboard.php");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css">
    <?php require_once("./include/include.php"); ?>
    <title>Admin Login Panel</title>
</head>
<body class="bg-light">
    <p class="error"></p>
    <div class="login-form text-center rounded bg-white shadow overflow-hidden">
        <form action="index.php" method="post">
            <h4 class="bg-dark text-white py-3">ADMIN LOGIN PANEL</h4>
            <div class="p-4">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control shadow-none text-center" placeholder="Username" required>
                </div>
                <div class="mb-4">
                    <input type="password" name="password" class="form-control shadow-none text-center" placeholder="Password" required>
                </div>
                <button type="submit" name="login" class="btn text-white btn-dark shadow-none">LOGIN</button>
            </div>
        </form>
    </div>

    <?php
        // USER LOGIN
        if(isset($_POST["login"])){
            $filterData = filteration($_POST);    // Filter the submitted form data for security
            $query = "SELECT * FROM `admin_cred` WHERE `username`=? AND `password`=?";    // Construct the SQL query to select admin credentials
            $result = execCRUD($query, "ss", $filterData["username"], $filterData["password"]);    // Execute the SQL query using execCRUD function with username and password parameters
            if(mysqli_num_rows($result) == 1){    // Check if exactly one row is returned from the query
                $data = mysqli_fetch_assoc($result);    // Fetch the admin data from the query result
                $_SESSION["adminLogin"] = true;    // Set session variables for adminLogin = true
                $_SESSION["adminId"] = $data["id"];    // Set session variables for adminId = userId
                redirect("dashboard.php");    // Redirect to the dashboard
            }else{
                // Display error message using JavaScript for invalid credentials
                echo "<script>
                          let error = document.querySelector('.error');
                          error.style.display = 'block';
                          error.innerText = 'Login Failed - Invalid Credentials!';
                          setTimeout(() => error.style.display = 'none', 3000);
                      </script>";
            }
        }
    ?>
</body>
</html>