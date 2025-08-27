<?php
$servername = "localhost";
$username = "karveweb_testusr";
$password = "!!Hulk@123!!";
$dbname = "karveweb_test";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $message);

$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$message = htmlspecialchars($_POST['message']);

$stmt->execute();

$to = 'prasadkarve4@gmail.com'; // Replace with your email
$subject = "New Contact Form Submission from $name";
$headers = "From: $email\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8\r\n";

$body = "You have received a new message from your website contact form.\n\n";
$body .= "Name: $name\nEmail: $email\n\nMessage:\n$message\n";

if (mail($to, $subject, $body, $headers)) {
    echo "Thank you for contacting us! We will get back to you shortly.";
} else {
    echo "There was an error sending your message. Please try again later.";
}

$stmt->close();
$conn->close();
?>
