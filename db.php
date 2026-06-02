<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fashion"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add this line so your old gown scripts using $con still work!
$con = $conn; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>