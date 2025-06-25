<?php
session_start();
include 'db_connect.php';
include 'session_handler.php';

// Enable error logging for debugging
error_log("Login attempt started");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    error_log("Login attempt for username: " . $username);

    if (empty($username) || empty($password)) {
        error_log("Login failed: Empty username or password");
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        error_log("User found in database: " . $user['username']);
        
        if (password_verify($password, $user['password'])) {
            error_log("Password verification successful for: " . $username);
            set_session($user);
            
            // Update last login time
            $update_sql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            echo json_encode(['status' => 'success', 'redirect' => '../html/menu.html']);
        } else {
            error_log("Password verification failed for: " . $username);
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
        }
    } else {
        error_log("User not found in database: " . $username);
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
    $stmt->close();
    $conn->close();
} else {
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}