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
     * In-house functionality for simple CRUD (`INSERT`, `SELECT`, `UPDATE`, `DELETE`) operations
     * @param string $query An SQL query
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
            die("Error: `mysqli_prepare` at Line #31: query: $query");
        
        if (!$stmt->bind_param($types, ...$vars))
            die("Error: `stmt->bind_param` at Line #35: query: $query");

        if (!$stmt->execute()) {
            $stmt->close();
            die("Error: `stmt->execute` at Line #38: query: $query");
        }
        
        if ($query[0] == 'S' || $query[0] == 's') {
            $result = $stmt->get_result();
        } else {
            $result = $stmt->affected_rows;
        }
        
        $stmt->close();
        return $result;
    }

    function insertData($sql, $values, $datatypes){
        $connect = $GLOBALS["connect"];
        if($stmt = mysqli_prepare($connect, $sql)){
            mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            }else{
                mysqli_stmt_close($stmt);
                die("Query Cannot be Executed - Insert");
            }
        }else{
            die("Query Cannot be Prepared - Insert");
        }
    }

    function selectData($sql, $values, $datatypes){
        $connect = $GLOBALS["connect"];
        if($stmt = mysqli_prepare($connect, $sql)){
            mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            }else{
                mysqli_stmt_close($stmt);
                die("Query Cannot be Executed - Select");
            }
        }else{
            die("Query Cannot be Prepared - Select");
        }
    }

    function updateData($sql, $values, $datatypes){
        $connect = $GLOBALS["connect"];
        if($stmt = mysqli_prepare($connect, $sql)){
            mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            }else{
                mysqli_stmt_close($stmt);
                die("Query Cannot be Executed - Update");
            }
        }else{
            die("Query Cannot be Prepared - Update");
        }
    }

    function deleteData($sql, $values, $datatypes){
        $connect = $GLOBALS["connect"];
        if($stmt = mysqli_prepare($connect, $sql)){
            mysqli_stmt_bind_param($stmt, $datatypes, ...$values);
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                return $result;
            }else{
                mysqli_stmt_close($stmt);
                die("Query Cannot be Executed - Delete");
            }
        }else{
            die("Query Cannot be Prepared - Delete");
        }
    }
?>