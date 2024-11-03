<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';// If using Composer
// Or include the PHPMailer files manually if not using Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header("Location: contact.html?status=error&message=All fields are required");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: contact.html?status=error&message=Invalid email format");
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'praveersingh.j@somaiya.edu'; // Your Gmail address
        $mail->Password = 'sjgf vnen eebl mrir'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($email, $name); // Use the email and name from the form
        $mail->addAddress('praveersingh.j@somaiya.edu', 'Praveer'); // Where you want to receive the emails
        $mail->addReplyTo($email, $name); // Allow replying to the sender

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Submission: $subject";
        
        // Email body
        $emailBody = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        ";
        
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags($emailBody); // Plain text version

        $mail->send();
        header("Location: /Food-delivery-website/html/contact.html?status=success");
        exit();

    } catch (Exception $e) {
        header("/Food-delivery-website/html/contact.html?status=error&message=" . urlencode("Message could not be sent. Mailer Error: {$mail->ErrorInfo}"));
        exit();
    }
} else {
    header("Location: /Food-delivery-website/html/contact.html");
    exit();
}
?>