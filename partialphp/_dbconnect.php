<?php 
$server = "localhost";
$username = "if0_40011685";
$password = "1cMiYIrI0CQ";
$database = "if0_40011685_db_bloodbank";

$conn = mysqli_connect($server, $username,$password,$database);
if($conn) {
echo "";
} else {
  die("ERROR".mysqli_connect_error());
}

?>