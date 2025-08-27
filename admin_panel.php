<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = "karveweb_testusr";
$password = "!!Hulk@123!!";
$dbname = "karveweb_test";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM contacts");
?>

<!DOCTYPE html>
<html>
<head><title>Admin Panel</title></head>
<body>
<h2>Contact Form Entries</h2>
<p><a href="download_csv.php">Download CSV</a> | <a href="logout.php">Logout</a></p>
<table border="1" cellpadding="5" cellspacing="0">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th></tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['message']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>

<?php
$conn->close();
?>
