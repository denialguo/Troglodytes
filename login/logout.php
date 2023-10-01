<?php
// Initialize the session
require_once "../resources/connectdb.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
    $sql = "INSERT INTO Logs (actionID, description) VALUES (10, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_username);
        $param_username = $_SESSION["username"];
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();
}



// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("location: ./login.php");
exit;
?>