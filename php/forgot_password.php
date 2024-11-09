<?php
session_start();
include 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    try {
       
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email not found in our records.']);
            exit;
        }
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
       
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
    
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, otp, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $otp, $expiry);
        $stmt->execute();

    
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'praveersingh.j@somaiya.edu'; 
        $mail->Password = ''; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('praveersingh.j@somaiya.edu', 'Crave Courier');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body = "Your OTP for password reset is: <strong>$otp</strong><br>This OTP will expire in 15 minutes.";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'OTP sent to your email.']);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        #message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        
       
        <form id="emailForm">
            <div class="form-group">
                <label for="email">Enter your email address:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Send OTP</button>
        </form>

        <form id="otpForm" style="display: none;">
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required maxlength="6">
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>

        <div id="message"></div>
    </div>

    <script>
        document.getElementById('emailForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const messageDiv = document.getElementById('message');
            const submitButton = e.target.querySelector('button');
            
            try {
                submitButton.disabled = true;
                submitButton.textContent = 'Sending...';
                
                const response = await fetch('forgot_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}`
                });
                
                const data = await response.json();
                
                messageDiv.textContent = data.message;
                messageDiv.className = data.status === 'success' ? 'success' : 'error';
                
                if (data.status === 'success') {
                    document.getElementById('emailForm').style.display = 'none';
                    document.getElementById('otpForm').style.display = 'block';
                }
            } catch (error) {
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'error';
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Send OTP';
            }
        });

        document.getElementById('otpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const otp = document.getElementById('otp').value;
            const new_password = document.getElementById('new_password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            const messageDiv = document.getElementById('message');
            
            if (new_password !== confirm_password) {
                messageDiv.textContent = 'Passwords do not match!';
                messageDiv.className = 'error';
                return;
            }
            
            try {
                messageDiv.textContent = 'Password reset successfully!';
                messageDiv.className = 'success';

                setTimeout(() => {
            window.location.href = '../html/login.html';
        }, 2000);
        
            } catch (error) {
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'error';
            }
        });
    </script>
</body>
</html>