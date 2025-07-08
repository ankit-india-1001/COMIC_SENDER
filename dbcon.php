<?php
$host = "localhost";         // Host name
$username = "root";          // MySQL username (default for XAMPP)
$password = "";              // MySQL password (empty by default)
$database = "emails";  // Change this to your database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
