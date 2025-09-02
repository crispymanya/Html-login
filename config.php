<?php
// Enable error reporting for debugging, disable in production
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); // Ensure this path is writable

// --- DATABASE CONFIGURATION ---
// Replace with your actual database credentials
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'your_db_username');
define('DB_PASSWORD', 'your_db_password');
define('DB_NAME', 'your_db_name');

// --- SMTP MAILER CONFIGURATION ---
// Replace with your actual SMTP credentials from your email provider
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'prasad@karveweb.solutions');
define('SMTP_PASSWORD', 'xplo xvat wbsq qupj');
define('SMTP_PORT', 587); // Common port for TLS. Use 465 for SSL.
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

// --- SITE CONFIGURATION ---
// The email address where contact form submissions will be sent
define('ADMIN_EMAIL', 'admin@test.karveweb.solutions');

// The "From" address for emails sent by the system.
// It's recommended to use an address from the same domain as your site.
define('SITE_EMAIL', 'noreply@test.karveweb.solutions');

// The base URL of your site, for creating password reset links.
// Example: 'https://www.yourwebsite.com'
define('BASE_URL', 'http://localhost');
?>
