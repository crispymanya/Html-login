<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: admin_panel.php');
    exit;
}

$servername = "localhost";
$username = "karveweb_testusr";
$password = "!!Hulk@123!!";
$dbname = "karveweb_test";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username_input = $_POST['username'];
    $password_input = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();
        if (password_verify($password_input, $hashed_password)) {
            $_SESSION['admin_id'] = $id;
            header('Location: admin_panel.php');
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head><title>Admin Login</title></head>
<body>
<h2>Admin Login</h2>
<?php if ($error): ?>
<p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="POST" action="">
    <label>Username:</label><br>
    <input type="text" name="username" required><br>
    <label>Password:</label><br>
    <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
</body>
</html>
