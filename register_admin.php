<?php
// Example script to create an admin user with hashed password
// Run once, then delete or restrict access for security

$servername = "localhost";
$username = "karveweb_testusr";
$password = "!!Hulk@123!!";
$dbname = "karveweb_test";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = $_POST['username'];
    $password_input = $_POST['password'];
    $hash = password_hash($password_input, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username_input, $hash);

    if ($stmt->execute()) {
        echo "Admin registered successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head><title>Register Admin</title></head>
<body>
<h2>Register Admin (Run Once)</h2>
<form method="POST" action="">
  <label>Username:</label><br>
  <input type="text" name="username" required><br>
  <label>Password:</label><br>
  <input type="password" name="password" required><br>
  <button type="submit">Register</button>
</form>
</body>
</html>
