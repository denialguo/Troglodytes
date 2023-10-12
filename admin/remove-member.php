<?php
require_once "../resources/util.php";
 
if (!(hasPermission($conn, "REMOVE_MEMBERS"))) {
    header("location: ./sorry.html");
    exit;
}

$err = $reason = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!(hasPermission($conn, "REMOVE_MEMBERS"))) {
        header("location: ./sorry.html");
        exit;
    }

    $memberID = 0;
    $reason = trim($_POST["reason"]);
    if (isset($_POST["member"])) {
        $memberID = intval($_POST["member"]);
    }
    if (!($memberID) || $memberID == 1) { // Member ID 1 is the Admin, which cannot be deleted.
        $err = "Please select a member.";
    } else {
        $sql = "SELECT fName, lName FROM Members WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_memberID);
            $param_memberID = $memberID;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($fName, $lName);
                    $stmt->fetch();
                    $sql = "DELETE FROM Members WHERE id = ?";
                    if ($stmt2 = $conn->prepare($sql)) {
                        $stmt2->bind_param("i", $param_memberID);
                        $param_memberID = $memberID;
                        if ($stmt2->execute()) {
                            $currentMember = getMember($conn, $_SESSION["username"]);
                            createLog($conn, $currentMember, $memberID, 'MEMBER_REMOVED', $fName.' '.$lName.' was removed.'.(!empty($reason) ? ' Reason: '.$reason : ''));
                            $success = true;
                        } else {
                            $err = "<b>Oops!</b> An error occured while trying to delete that member.";
                        }
                        $stmt2->close();
                    }
                } else {
                    $err = "Please select a member.";
                }
            } else {
                $err = "<b>Oops!</b> An error occured while trying to delete that member.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove a Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
        .form-group{margin-bottom: 5px;}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Remove a Member</h2>

        <div class="alert alert-warning">
            <b>Warning!</b> If you delete a member, it is permanent!
            This form will <b>not</b> ban any accounts associated with the member. Looking to ban an account instead? Use the <a href="./ban-account.php">ban account</a> form.
        </div>

        <?php 
            if ($err) {
                echo '<div class="alert alert-danger">'.$err.'</div>';
            }        
        ?>

        <?php 
            if ($success) {
                echo '<div class="alert alert-success">Successfully removed that member.</div>';
            }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form"> 
            <div class="form-group">
                <select name="member" class="form-control">
                    <option value="">Choose a member...</option>
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