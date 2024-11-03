<?php
session_start();
include 'db_connect.php';
include 'session_handler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
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
        if (password_verify($password, $user['password'])) {
            set_session($user);
            
            // Set remember me cookie if requested
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                
                // Store token in database
                $update_sql = "UPDATE users SET remember_token = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $token, $user['id']);
                $update_stmt->execute();
            }

            echo json_encode(['status' => 'success', 'redirect' => 'dashboard.php']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    $stmt->close();
    $conn->close();
}
?>