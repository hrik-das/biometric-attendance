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
    * In-house utility for simple CRUD (`INSERT`, `SELECT`, `UPDATE`, `DELETE`)
    * operations. Refrain from passing nested subqueries to this function for
    * easier error checking.
    * @param string $query SQL `INSERT`, `SELECT`, `UPDATE` or `DELETE` query
    * @param string $types type string
    * @param array ...$vars UNPACKED array, or simply comma-separated-values
    * @return \mysqli_result|false|int|string
    * Returns a `\mysqli_result` object or `false` for a sucessful or unsucessful
    * `SELECT` `$query` respectively, `int` or `string` for no. of affected rows
    * for the other queries
    */
    function execCRUD($query, $types, ...$vars) {
        $stmt = mysqli_prepare($GLOBALS["connect"], $query);
        if (!$stmt) {
            die("Error: `mysqli_prepare()` at include/connect.php: Line #43:"."\nquery: $query");
        }
        
        if (!mysqli_stmt_bind_param($stmt, $types, ...$vars)) {
            die("Error: `mysqli_stmt_bind_param()` at include/connect.php: Line #48:"."\nquery: $query\ntypes: $types\nvars: $vars");
        }
    
        if (!mysqli_stmt_execute($stmt)) {
            die("Error: `mysqli_stmt_execute()` at include/connect.php: Line #52:"."\nquery: $query");
        }
    
        $result = null;
        if (stripos($query, "SELECT") !== false) {
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $result = mysqli_stmt_affected_rows($stmt);
        }
    
        mysqli_stmt_close($stmt);
        return $result;
    }    
?>