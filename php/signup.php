<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic form validation (similar to JS validation)
    if (strlen($username) < 3 || strlen($username) > 20 || !preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        echo "Invalid username!";
        exit;
    }
    if (strlen($password) < 8 || strlen($password) > 20) {
        echo "Password must be between 8 and 20 characters!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   
    $checkQuery = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username or Email already exists!";
    } else {
       
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "Registration successful! <a href='../html/login.html'>Login here</a>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
    $stmt->close();
    $conn->close();
}
?>
