<?php
require_once "../resources/util.php";

$jobs = array();
$err = $profile = $fName = $lName = $pic = "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["member"])) {
        $memberID = intval($_GET["member"]);
    }
    if (!empty($memberID) && $memberID > 0) {
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
                                $sql = "SELECT title FROM Jobs WHERE id = ?";
                                if ($stmt3 = $conn->prepare($sql)) {
                                    $stmt3->bind_param("i", $param_jobID);
                                    $param_jobID = $j;
                                    if ($stmt3->execute()) {
                                        $stmt3->store_result();
                                        if ($stmt3->num_rows == 1) {
                                            $stmt3->bind_result($jt);
                                            $stmt3->fetch();
                                            array_push($jobs, $jt);
                                        }
                                    }
                                    $stmt3->close();
                                }
                            }
                        }
                        $stmt2->close();
                    }
                } else {
                    $err = "Please select a member.";
                }
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
    <title>View Member's Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; padding: 20px; }
        select{max-width: 500px;}
        img{
            height: auto; 
            width: auto; 
            max-width: 300px; 
            max-height: 300px;
        }
    </style>
</head>
<body>
    <h2>View Profile</h2>
    <a href="./welcome.php">Back</a>
    <?php 
        if ($err) {
            echo '<div class="alert alert-danger">'.$err.'</div>';
        }        
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
        <div class="form-group">
            <select name="member" class="form-control">
                <option value="">Choose a member...</option>
                <?php 
                    $sql = "SELECT id, fName, lName FROM Members";
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
    </form><br>
    <hr>
    <div>
        <?php
            if (!empty($fName) && !empty($lName)) {
                echo '<h3>'.$fName.' '.$lName."'s Profile</h3>";
            }
            if (!empty($pic)) {
                echo '<img src="' . htmlspecialchars($pic) . '">';
            }
            echo '<hr>';
            if (count($jobs) > 0) {
                echo '<h4>Jobs held: '.htmlspecialchars(join(', ', $jobs)).'</h4>';
            }
            if (!empty($profile)) {
                echo '<h5>'.htmlspecialchars($profile).'</h5>';
            }
        ?>
    </div>
</body>