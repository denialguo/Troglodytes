<?php
require_once "../resources/util.php";
 
if (!(hasPermission($conn, "ADMINISTRATOR"))) {
    header("location: ./sorry.html");
    exit;
}

$username_err = $err = $username = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!(hasPermission($conn, "ADMINISTRATOR"))) {
        header("location: ./sorry.html");
        exit;
    }

    $username = trim($_POST["username"]);
    $memberID = 0;
    if (isset($_POST["member"])) {
        $memberID = intval($_POST["member"]);
    }
    if (empty($username)) {
        $username_err = "Please enter a username.";
    } else if (preg_match('/admin/i', $username)) {
        $username_err = "You cannot link the admin account.";
    } else {
        if ($memberID == 1) {
            $err = 'Please select a member. To unlink the account, select the "Unlink this account" option.';
        } else {
            $sql = "SELECT id FROM Members WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $param_memberID);
                $param_memberID = $memberID;
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1 || !($memberID)) {
                        $sql = "SELECT memberID FROM Logins WHERE username = ?";
                        if ($stmt2 = $conn->prepare($sql)) {
                            $stmt2->bind_param("s", $param_username);
                            $param_username = $username;
                            if ($stmt2->execute()) {
                                $stmt2->store_result();
                                if ($stmt2->num_rows == 1) {
                                    $stmt2->bind_result($currentMemberID);
                                    $stmt2->fetch();
                                    if ($currentMemberID == $memberID) {
                                        $err = "That account is already linked to that member.";
                                    } else if ($memberID) {
                                        $sql = "UPDATE Logins SET memberID = ? WHERE username = ?";
                                        if ($stmt3 = $conn->prepare($sql)) {
                                            $stmt3->bind_param("is", $param_memberID, $param_username);
                                            $param_memberID = $memberID;
                                            $param_username = $username;
                                            if ($stmt3->execute()) {
                                                $currentMember = getMember($conn, $_SESSION["username"]);
                                                createLog($conn, $currentMember, $memberID, 'ACCOUNT_LINKED', $username);
                                                $success = true;
                                            } else {
                                                $err = "<b>Oops!</b> An error occured while trying to link that account.";
                                            }
                                            $stmt3->close();
                                        }
                                    } else {
                                        $sql = "UPDATE Logins SET memberID = NULL WHERE username = ?";
                                        if ($stmt2 = $conn->prepare($sql)) {
                                            $stmt2->bind_param("s", $param_username);
                                            $param_username = $username;
                                            if ($stmt2->execute()) {
                                                $currentMember = getMember($conn, $_SESSION["username"]);
                                                createLog($conn, $currentMember, $currentMemberID, 'ACCOUNT_UNLINKED', $username);
                                                $success = true;
                                            } else {
                                                $err = "<b>Oops!</b> An error occured while trying to unlink that account.";
                                            }
                                            $stmt2->close();
                                        }
                                    }
                                } else {
                                    $username_err = "User not found.";
                                }
                            } else {
                                $err = "<b>Oops!</b> An error occured while trying to link that account.";
                            }
                        }
                    } else {
                        $err = "Please select a member.";
                    }
                } else {
                    $err = "<b>Oops!</b> An error occured while trying to link that account.";
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Link an Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
        .form-group{margin-bottom: 5px;}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Link an Account</h2>

        <div class="alert alert-warning">
            <b>Warning!</b> If you link an account with a member, it will be unlinked from any previous members.
        </div>

        <?php 
            if ($err) {
                echo '<div class="alert alert-danger">'.$err.'</div>';
            }        
        ?>

        <?php 
            if ($success) {
                echo '<div class="alert alert-success">Successfully linked that account with that member.</div>';
            }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form"> 
            <div class="form-group">
                <label>Username</label>
                <input name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-group">
                <select name="member" class="form-control">
                    <option value="1">Choose a member...</option>
                    <option value="0">Unlink this account</option>
                    <?php 
                        $sql = "SELECT id, fName, lName FROM Members WHERE NOT id = 1;";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="'.$row["id"].'">'.$row["fName"].' '.$row["lName"].' ('.$row["id"].')</option>';
                        }
                    ?>
                </select>
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