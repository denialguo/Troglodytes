<?php
#Connection Values
$servername = "localhost";
$username = "root";
#  $password = '123456';
$password = ""; # Mr Millard's password is an empty string.

// Create connection to MySQL
$conn = new mysqli($servername, $username, $password);

// Test connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

# Draw data from database using the query() method
$conn->query("use Students23;"); #First select the active database
$sql = 'select fName, lName from Students where Period =1;'; #write query
$result = $conn->query($sql); #submit query and save data in $result

if ($result->num_rows > 0) {  #Check for presence of return data
    echo '<label>Choose a team member:</label><br>'; #echo sends data to browser
    echo '<select>'; # start a listbox with the html select element
  # enter data from each row of results using the fetch_assoc() method
  while($row = $result->fetch_assoc()){ #method returns one row at a time
    #Load the listbox using the <option> tag
    #Php uses a period (.) to concatenate strings.
    #$row data is already string but html must be added in as quoted string values
    echo '<option>' . $row["fName"] . ' ' . $row["lName"] . '</option>';
  }
   echo '</select>'; #close listbox
 } else {
  echo 'No one found';
}
$conn->close();
?>