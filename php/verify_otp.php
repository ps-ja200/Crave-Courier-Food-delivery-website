<?php
session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['email']; 

    if ($new_password !== $confirm_password) {
        $message = 'Passwords do not match';
    } else {
        
        $stmt = $conn->prepare("SELECT * FROM password_reset_tokens WHERE email = ? AND otp = ? AND expires_at > NOW()");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = 'Invalid or expired OTP';
        } else {
           
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);

            if ($update_stmt->execute()) {
                $message = 'Password successfully reset';
                $delete_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
                $delete_stmt->bind_param("s", $email);
                $delete_stmt->execute();
            } else {
                $message = 'Error resetting password';
            }
        }
    }
    echo json_encode(['status' => $message === 'Password successfully reset' ? 'success' : 'error', 'message' => $message]);
}
?>
