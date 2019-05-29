<?php
// Include config file
require_once "config.php";
// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";
 
//Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
        $username = check_input($username);
        $username_err = validate_username($username);
        if ($username_err == "") {
            $sql = "SELECT id FROM user_info WHERE username = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = $username;
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $username_err = "This username is already taken.";
                    } else{
                        $username = $username;
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    // Validate email
    if (isset($_POST["email"])) {
        $email = $_POST["email"];  
        $email = check_input($email);  
        $email_err = validate_email($email);
        if ($email_err == "") {
            $sql = "SELECT id FROM user_info WHERE email = ?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                $param_email = htmlspecialchars(stripslashes(trim($_POST["email"])));
                if(mysqli_stmt_execute($stmt)){
                    mysqli_stmt_store_result($stmt);
                    if(mysqli_stmt_num_rows($stmt) == 1){
                        $email_err = "This email is already taken.";
                    } else{
                        $email = $email;
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    } 
    // Validate password
    if (isset($_POST["password"])) {
        $password = $_POST["password"];  
        $password = check_input($password);  
        $password_err = validate_password($password);
        if ($password_err == "") {
            $password = $password;
        }     
    }
    // Validate confirm password
    if (isset($_POST["confirm_password"])) {
        $confirm_password = $_POST["confirm_password"];  
        $confirm_password = check_input($confirm_password);  
        $confirm_password_err = validate_password($confirm_password);
        if ($confirm_password == "") {
            $confirm_password = $confirm_password;
        }     
    }
    //Password check
    if(empty($password_err) && ($password != $confirm_password)){
        $confirm_password_err = "Password did not match.";
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "INSERT INTO user_info (username, email, password) VALUES (?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_email, $param_password);
            $param_username = $username;
            $param_email = $email;
            $salt1 = "qmh*";
            $salt2 = "pg!@";
            $param_password = hash('ripemd128', "$salt1$password$salt2");
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}

    //Validating functions
    function check_input($string){
        if(get_magic_quotes_gpc()) 
            $string = stripslashes($string);
        return htmlentities($string);
    }

    function validate_username($username){
        if ($username == "") {
            return "Please enter username <br>";
        }elseif (strlen($username) < 5) {
            return "Username must be atleast 5 characters<br>";
        }elseif (preg_match("/[^a-zA-Z0-9_-]/", $username)){
            return "Only letters, numbers, - and _ are allowed in username";
        }else{
            return "";
        }
    }
    function validate_email($email){
        if ($email == "") {
            return "Please enter Email <br>";
        }elseif (!((strpos($email, ".") > 0) && (strpos($email, "@") > 0)) || preg_match("/[^a-zA-Z0-9.@_-]/", $email)){
            return "The email address is Invalid<br>";
        }
        return "";
    }
    function validate_password($password){
        if ($password == "") {
            return "Please enter password <br>";
        }elseif (strlen($password) < 6) {
            return "Password must have atleast 6 characters <br>";
        }
        return "";
    }



echo <<<_END
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
        <style type="text/css">
            body{ font: 14px sans-serif; }
            .wrapper{ width: 350px; padding: 20px; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <h2>Sign Up</h2>
            <form action="" method="post">
                <div class="form-group  (!empty($username_err)) ? 'has-error' : ''; ">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="">
                    <span class="help-block" style="color: red;">$username_err</span>
                </div>  
                <div class="form-group (!empty($email_err)) ? 'has-error' : ''; ">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" value="">
                    <span class="help-block" style="color: red;">$email_err</span>
                </div>    
                <div class="form-group (!empty($password_err)) ? 'has-error' : '';">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" value="">
                    <span class="help-block" style="color: red;">$password_err</span>
                </div>
                <div class="form-group (!empty($confirm_password_err)) ? 'has-error' : ''; ">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" value="">
                    <span class="help-block" style="color: red;">$confirm_password_err</span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Submit">
                    <input type="reset" class="btn btn-default" value="Reset">
                </div>
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
        </div>    
    </body>
    </html>
_END;
?>