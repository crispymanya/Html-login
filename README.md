# PHP Contact Form & Admin Panel

## 1. Overview

This project provides a complete, self-contained system for handling a website contact form. It includes:

- A public-facing HTML contact form (`index.html`).
- A backend script that saves submissions to a MySQL database.
- Email notifications to an administrator upon new submissions.
- A secure admin panel to view and manage contact entries.
- A full admin authentication system: login, logout, and password reset functionality.
- An option to download all contact submissions as a CSV file.

The code has been structured to be easily configurable and deployable on any server with PHP and MySQL.

## 2. Server Requirements

Before you begin, ensure your server meets the following requirements:

- **PHP**: Version 7.4 or newer.
- **MySQL**: Version 5.7 or newer.
- **Composer**: The PHP dependency manager. ([Installation Instructions](https://getcomposer.org/doc/00-intro.md))
- A functioning **SMTP server** or email sending service (e.g., Gmail, SendGrid, Amazon SES) to handle outgoing emails.

## 3. Deployment Instructions

Follow these steps to deploy the application on your server.

### Step 3.1: Upload Files & Install Dependencies

1.  Upload all the files from this repository to your web server (e.g., into the `public_html` or `www` directory).
2.  Open a terminal or SSH into your server, navigate to the project directory, and run Composer to install the PHPMailer dependency:
    ```bash
    composer install
    ```
    This will create a `vendor` directory and an `autoload.php` file.

### Step 3.2: Create the Database

1.  Log in to your MySQL server (e.g., via phpMyAdmin or command line).
2.  Create a new database. You can name it anything you like (e.g., `contact_form_db`).
3.  Execute the following SQL queries to create the necessary tables (`admins`, `contacts`, `password_resets`):

    ```sql
    --
    -- Table structure for table `admins`
    --
    CREATE TABLE `admins` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    --
    -- Table structure for table `contacts`
    --
    CREATE TABLE `contacts` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `message` text NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    --
    -- Table structure for table `password_resets`
    --
    CREATE TABLE `password_resets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `admin_id` int(11) NOT NULL,
      `token` varchar(255) NOT NULL,
      `expires_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `admin_id` (`admin_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ```

### Step 3.3: Configure the Application

1.  Open the `config.php` file in a text editor.
2.  Update the placeholder values with your own settings. This is the **most important step**.

    - **Database Configuration:**
      ```php
      define('DB_HOST', 'localhost');
      define('DB_USERNAME', 'your_db_username'); // Your MySQL username
      define('DB_PASSWORD', 'your_db_password'); // Your MySQL password
      define('DB_NAME', 'your_db_name');       // The name of the database you created
      ```

    - **SMTP Mailer Configuration:**
      ```php
      define('SMTP_HOST', 'smtp.example.com');       // Your SMTP server address
      define('SMTP_USERNAME', 'your_email@example.com'); // Your SMTP username
      define('SMTP_PASSWORD', 'your_smtp_password');     // Your SMTP password
      define('SMTP_PORT', 587);                        // 587 for TLS, 465 for SSL
      define('SMTP_SECURE', 'tls');                    // 'tls' or 'ssl'
      ```

    - **Site Configuration:**
      ```php
      define('ADMIN_EMAIL', 'admin@yourdomain.com'); // Email to receive contact notifications
      define('SITE_EMAIL', 'noreply@yourdomain.com'); // "From" address for system emails
      define('BASE_URL', 'https://www.yourwebsite.com'); // Base URL for reset links
      ```

## 4. Initial Admin Setup

1.  After configuring the application, open your web browser and navigate to `register_admin.php`.
    - `https://www.yourwebsite.com/register_admin.php`
2.  Fill out the form to create your first administrator account.

3.  **VERY IMPORTANT:** After you have successfully created your admin account, you **MUST** delete the `register_admin.php` file from your server. Leaving this file on a live server is a major security risk, as it would allow anyone to create an admin account.

4.  You can now log in at `login.php`.

---

## 5. File Descriptions

- `index.html`: The main contact form page.
- `save_contact.php`: Processes the contact form, saves to DB, and sends email.
- `thank_you.html`: Page shown after successful form submission.
- `config.php`: All application settings. **Configure this file first.**
- `composer.json`: Defines the PHPMailer dependency for Composer.
- `login.php`: Admin login page.
- `logout.php`: Handles admin logout.
- `admin_panel.php`: Displays all contact submissions.
- `download_csv.php`: Handles CSV export of contacts.
- `register_admin.php`: **(DELETE AFTER USE)** Script to create a new admin.
- `forgot_password.php`: First step of the password reset flow.
- `reset_password.php`: Second step of the password reset flow.
