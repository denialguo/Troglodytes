<?php
// Initialize the session
require_once "../resources/util.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
    $username = $_SESSION["username"];
    $memberID = getMember($conn, $username);
    createLog($conn, $memberID, $memberID, 'LOGGED_OUT', $username);
}



// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("location: ./login.php");
exit;
?>