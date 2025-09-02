<?php
// Include configuration
require_once __DIR__ . '/config.php';

session_start();

// If already logged in, redirect to admin panel
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_panel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }

    $email_input = $_POST['email'];
    $password_input = $_POST['password'];

    // Prepare statement to find admin by email
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email_input);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verify password
        if (password_verify($password_input, $hashed_password)) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $id;
            header('Location: admin_panel.php');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
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
    <title>Admin Login</title>
</head>
<body>
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
    <p><a href="forgot_password.php">Forgot Password?</a></p>
</body>
</html>
