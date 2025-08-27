<?php
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
    // Database insert was successful. Now, attempt to send the email.
    $to = 'prasadkarve4@gmail.com'; // This should be the admin's email address
    $subject = "New Contact Form Submission from " . $name;
    $headers = "From: " . $email . "\r\n" .
               "Reply-To: " . $email . "\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n" .
               "X-Mailer: PHP/" . phpversion();

    $body = "You have received a new message from your website contact form.\n\n";
    $body .= "Name: " . $name . "\n";
    $body .= "Email: " . $email . "\n\n";
    $body .= "Message:\n" . $message . "\n";

    // Attempt to send mail and log error on failure
    if (!mail($to, $subject, $body, $headers)) {
        error_log("Contact form email failed to send. To: $to, Subject: $subject");
    }

    // Redirect to a 'thank you' page to prevent re-submission on refresh
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
