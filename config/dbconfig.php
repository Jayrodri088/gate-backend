<?php
// Database configuration
$host = 'localhost';
$db_name = 'gate_pass';
$username = 'root';
$password = 'Lolamarsh@123';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
