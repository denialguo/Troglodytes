<?php
require_once "../resources/util.php";
 
if(!(hasPermission($conn, "ADMINISTRATOR"))){
    header("location: ./sorry.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    <h1 class="my-5">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to the admin dashboard.</h1>
    <p>
        <a href="./add-member.php" class="btn btn-success">Add a member</a>
        <a href="./remove-member.php" class="btn btn-danger">Remove a member</a>
        <a href="./update-member.php" class="btn btn-primary">Update a member</a>
        <a href="./ban-account.php" class="btn btn-warning">Ban an account</a>
        <a href="./link-account.php" class="btn btn-info">Link accounts to members</a>
    </p>
    <p>
        <a href="../Index/index.html" class="btn btn-primary">Back to Main Page</a>
        <a href="../Index/welcome.php" class="btn btn-secondary">Exit Dashboard</a>
    </p>
</body>
</html>