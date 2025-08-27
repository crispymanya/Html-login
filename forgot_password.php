<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $conn = new mysqli('localhost', 'root', '', 'contactdb');
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    // Find admin id by email
    $stmt = $conn->prepare('SELECT id FROM admins WHERE username = ?');
    $stmt->bind_param('s', $email); // Assuming username is email; adjust if needed
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id);
        $stmt->fetch();

        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save token to password_resets table, after deleting old tokens for this admin
        $conn->query("DELETE FROM password_resets WHERE admin_id = $admin_id");
        $stmt2 = $conn->prepare("INSERT INTO password_resets (admin_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt2->bind_param('iss', $admin_id, $token, $expires);
        $stmt2->execute();

        // Send reset email using PHPMailer (adjust your SMTP settings)
        require 'vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // SMTP Config
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com';
            $mail->Password = 'your_email_password';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your_email@example.com', 'Site Admin');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click the link to reset your password: <a href='https://yourdomain.com/reset_password.php?token=$token'>Reset Password</a>";

            $mail->send();
            echo "Password reset link sent to your email.";
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "No admin found with that email.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head><title>Forgot Password</title></head>
<body>
<h2>Forgot Password</h2>
<form method="POST" action="">
  <label>Enter your username/email:</label><br>
  <input type="email" name="email" required><br>
  <button type="submit">Send Reset Link</button>
</form>
</body>
</html>
