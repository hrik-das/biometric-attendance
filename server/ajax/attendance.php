<?php
opcache_compile_file("attendance.php");
require_once("../include/connect.php");
require_once("../include/essential.php");
adminLogin();

if (isset($_POST["init-session-vars"])) {
    $_SESSION["dropdown-date"] = date("Y-m-d");
    $_SESSION["dropdown-sem"] = "all";

    exit(0);
}

if (isset($_POST["dropdown-date"])) {
    $filterData = filteration($_POST);
    $_SESSION["dropdown-date"] = $filterData["dropdown-date"];

    exit(0);
}

if (isset($_POST["dropdown-sem"])) {
    $filterData = filteration($_POST);
    $_SESSION["dropdown-sem"] = $filterData["dropdown-sem"];

    exit(0);
}

if (isset($_POST["get-attendance"])) {
    $query = "SELECT * FROM `attendance_log` WHERE `date`= \"" . $_SESSION["dropdown-date"] . "\"";

    $query .= match ($_SESSION["dropdown-sem"]) {
        "all" => "",
        default => " AND `semester` = " . $_SESSION["dropdown-sem"],
    };

    $result = mysqli_query($connect, $query);
    $i = 1;
    $data = "";
    if (mysqli_num_rows($result) == 0) {
        echo "<b>No Data Found!</b>";
        exit(1);
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $data .= "<tr class='align-middle'>
                <td>$i</td>
                <td>{$row['roll_no']}</td>
                <td>{$row['full_name']}</td>
                <td>{$row['semester']}</td>
                <td>{$_SESSION['dropdown-date']}</td>
                </tr>";
        $i++;
    }

    echo $data;

    exit(0);
}

if (isset($_POST["search-student"])) {
    $filterData = filteration($_POST);
    $searchTerm = "%{$filterData['value']}%"; // Add wildcards to the search term
    // Prepare the query
    $query = "SELECT * FROM `attendance_log` WHERE `full_name` LIKE ? OR `roll_no` LIKE ?";
    // Execute the query using execCRUD function
    $result = execCRUD($query, "ss", $searchTerm, $searchTerm);
    $i = 1;
    $data = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $data .= "
                <tr class='align-middle'>
                    <td>$i</td>
                    <td>{$row['roll_no']}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['semester']}</td>
                </tr>";
                $i++;
    }
    echo $data;
    exit(0);
}