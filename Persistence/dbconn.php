<?php
// Database connection for XAMPP (MySQL)
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'anis';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
