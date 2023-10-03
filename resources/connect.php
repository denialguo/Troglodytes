<?php
  require_once 'password.php';
  /* Attempt to connect to MySQL database */
  $conn = mysqli_connect('localhost', 'root',$DB_PASSWORD);
   
  // Check connection
  if($conn === false){
      die('ERROR: Could not connect. ' . mysqli_connect_error());
  }
  
?>