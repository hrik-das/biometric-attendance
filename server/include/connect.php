<?php
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "attendance";
    $connect = mysqli_connect($hostname, $username, $password, $database);
    if(!$connect){
        die("Something went wrong!".mysqli_connect_error());
    }

    function filteration($data){
        foreach($data as $key => $value){
            $value = trim($value);
            $value = stripslashes($value);
            $value = strip_tags($value);
            $value = htmlspecialchars($value);
            $data[$key] = $value;
        }
        return $data;
    }

    function selectAllData($table){
        $connect = $GLOBALS["connect"];
        $query = "SELECT * FROM `$table`";
        $result = mysqli_query($connect, $query);
        return $result;
    }

    /**
     * In-house utility for simple CRUD (`INSERT`, `SELECT`, `UPDATE`, `DELETE`) operations. Refrain
     * from passing nested subqueries to this function for easier error checking.
     * @param string $query SQL `INSERT`, `SELECT`, `UPDATE` or `DELETE` query
     * @param string $types
     * @param mixed $vars
     * @return \mysqli_result|false|int|string
     * Returns a `\mysqli_result` object or `false` for a sucessful or unsucessful `SELECT`
     * `$query` respectively, `int` or `string` for no. of affected rows for the other queries
     */
    function execCRUD($query, $types, $vars) {
        $connect = $GLOBALS["connect"];
        $stmt = mysqli_prepare($connect, $query);
        if (!$stmt)
            die("Error: `mysqli_prepare` at include/connect.php: Line #31: query: $query");
        
        if (!$stmt->bind_param($types, ...$vars))
            die("Error: `stmt->bind_param` at include/connect.php: Line #35: query: $query");

        if (!$stmt->execute()) {
            $stmt->close();
            die("Error: `stmt->execute` at include/connect.php: Line #38: query: $query");
        }
        
        if ($query[0] == 'S' || $query[0] == 's') {
            $result = $stmt->get_result();
        } else {
            $result = $stmt->affected_rows;
        }
        
        $stmt->close();
        return $result;
    }
?>