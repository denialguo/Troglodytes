<?php
require_once "../resources/util.php";
 
if(!(hasPermission($c, "ADD_MEMBERS"))){
    header("location: ./sorry.html");
    exit;
}

$fName = $fName_err = $lName = $lName_err = $pic = $pic_err = "";
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
        .form-group{margin-bottom: 5px;}
        .job-checkbox div{padding: 5px}
        .job-checkbox text{vertical-align: top; padding: 5px;}
        .job-checkbox input{ width: 20px; height: 20px;}
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Add a Member</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
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
                <?php
                    $sql = "SELECT * FROM Jobs;";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        echo '<label>Jobs</label><br>';
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="job-checkbox"><label>';
                            echo '<input type="checkbox" name="jobs" value="'.$row["id"].'"/>';
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
                <a class="btn btn-link ml-2" href="../Index/welcome.php">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>