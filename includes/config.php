<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'perfectopro';
$db_user = 'root';
$db_pass = 'mySQL@25';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Global site settings
define('SITE_NAME', 'perfecto');
define('SITE_URL', 'http://localhost/perfecto');
define('PRIMARY_COLOR', '#58CC02'); // Duolingo green

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirectTo($location) {
    header("Location: $location");
    exit;
}

function getFlashMessage() {
    $message = '';
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    return $message;
}

function setFlashMessage($message) {
    $_SESSION['flash_message'] = $message;
}

function sanitizeInput($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}
?>