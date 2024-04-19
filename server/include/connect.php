<?php
    // Establish a connection to the MySQL database
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "attendance";
    $connect = mysqli_connect($hostname, $username, $password, $database);
    if(!$connect){    // Check if the connection was successful
        die("Connection failed: ".mysqli_connect_error());    // If connection failed, output an error message and terminate the script
    }

    /**
    * Filters and sanitizes input data to prevent common security vulnerabilities.
    * @param array $data An associative array containing input data to be filtered.
    * @return array The filtered and sanitized input data.
    */
    function filteration($data){
        foreach($data as $key => $value){    // Iterate through each key-value pair in the input data
            $value = trim($value);    // Trim leading and trailing whitespace
            $value = stripslashes($value);    // Remove backslashes (slashes are not typically harmful in input data, so removing them may not be necessary)
            $value = strip_tags($value);    // Remove HTML and PHP tags
            $value = htmlspecialchars($value);    // Convert special characters to HTML entities to prevent XSS attacks
            $data[$key] = $value;    // Assign the filtered value back to the original data array
        }
        return $data;    // Return the filtered and sanitized data
    }

    /**
    * Selects all data from a specified table in the database.
    * @param string $table The name of the table from which to retrieve data.
    * @param mysqli $connection Optional. The mysqli database connection object. If not provided, it uses the global connection.
    * @return mysqli_result|false Returns a mysqli_result object on success, or false on failure.
    */
    function selectAllData($table){
        $connect = $GLOBALS["connect"];    // Use global connection
        $query = "SELECT * FROM `$$table`";    // Construct the SQL query
        $result = mysqli_query($connect, $query);
        if($result){    // Check if the query execution was successful
            return false;    // Return false if there was an error
        }
        return $result;    // Return the mysqli_result object
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
    function execCRUD($query, $types, ...$vars){
        $stmt = mysqli_prepare($GLOBALS["connect"], $query);    // Prepare the SQL statement
        if(!$stmt){    // Check if the statement preparation failed
            die("Error: `mysqli_prepare()` at include/connect.php: Line #43:"."\nquery: $query");
        }
        if(!mysqli_stmt_bind_param($stmt, $types, ...$vars)){    // Bind parameters to the prepared statement
            die("Error: `mysqli_stmt_bind_param()` at include/connect.php: Line #48:"."\nquery: $query\ntypes: $types\nvars: $vars");
        }
        if(!mysqli_stmt_execute($stmt)){    // Execute the prepared statement
            die("Error: `mysqli_stmt_execute()` at include/connect.php: Line #52:" . "\nquery: $query");
        }
        $result = null;
        if(stripos($query, "SELECT") !== false){    // Determine the type of operation and retrieve the result accordingly
            $result = mysqli_stmt_get_result($stmt);
        }else{
            $result = mysqli_stmt_affected_rows($stmt);
        }
        mysqli_stmt_close($stmt);    // Close the prepared statement
        return $result;    // Return the result of the operation
    }
?>