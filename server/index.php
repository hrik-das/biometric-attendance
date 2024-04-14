<?php
    require("./include/connect.php");
    require("./include/essential.php");
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
    <?php require("./include/include.php"); ?>
    <title>Admin Login Panel</title>
</head>
<body class="bg-light">
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
        if(isset($_POST["login"])){
            $filterData = filteration($_POST);
            $query = "SELECT * FROM `admin_cred` WHERE `username`=? AND `password`=?";
            $values = [$filterData["username"], $filterData["password"]];
            $datatypes = "ss";
            $result = selectData($query, $values, $datatypes);
            if($result->num_rows == 1){
                $data = mysqli_fetch_assoc($result);
                $_SESSION["adminId"] = $data["id"];
                $_SESSION["adminLogin"] = true;
                redirect("dashboard.php");
            }else{
                alert("error", "Login failed - Invalid Credentials!");
            }
        }
    ?>
</body>
</html>