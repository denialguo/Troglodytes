<?php
require_once "../resources/util.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) {
    header("location: ./login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $err = "";
    $username = $_SESSION["username"];
    $password = trim($_POST["password"]);
    if (empty($password)) {
        $err = "Please enter your password.";
    } else {
        if ($username == "admin") {
            $err = "You cannot delete the 'admin' account.";
        } else {
            $sql = "SELECT password FROM Logins WHERE username = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $param_username);
                $param_username = $username;
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($hashed);
                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed)) {
                                $sql = "DELETE FROM Logins WHERE username = ?";
                                if ($stmt2 = $conn->prepare($sql)) {
                                    $stmt2->bind_param("s", $param_username);
                                    $param_username = $username;
                                    $stmt2->execute();
                                    $stmt2->close();
                                }
                                $memberID = getMember($conn, $username);
                                createLog($conn, $memberID, $memberID, 'ACCOUNT_DELETED', "User '".$username."' with ID ".strval($_SESSION["id"])." deleted their account.");
                                $_SESSION = array();
                                session_destroy();
                                header("location: ./goodbye.html");
                                exit;
                            } else {
                                $err = "Invalid password!";
                            }
                        }
                    }
                } else {
                    $err = "Oops! An error occured while trying to log you in.";
                }
                $stmt->close();
            }
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
        <h2>Delete Your Account</h2>
        <p>You are deleting the account <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Please enter your password to confirm.</p>
        
        <?php 
        if(!empty($err)){
            echo '<div class="alert alert-danger">' . $err . '</div>';
        }        
        ?>

        <div class="alert alert-warning">
            <b>Warning!</b> Once you delete your account, it is gone permanently.
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control"><br>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-danger" value="Delete">
            </div><br>
            
            <p><a class="btn btn-secondary" href="../Index/welcome.php">Cancel</a> <a class="btn btn-primary" href="../Index/index.html">Back to main page</a></p>
        </form>
    </div>
</body>
</html>