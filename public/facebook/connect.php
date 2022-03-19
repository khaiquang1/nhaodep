<?php
$servername = "localhost";
$username = "salesdy";
$password = "salesdy@123";
$dbname = "salesdy";
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
mysqli_set_charset($conn, "utf8");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
