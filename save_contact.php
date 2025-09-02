<?php
// Include configuration and autoloader
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create database connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $message);

// Sanitize and set parameters from POST request
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$message = htmlspecialchars($_POST['message']);

// Execute the statement to insert into database
if ($stmt->execute()) {
    // Database insert was successful. Now, attempt to send the email.
    $mail = new PHPMailer(true);

    try {
        // --- SMTP SERVER SETTINGS from config.php ---
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = 0; // Set to 2 for debugging, 0 for production

        // --- RECIPIENTS (Admin) ---
        $mail->setFrom(SMTP_USERNAME, 'Contact Form'); // Use SMTP username as "From"
        $mail_user = SMTP_USERNAME;
        $mail->addAddress(ADMIN_EMAIL);               // The admin's email from config
        $mail->addReplyTo($email, $name);             // Set the Reply-To to the user's email

        // --- CONTENT (Admin) ---
        $mail->isHTML(false);
        $mail->Subject = "New Contact Form Submission from " . $name;
        $admin_body = "You have received a new message from your website contact form.\n\n" .
                      "Name: " . $name . "\n" .
                      "Email: " . $email . "\n\n" .
                      "Message:\n" . $message . "\n";
        $mail->Body = $admin_body;

        // Send to admin
        $mail->send();

        // --- Clear recipients and settings for the next email ---
        $mail->clearAllRecipients();
        $mail->clearReplyTos();

        // --- RECIPIENTS (User) ---
        $mail->setFrom($mail_user, 'Karve Web Solutions'); // From admin
        $mail->addAddress($email, $name); // Send to the user who submitted the form

        // --- CONTENT (User) ---
        $mail->Subject = 'Thank you for contacting us!';
        $user_body = "Dear " . $name . ",\n\n" .
                     "Thank you for contacting us. We have received your message and will get back to you shortly.\n\n" .
                     "Best regards,\n" .
                     "The Team";
        $mail->Body = $user_body;

        // Send to user
        $mail->send();

    } catch (Exception $e) {
        // Email failed. Log the error but don't show it to the user.
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    // Redirect to a 'thank you' page.
    header("Location: thank_you.html");
    exit();

} else {
    // Database insert failed.
    error_log("Database insert failed: " . $stmt->error);
    die("There was an error saving your message. Please try again later.");
}

$stmt->close();
$conn->close();
?>
