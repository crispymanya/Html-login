<?php
// Include configuration
require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? '';
$error = '';
$message = '';
$show_form = false;

if (empty($token)) {
    $error = "Invalid password reset request. No token provided.";
} else {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }

    $stmt = $conn->prepare("SELECT admin_id, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error = "Invalid or expired token. Please request a new reset link.";
    } else {
        $stmt->bind_result($admin_id, $expires_at);
        $stmt->fetch();

        if (time() > strtotime($expires_at)) {
            $error = "This token has expired. Please request a new reset link.";
        } else {
            // Token is valid, show the password reset form
            $show_form = true;
        }
    }
    $stmt->close();

    // Handle form submission
    if ($show_form && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($new_password) || $new_password !== $confirm_password) {
            $error = "Passwords do not match or are empty.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $conn->begin_transaction();
            try {
                // Update admin's password
                $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $update_stmt->bind_param('si', $hashed_password, $admin_id);
                $update_stmt->execute();

                // Delete the used token
                $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                $delete_stmt->bind_param('s', $token);
                $delete_stmt->execute();

                $conn->commit();

                $message = "Your password has been reset successfully. You can now <a href='login.php'>login</a>.";
                $show_form = false; // Hide form after successful reset

            } catch (Exception $e) {
                $conn->rollback();
                $error = "An error occurred while resetting your password. Please try again.";
                error_log("Password update failed for admin_id $admin_id: " . $e->getMessage());
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Your Password</h2>

    <?php if ($message): ?>
        <p style="color:green;"><?= $message ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
            <label for="password">New Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirm New Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</body>
</html>
