<?php
// Include configuration
require_once __DIR__ . '/config.php';

session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Set headers for CSV download
$filename = "contact_entries_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV header row
fputcsv($output, ['ID', 'Name', 'Email', 'Message', 'Submitted At']);

// Fetch data from the database and write to CSV
$result = $conn->query("SELECT id, name, email, message, created_at FROM contacts ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['email'],
            $row['message'],
            $row['created_at']
        ]);
    }
}

fclose($output);
$conn->close();
exit();
?>
