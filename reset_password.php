<?php
if (!isset($_GET['token'])) {
    die('Invalid request');
}

$token = $_GET['token'];
$conn = new mysqli('localhost', 'root', '', 'contactdb');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$stmt = $conn->prepare("SELECT admin_id, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die('Invalid or expired token');
}

$stmt->bind_result($admin_id, $expires_at);
$stmt->fetch();

if (strtotime($expires_at) < time()) {
    die('Token expired');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
    $stmt->bind_param('si', $hashed_password, $admin_id);
    $stmt->execute();

    // Delete token after use
    $conn->query("DELETE FROM password_resets WHERE token = '$token'");

    echo "Password has been reset. You can now <a href='login.php'>login</a>.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
<h2>Reset Password</h2>
<form method="POST" action="">
  <label>New Password:</label><br>
  <input type="password" name="password" required><br>
  <button type="submit">Reset Password</button>
</form>
</body>
</html>
