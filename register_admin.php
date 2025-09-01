<?php
// Include configuration
require_once __DIR__ . '/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }

    $email_input = $_POST['email'];
    $password_input = $_POST['password'];

    // It's a good idea to check if the email is already registered
    $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Error: An admin with this email address already exists.";
    } else {
        // Hash the password
        $hash = password_hash($password_input, PASSWORD_DEFAULT);

        // Insert the new admin
        $stmt_insert = $conn->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
        $stmt_insert->bind_param("ss", $email_input, $hash);

        if ($stmt_insert->execute()) {
            $message = "Admin registered successfully. You should now DELETE this script.";
        } else {
            $message = "Error: " . $stmt_insert->error;
            error_log("Admin registration failed: " . $stmt_insert->error);
        }
        $stmt_insert->close();
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
    <title>Register Admin</title>
</head>
<body>
    <h2>Register Admin (Run Once & Then Delete)</h2>
    <p style="color:red; font-weight:bold;">
        SECURITY WARNING: This script is for initial setup only.
        You must delete it from your server immediately after creating your first admin account.
    </p>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="register_admin.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
