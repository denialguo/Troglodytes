<?php
  // This is the same as the other file except it uses the database "Troglodytes"

  $conn = mysqli_connect('localhost', 'root','');
   
  // Check connection
  if($conn === false){
      die('ERROR: Could not connect. ' . mysqli_connect_error());
  } else {
    $conn->query("USE Troglodytes;");
  }
  
  session_start();

  if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] == true) {
    $sql = "SELECT id FROM Logins WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $_SESSION["id"];
        if ($stmt->execute()) {
            $stmt->store_result();
            if ($stmt->num_rows == 0) {
                $_SESSION = array();
                session_destroy();
            }
        }
      }
  }
?>