<?php
  // This is the same as the other file except it uses the database "Troglodytes"

  $conn = mysqli_connect('localhost', 'root','');
   
  // Check connection
  if($conn === false){
      die('ERROR: Could not connect. ' . mysqli_connect_error());
  } else {
    $conn->query("USE Troglodytes;");
  }
  
?>