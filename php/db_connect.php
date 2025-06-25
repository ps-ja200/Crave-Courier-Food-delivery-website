<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crave_courier";
$host = $servername; // For compatibility

// MySQLi connection (for most PHP scripts)
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// PDO connection (for advanced features and prepared statements)
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Don't die here, let scripts handle PDO errors gracefully
    $pdo = null;
}
?>