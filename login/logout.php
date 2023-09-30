<?php
// Initialize the session
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    require_once "../resources/connectdb.php";
    $sql = "INSERT INTO Logs (actionID, description) VALUES (10, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $param_username);
    $param_username = $_SESSION["username"];
    $stmt->execute();
    $stmt->close();
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