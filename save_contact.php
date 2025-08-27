<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load the PHPMailer library files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$servername = "localhost";
$username = "karveweb_testusr";
$password = "!!Hulk@123!!";
$dbname = "karveweb_test";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    // Log database connection error for debugging
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $message);

// Sanitize and set parameters
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$message = htmlspecialchars($_POST['message']);

// Execute the statement
if ($stmt->execute()) {
    // Database insert was successful. Now, attempt to send the email using PHPMailer.
    $mail = new PHPMailer(true); // Passing `true` enables exceptions

    try {
        // --- SMTP SERVER SETTINGS ---
        // IMPORTANT: You must replace these placeholder values with your actual SMTP credentials.
        // You can get these from your email provider (e.g., Gmail, Outlook, SendGrid, etc.).

        // Enable verbose debug output (optional, useful for troubleshooting)
        // Set to 0 for production use
        $mail->SMTPDebug = 0; // SMTP::DEBUG_SERVER;

        // Set mailer to use SMTP
        $mail->isSMTP();

        // Specify main and backup SMTP servers
        $mail->Host = 'smtp.example.com'; // e.g., 'smtp.gmail.com'

        // Enable SMTP authentication
        $mail->SMTPAuth = true;

        // SMTP username
        $mail->Username = 'your_email@example.com'; // Your SMTP username

        // SMTP password
        $mail->Password = 'your_smtp_password'; // Your SMTP password

        // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // TCP port to connect to
        $mail->Port = 587; // Use 465 for `PHPMailer::ENCRYPTION_SMTPS`

        // --- RECIPIENTS ---
        $admin_email = 'prasadkarve4@gmail.com'; // The admin's email address
        $mail->setFrom($email, $name); // Sender's email and name (from form)
        $mail->addAddress($admin_email, 'Admin'); // Add a recipient (the admin)
        $mail->addReplyTo($email, $name); // Set the Reply-To to the user's email

        // --- CONTENT ---
        $mail->isHTML(false); // Set email format to plain text
        $mail->Subject = "New Contact Form Submission from " . $name;

        $body = "You have received a new message from your website contact form.\n\n";
        $body .= "Name: " . $name . "\n";
        $body .= "Email: " . $email . "\n\n";
        $body .= "Message:\n" . $message . "\n";
        $mail->Body = $body;

        $mail->send();
        // Email sent successfully

    } catch (Exception $e) {
        // Email failed to send. Log the detailed error message.
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    // Redirect to a 'thank you' page regardless of email sending status.
    // This prevents re-submission on refresh.
    header("Location: thank_you.html");
    exit();

} else {
    // Database insert failed. Log the error for debugging.
    error_log("Database insert failed: " . $stmt->error);
    die("There was an error saving your message. Please try again later.");
}

$stmt->close();
$conn->close();
?>
