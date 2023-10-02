<?php
require_once "../resources/util.php";
 
if (!(hasPermission($conn, "DELETE_ACCOUNTS"))) {
    header("location: ./sorry.html");
    exit;
}

$username_err = $err = $username = $reason = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!(hasPermission($conn, "DELETE_ACCOUNTS"))) {
        header("location: ./sorry.html");
        exit;
    }

    $username = trim($_POST["username"]);
    $reason = trim($_POST["reason"]);
    if (empty($username)) {
        $username_err = "Please enter a username.";
    } else if (preg_match('/admin/i', $username)) {
        $username_err = "You cannot ban the admin account.";
    } else {
        $sql = "SELECT memberID FROM Logins WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($affectedMemberID);
                    $stmt->fetch();
                    $sql = "DELETE FROM Logins WHERE username = ?";
                    if ($stmt2 = $conn->prepare($sql)) {
                        $stmt2->bind_param("s", $param_username);
                        $param_username = $username;
                        if ($stmt2->execute()) {
                            $currentMember = getMember($conn, $_SESSION["username"]);
                            createLog($conn, $currentMember, $affectedMemberID, 'ACCOUNT_DELETED', $username.' was banned.'.(!empty($reason) ? ' Reason: '.$reason : ''));
                            $success = true;
                            $username = "";
                        } else {
                            $err = "<b>Oops!</b> An error occured while trying to delete that account.";
                        }
                        $stmt2->close();
                    }
                } else {
                    $username_err = "User not found.";
                }
            } else {
                $err = "<b>Oops!</b> An error occured while trying to delete that account.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ban an Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
        .form-group{margin-bottom: 5px;}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Ban an Account</h2>

        <div class="alert alert-warning">
            <b>Warning!</b> If you ban an account, it is permanent!
            Looking to remove a member instead? Use the <a href="./remove-member.php">remove member</a> form.
        </div>

        <?php 
            if ($err) {
                echo '<div class="alert alert-danger">'.$err.'</div>';
            }        
        ?>

        <?php 
            if ($success) {
                echo '<div class="alert alert-success">Successfully deleted that account.</div>';
            }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form"> 
            <div class="form-group">
                <label>Username</label>
                <input name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <label>Reason (optional)</label>
                <textarea name="reason" form="form" class="form-control" maxlength="10000"><?php echo htmlspecialchars($reason);?></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="./dashboard.php">Cancel</a>
            </div>
        </form>
    </div>
</body>

<script type="text/javascript">
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href ); // fuck you, duplicate form submission
    }
</script>

</html>