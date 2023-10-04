<?php
    require_once "../resources/util.php";
    $memberID = getMember($conn, isset($_SESSION["username"]) ? $_SESSION["username"] : NULL);
    $err = $pic = $old_pic = $profile = $old_profile = $pic_err = "";
    $success = false;
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true || !$memberID) {
        $err = 'Your account isn\'t linked to a member. Ask an administrator for help or <a href="../login/login.php">log in</a> to a linked account.<br><a href="./welcome.php">Go back</a>';
    } else {
        if ($memberID > 0) {
            $sql = "SELECT picURL, profileText FROM Members WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $param_memberID);
                $param_memberID = $memberID;
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($pic, $profile);
                        $stmt->fetch();
                    }
                }
                $stmt->close();
            }
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pic = trim($_POST["pic"]);
        $profile = trim($_POST["profile"]);
        if (strlen($pic) > 255) {
            $pic_err = "Photo URL is too long. Use a link with fewer than 255 characters.";
        }
        $sql = "SELECT picURL, profileText FROM Members WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_memberID);
            $param_memberID = $memberID;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $memberFound = true;
                    $stmt->bind_result($old_pic, $old_profile);
                    $stmt->fetch();
                }
            }
            $stmt->close();
        }
        $sql = "UPDATE Members SET picURL = ?, profileText = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $param_pic, $param_profile, $param_memberID);
            $param_pic = $pic;
            $param_profile = $profile;
            $param_memberID = $memberID;
            if ($stmt->execute()) {
                if ($pic != $old_pic) {
                    createLog($conn, $memberID, $memberID, "MEMBER_UPDATED", $pic ? "Updated picture URL to ".$pic : "Removed picture");
                }
                if ($profile != $old_profile) {
                    createLog($conn, $memberID, $memberID, "PROFILE_UPDATED", $profile);
                }
                $success = true;
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Your Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; padding: 20px; }
    </style>
</head>
<body>
    <h2>Update Your Profile</h2>
    <?php 
        if ($err) {
            echo '<div class="alert alert-danger">'.$err.'</div>';
        }        
    ?>
    <?php 
        if ($success) {
            echo '<div class="alert alert-success">Successfully updated your profile.</div>';
        }        
    ?>
    <form style="<?php echo $memberID ? '': 'display: none;' ?>" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form">
        <div class="form-group">
            <label>URL of Photo (optional)</label>
            <input name="pic" class="form-control <?php echo (!empty($pic_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $pic; ?>">
        </div>
        <div class="form-group">
            <label>Profile Text (optional, 10,000 characters max)</label>
            <textarea name="profile" form="form" class="form-control" maxlength="10000"><?php echo $profile;?></textarea>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Submit">
            <a class="btn btn-link ml-2" href="./welcome.php">Cancel</a>
        </div>
    </form>
</body>
</html>