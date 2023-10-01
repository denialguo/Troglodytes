<?php
require_once "../resources/connectdb.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true){
    header("location: ../Index/welcome.php");
    exit;
}

$err = $username = $password = $confirm_password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = trim($_POST["username"]);
    $p = trim($_POST["password"]);
    $c = trim($_POST["confirm_password"]);

    // Validate username
    if (empty($u)) {
        $err = "Please enter a username.";
    } else if (strlen($u) > 50) {
        $err = "Username cannot be longer than 50 characters.";
    } else if(!preg_match('/^[a-zA-Z0-9_]+$/', $u)) {
        $err = "Username can only contain English letters, numbers, and underscores.";
    } else if (preg_match('/admin/i', $u)) {
        $err = "Username cannot contain 'admin'.";
    } else {
        $sql = "SELECT id FROM Logins WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $u;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $err = "That username is already taken.";
                } else {
                    $username = $u;
                }
            } else {
                $err = "Oops! An error occured. Please try again later.";
            }
            $stmt->close();
        }
    }

    if (empty($err) && empty($p)) {
        $err = "Please enter a password.";
    } else {
        $password = $p;
    }

    if (empty($err)) {
        if (empty($c)) {
            $err = "Please confirm your password.";
        } else {
            $confirm_password = $c;
            if ($confirm_password != $password) {
                $err = "Passwords do not match.";
            } else {
                $sql = "INSERT INTO Logins (username, password) VALUES (?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ss", $param_username, $param_password);
                    $param_username = $username;
                    $param_password = password_hash($password, PASSWORD_DEFAULT);
                    if ($stmt->execute()) {
                        $sql = "INSERT INTO Logs (actionID, description) VALUES (3, ?)";
                        if ($stmt2 = $conn->prepare($sql)) {
                            $stmt2->bind_param("s", $param_username);
                            $param_username = $username;
                            $stmt2->execute();
                            $stmt2->close();
                        }
                        header("location: ./login.php");
                    } else {
                        $err = "Oops! An error occured. Please try again later.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" href="./images/favicon1.jpg" type="image/jpg">
    
    <title>Sign in to CSP Webpage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./LoginPage.css">
    <style>body { font-family: Arial, Helvetica, sans-serif; }</style>
</head>
<body>
    <button onclick="Cancel()">Cancel</button>
    
    <?php 
        if(!empty($err)){
            echo '<div class="alert alert-danger">' . $err . '</div>';
        }        
    ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <fieldset>
         <legend>Sign Up:</legend>
         <!--
            First & last name will be added in a separate form that links accounts to members
            (i.e. not every account will have a member associated with it)
        -->
         <label for="username">Username:</label><br>
         <input id="username" name="username" value="<?php echo $username; ?>"><br><br>
         <label for="password">Password:</label><br>
         <input type="password" id="password" name="password" value="<?php echo $password; ?>"><br><br>
         <label for="confirm_password">Confirm Password:</label><br>
         <input type="password" id="confirm_password" name="confirm_password"><br><br>
         <input class="submit" type="submit" value="Submit" href="Login2.html"><br><br>
        </fieldset>
    </form>
</body>
<script src="./LoginPage.js"></script>
</html>