<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: endecrypt.php");
    exit;
}
 
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = "";
 
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate email
    if (isset($_POST["email"])) {
        $email = $_POST["email"];  
        $email = check_input($email);  
        $email_err = validate_email($email);
        if ($email_err == "") {
            $email = $email;
        }
    }
    // Validate password
    if (isset($_POST["password"])) {
        $password = $_POST["password"];  
        $password = check_input($password);  
        $password_err = validate_password($password);
        if ($password_err == "") {
            $temp_password  = $password;
        }     
    }
    if(empty($email_err) && empty($password_err)){
        $sql = "SELECT id, email, password FROM user_info WHERE email = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        $salt1 = "qmh*";
                        $salt2 = "pg!@";
                        $password = hash('ripemd128', "$salt1$temp_password$salt2");
                        if($password == $hashed_password){
                            session_start();
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["email"] = $email;                            
                            header("location: endecrypt.php");
                        } else{
                             $password_err = "Invalid Email/Password";
                        }
                    }
                }else{
                    $password_err = "Invalid Email/Password";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
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
function validate_email($email){
    if ($email == "") {
        return "Please enter Email <br>";
    }
    return "";
}
function validate_password($password){
    if ($password == "") {
        return "Please enter password <br>";
    }
    return "";
}

echo <<<_END
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <span class="help-block" style="color: red;"> $password_err </span>
        <form action="" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" class="form-control" value="">
                <span class="help-block" style="color: red;"> $email_err </span>
            </div>    
            <div class="form-group (!empty($password_err)) ? 'has-error' : ''; ">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>    
</body>
</html>
_END;
?>