<?php
session_start();
 
// Checks if user is logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: ../Index/welcome.php");
    exit;
}
 
// Connect to DB
require_once "../resources/connectdb.php";

$username = $password = "";
$username_err = $login_err = "";
 
// Form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    $password = trim($_POST["password"]);
    
    // Validate credentials
    if(empty($username_err)) {
        
        $sql = "SELECT id, username, password FROM Logins WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed);
                    if ($stmt->fetch()) {
                        if (!($hashed) || password_verify($password, $hashed)) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            $sql = "INSERT INTO Logs (actionID, description) VALUES (9, ?)";
                            $stmt2 = $conn->prepare($sql);
                            $stmt2->bind_param("s", $param_username);
                            $param_username = $username;
                            $stmt2->execute();
                            $stmt2->close();
                            header("location: ../Index/welcome.php");
                        } else {
                            $login_err = "Invalid password!";
                        }
                    }
                } else {
                    $login_err = "Invalid username!";
                }
            } else {
                echo "Oops! An error occured while trying to log you in.";
            }

            $stmt->close();
        }
    }
    
    $conn->close();
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div><br>
            <p>Don't have an account? <a href="./signup.php">Sign up now</a>.</p>
            <a href="../Index/index.html">Back to main page</a><br><br>
            <a href="" onclick="alert('skill issue')">Forgot password?</a>
        </form>
    </div>
</body>
</html>