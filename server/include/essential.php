<?php
    // SENDGRID API KEY
    define("SENDGRID_API_KEY", "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");
    define("SENDGRID_NAME", "Karimganj College Attendance System");
    define("SENDGRID_EMAIL", "hrikdas012@gmail.com");

    /**
    * Displays an alert message with specified type and content using Bootstrap classes.
    * @param string $type The type of alert ("success" for success message, "danger" for error message).
    * @param string $message The message content to be displayed in the alert.
    * @return void Outputs the HTML code for the alert message.
    */
    function alert($type, $message){
        $bs_class = ($type == "success") ? "alert-success" : "alert-danger";    // Determine the Bootstrap class based on the alert type
        // Output the HTML code for the alert message
        echo<<<alert
            <div class="alert $bs_class alert-dismissible fade show custom-alert" role="alert">
                <p>$message</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        alert;
    }

    /**
    * Redirects the user to a specified URL using JavaScript.
    * @param string $url The URL to which the user should be redirected.
    * @return void Redirects the user to the specified URL.
    */
    function redirect($url){
        echo "<script> window.location.href = '$url'; </script>";    // Output JavaScript code to redirect the user
        exit();    // Exit the script to ensure no further code execution after redirection
    }

    /**
     * Performs admin login validation using PHP sessions and redirects to the login page if not logged in.
     * This function starts a PHP session and checks if the admin is logged in by verifying the "adminLogin" session variable.
     * If the admin is not logged in, the function redirects the user to the login page (index.php) and exits the script.
     * Ensure that session_start() is called before using this function to handle sessions.
     * @return void Redirects to the login page if the admin is not logged in.
     */
    function adminLogin(){
        session_start();    // Start or resume the PHP session
        // Check if admin is logged in (by checking the "adminLogin" session variable)
        if(!(isset($_SESSION["adminLogin"]) && $_SESSION["adminLogin"] == true)){
            header("Location: index.php");    // Redirect to the login page if admin is not logged in
            exit();    // Exit the script after redirection
        }
    }
?>