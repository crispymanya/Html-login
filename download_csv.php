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
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=contact_entries.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Message']);

$result = $conn->query("SELECT * FROM contacts");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['id'], $row['name'], $row['email'], $row['message']]);
}

fclose($output);
$conn->close();
exit();
?>
