<?php
    require_once '../resources/connect.php'; // Connects to the DB
    if ($_SERVER['REQUEST_METHOD'] == 'POST') { // The setup button is pressed

        $result = $conn->query("SHOW DATABASES LIKE 'Troglodytes';");

        if ($result->num_rows > 0) { // Checks if the DB already exists
            $conn->query("USE Troglodytes;");
            $result = $conn->query("SHOW TABLES LIKE 'Logins';"); // If so check for a login table
            if ($result->num_rows > 0) {
                echo "<p>Setup is already complete. <a href='../login/login.php'>Log in</a> with username 'admin' and an empty password.</p>";
            } else {
                setup($conn);
            }
        } else {
            setup($conn);
        }

    }

    function setup($c) {
        $query = file_get_contents('./setup.sql');
        if (mysqli_multi_query($c, $query)) {
            echo "<p>Setup complete. <a href='../login/login.php'>Log in</a> with username 'admin' and an empty password.</p>";
        } else {
            echo "Oops! An error occured trying to complete setup.";
        }
    }

    $conn->close();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Database Setup</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>body { font-family: Arial, Helvetica, sans-serif; }</style>
    </head>
    <body>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <button type="submit" class="btn btn-primary">Begin Setup</button>
            <p>Set up the database in order to use it. Setup is needed before logins and most features involving a back-end will work.</p>
        </form>
    </body>
</html>
