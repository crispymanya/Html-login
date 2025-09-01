<?php
// Include configuration and autoloader
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }

    // Find admin id by email
    $stmt = $conn->prepare('SELECT id FROM admins WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id);
        $stmt->fetch();

        // Generate a secure token
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", time() + 3600); // Token expires in 1 hour

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Delete any old tokens for this user
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE admin_id = ?");
            $delete_stmt->bind_param('i', $admin_id);
            $delete_stmt->execute();

            // Save new token to the password_resets table
            $insert_stmt = $conn->prepare("INSERT INTO password_resets (admin_id, token, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->bind_param('iss', $admin_id, $token, $expires);
            $insert_stmt->execute();

            // Send reset email using PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SITE_EMAIL, 'Site Admin');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $reset_link = rtrim(BASE_URL, '/') . '/reset_password.php?token=' . $token;
            $mail->Body = "Click the link to reset your password: <a href='$reset_link'>$reset_link</a>";

            $mail->send();

            // Commit transaction
            $conn->commit();
            $message = "A password reset link has been sent to your email address.";

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Password reset failed for $email. Mailer Error: {$mail->ErrorInfo}");
            $error = "Could not send the reset email. Please try again later.";
        }
    } else {
        // To prevent user enumeration, show a generic message even if the email doesn't exist.
        $message = "If an account with that email exists, a password reset link has been sent.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <p>Enter your email address and we will send you a link to reset your password.</p>

    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
        <label for="email">Your Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        <button type="submit">Send Reset Link</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</body>
</html>
