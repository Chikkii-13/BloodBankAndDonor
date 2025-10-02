<?php
session_start();
$loginError = "";
$formSubmitted = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formSubmitted = true;
    include("partialphp/_dbconnect.php");
}

header("Location: loginpage.php");
exit();

?>