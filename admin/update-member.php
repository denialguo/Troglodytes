<?php
require_once "../resources/util.php";
 
if(!(hasPermission($conn, "ADMINISTRATOR"))){
    header("location: ./sorry.html");
    exit;
}

$fName = $fName_err = $lName = $lName_err = $pic = $pic_err = $err = $profile = $old_profile = "";
$jobs = $job_list = $r = array();
$fetched = $success = false;
$memberID = 0;



if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["member"])) {
        $memberID = intval($_GET["member"]);
    }
    if (!empty($memberID) && $memberID > 1) {
        $sql = "SELECT fName, lName, picURL, profileText FROM Members WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_memberID);
            $param_memberID = $memberID;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($fName, $lName, $pic, $profile);
                    $stmt->fetch();
                    $sql = "SELECT jobID FROM MembersJobs WHERE memberID = ?";
                    if ($stmt2 = $conn->prepare($sql)) {
                        $stmt2->bind_param("i", $param_memberID);
                        $param_memberID = $memberID;
                        if ($stmt2->execute()) {
                            $stmt2->store_result();
                            $stmt2->bind_result($j);
                            while ($stmt2->fetch()) {
                                array_push($jobs, $j);
                            }
                            $fetched = true;
                        }
                        $stmt2->close();
                    }
                }
            }
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["member"])) {
        $memberID = intval($_POST["member"]);
    }
    $fName = substr(trim($_POST["fName"]),0,50);
    $lName = substr(trim($_POST["lName"]),0,50);
    $pic = trim($_POST["pic"]);
    $profile = trim($_POST["profile"]);

    if (isset($_POST["jobs"])) {
        $jobs = $_POST["jobs"];
    };

    if (empty($fName)) {
        $fName_err = "Please enter a first name.";
    } else if (!preg_match('/^[a-zA-Z]+$/', $fName)) {
        $fName_err = "Name can only contain English letters.";
    } else if (preg_match('/admin/i', $fName)) {
        $fName_err = "Name cannot contain 'admin'.";
    }

    if (empty($lName)) {
        $lName_err = "Please enter a last name.";
    } else if (!preg_match('/^[a-zA-Z]+$/', $lName)) {
        $lName_err = "Name can only contain English letters.";
    } else if (preg_match('/admin/i', $lName)) {
        $lName_err = "Name cannot contain 'admin'.";
    }

    if (strlen($pic) > 255) {
        $pic_err = "Photo URL is too long. Use a link with fewer than 255 characters.";
    }

    if (empty($fName_err) && empty($lName_err) && empty($pic_err) && $memberID > 1) {

        if(!(hasPermission($conn, "ADMINISTRATOR"))){
            header("location: ./sorry.html");
            exit;
        }

        $memberFound = false;

        $sql = "SELECT profileText FROM Members WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_memberID);
            $param_memberID = $memberID;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $memberFound = true;
                    $stmt->bind_result($old_profile);
                    $stmt->fetch();
                }
            }
            $stmt->close();
        }

        $sql = "UPDATE Members SET fName = ?, lName = ?, picURL = ?, profileText = ? WHERE id = ?";
        if ($memberFound && ($stmt = $conn->prepare($sql))) {
            $stmt->bind_param("ssssi", $param_fName, $param_lName, $param_pic, $param_profile, $param_memberID);
            $param_fName = $fName;
            $param_lName = $lName;
            $param_pic = $pic;
            $param_profile = $profile;
            $param_memberID = $memberID;
            if ($stmt->execute()) {
                $sql = "DELETE FROM MembersJobs WHERE memberID = ?";
                if ($stmt2 = $conn->prepare($sql)) {
                    $stmt2->bind_param("i", $param_memberID);
                    $param_memberID = $memberID;
                    if ($stmt2->execute()) {
                        for ($i = 0; $i < count($jobs); $i++) {
                            $sql = "SELECT title FROM Jobs WHERE id = ?";
                            if ($stmt3 = $conn->prepare($sql)) {
                                $stmt3->bind_param("i", $param_jobID);
                                $param_jobID = $jobs[$i];
                                if ($stmt3->execute()) {
                                    $stmt3->store_result();
                                    if ($stmt3->num_rows == 1) {
                                        $stmt3->bind_result($j);
                                        $stmt3->fetch();
                                        array_push($job_list, $j);
                                        $sql = "INSERT INTO MembersJobs (memberID, jobID) VALUES (?, ?)";
                                        if ($stmt3 = $conn->prepare($sql)) {
                                            $stmt3->bind_param("ii", $param_memberID, $param_jobID);
                                            $param_memberID = $memberID;
                                            $param_jobID = $jobs[$i];
                                            $stmt3->execute();
                                            $stmt3->close();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                $currentMember = getMember($conn, $_SESSION["username"]);
                createLog($conn, $currentMember, $memberID, "MEMBER_UPDATED", "Updated member to ".$fName." ".$lName.".".(!(empty($pic)) ? " Picture URL: ".$pic."." : "")." Jobs: ".(count($job_list) > 0 ? join(", ", $job_list) : "None"));
                if ($profile != $old_profile) {
                    createLog($conn, $currentMember, $memberID, "PROFILE_UPDATED", $profile);
                }
                $success = true;
            } else {
                $err = "<b>Oops!</b> An error occured while trying to update that member.";
            }
            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
        .form-group{margin-bottom: 5px;}
        .job-checkbox div{padding: 5px}
        .job-checkbox text{vertical-align: top; padding: 5px;}
        .job-checkbox input{ width: 20px; height: 20px;}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Update a Member</h2>
        <?php 
            if ($err) {
                echo '<div class="alert alert-danger">'.$err.'</div>';
            }        
        ?>

        <?php 
            if ($success) {
                echo '<div class="alert alert-success">Successfully updated that member.</div>';
            }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
            <div class="form-group">
                <select name="member" class="form-control">
                    <option value="">Choose a member...</option>
                    <?php 
                        $sql = "SELECT id, fName, lName FROM Members WHERE NOT id = 1;";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo '<option '.($row["id"] == $memberID ? 'selected ' : '').'value="'.$row["id"].'">'.$row["fName"].' '.$row["lName"].' ('.$row["id"].')</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Fetch">
            </div> 
        </form>

        <form style="<?php echo $fetched ? '': 'display: none;' ?>" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form">
            <div class="form-group">
                <input name="member" style="display: none;" value="<?php echo $memberID; ?>">
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input name="fName" class="form-control <?php echo (!empty($fName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $fName; ?>">
                <span class="invalid-feedback"><?php echo $fName_err; ?></span>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input name="lName" class="form-control <?php echo (!empty($lName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $lName; ?>">
                <span class="invalid-feedback"><?php echo $lName_err; ?></span>
            </div>
            <div class="form-group">
                <label>URL of Photo (optional)</label>
                <input name="pic" class="form-control <?php echo (!empty($pic_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $pic; ?>">
            </div>
            <div class="form-group">
                <label>Profile Text (optional, 10,000 characters max)</label>
                <textarea name="profile" form="form" class="form-control" maxlength="10000"><?php echo $profile;?></textarea>
            </div>
            <div class="form-group">
                <?php
                    $sql = "SELECT * FROM Jobs;";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        echo '<label>Jobs</label><br>';
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="job-checkbox"><label>';
                            echo '<input type="checkbox" name="jobs[]" value="'.$row["id"].'" '. ((isset($jobs) && in_array($row["id"], $jobs)) ? 'checked' : '') .'/>';
                            if (empty($row["description"])) {
                                echo '<text>'.htmlspecialchars($row["title"]).'</text>';
                            } else {
                                echo '<text><abbr title="'.htmlspecialchars($row["description"]).'">'.htmlspecialchars($row["title"]).'</abbr></text>';
                            }
                            echo '</label></div>';
                        }
                    }
                ?>
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