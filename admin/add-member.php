<?php
require_once "../resources/util.php";
 
if(!(hasPermission($conn, "ADMINISTRATOR"))){
    header("location: ./sorry.html");
    exit;
}

$fName = $fName_err = $lName = $lName_err = $pic = $pic_err = $profile = "";
$jobs = $job_list = array();
$warning = $err = $success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $sql = "SELECT id FROM Members WHERE LOWER(fName) = LOWER(?) AND LOWER(lName) = LOWER(?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $param_fName, $param_lName);
        $param_fName = $fName;
        $param_lName = $lName;
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows > 0 && (!isset($_SESSION["prev_fName"]) || !isset($_SESSION["prev_lName"]) || ($_SESSION["prev_fName"] != $fName) || ($_SESSION["prev_lName"] != $lName))) {
                $_SESSION["prev_fName"] = $fName;
                $_SESSION["prev_lName"] = $lName;
                $warning = true;
            } else {
                $warning = false;
            }
            $stmt->close();
        }
    }

    if (empty($fName_err) && empty($lName_err) && empty($pic_err) && !($warning)) {

        if(!(hasPermission($conn, "ADMINISTRATOR"))){
            header("location: ./sorry.html");
            exit;
        }

        $sql = "INSERT INTO Members (fName, lName, picURL, profileText) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $param_fName, $param_lName, $param_pic, $profile);
            $param_fName = $fName;
            $param_lName = $lName;
            $param_pic = $pic;
            if ($stmt->execute()) {
                $memberID = mysqli_insert_id($conn);
                for ($i = 0; $i < count($jobs); $i++) {
                    $sql = "SELECT title FROM Jobs WHERE id = ?";
                    if ($stmt2 = $conn->prepare($sql)) {
                        $stmt2->bind_param("i", $param_jobID);
                        $param_jobID = $jobs[$i];
                        if ($stmt2->execute()) {
                            $stmt2->store_result();
                            if ($stmt2->num_rows == 1) {
                                $stmt2->bind_result($j);
                                $stmt2->fetch();
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
                $currentMember = getMember($conn, $_SESSION["username"]);
                createLog($conn, $currentMember, $memberID, "MEMBER_ADDED", "Added member ".$fName." ".$lName.".".(!(empty($pic)) ? " Picture URL: ".$pic."." : "")." Jobs: ".(count($job_list) > 0 ? join(", ", $job_list) : "None"));
                if (!empty($profile)) {
                    createLog($conn, $currentMember, $memberID, "PROFILE_UPDATED", $profile);
                }
                unset($_SESSION["prev_fName"]);
                unset($_SESSION["prev_lName"]);
                
                $success = true;
            } else {
                $err = true;
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
    <title>Add Member</title>
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
        <h2>Add a Member</h2>
        <?php 
            if ($err) {
                echo '<div class="alert alert-danger"><b>Oops!</b> An error occured while trying to add that member.</div>';
            }        
        ?>

        <?php 
            if ($warning) {
                echo '<div class="alert alert-warning">A member already exists with the same first and last name. Are you sure you want to add another member with the same name? If you are trying to update a member, use the <a href="./update-member.php">update member</a> form.</div>';
            }        
        ?>

        <?php 
            if ($success) {
                echo '<div class="alert alert-success">Successfully added member. ID: <b>'.$memberID.'</b></div>';
            }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form"> 
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