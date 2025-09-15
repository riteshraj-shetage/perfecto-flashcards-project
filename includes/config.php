<?php
// Environment configuration
$env = $_ENV['APP_ENV'] ?? 'development';

// Database configuration
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = $_ENV['DB_NAME'] ?? 'perfecto_db';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASS'] ?? '';

// Create connection with proper error handling
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    if ($env === 'development') {
        die("Connection failed: " . $conn->connect_error);
    } else {
        die("Database connection error. Please try again later.");
    }
}

// Set charset for security
$conn->set_charset("utf8mb4");

// Global site settings
define('SITE_NAME', 'perfecto');
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/perfecto');
define('PRIMARY_COLOR', '#58CC02'); // Duolingo green
define('APP_ENV', $env);

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 50);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Session configuration with security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
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

function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlashType() {
    $type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_type']);
    return $type;
}

function sanitizeInput($input) {
    global $conn;
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return $conn->real_escape_string(trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8')));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUsername($username) {
    $length = strlen($username);
    return $length >= USERNAME_MIN_LENGTH && 
           $length <= USERNAME_MAX_LENGTH && 
           preg_match('/^[a-zA-Z0-9_-]+$/', $username);
}

function validatePassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH &&
           preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateCSRFToken() {
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function logError($message, $context = []) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $log_message .= " - Context: " . json_encode($context);
    }
    error_log($log_message, 3, 'logs/error.log');
}

function logActivity($user_id, $action, $details = '') {
    global $conn;
    $user_id = (int)$user_id;
    $action = sanitizeInput($action);
    $details = sanitizeInput($details);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

function checkRateLimit($identifier, $max_attempts = MAX_LOGIN_ATTEMPTS, $time_window = LOGIN_LOCKOUT_TIME) {
    global $conn;
    $identifier = sanitizeInput($identifier);
    $time_threshold = date('Y-m-d H:i:s', time() - $time_window);
    
    $sql = "SELECT COUNT(*) as attempts FROM login_attempts 
            WHERE identifier = ? AND created_at > ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $identifier, $time_threshold);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['attempts'] < $max_attempts;
    }
    return true;
}

function recordLoginAttempt($identifier, $success = false) {
    global $conn;
    $identifier = sanitizeInput($identifier);
    $success = $success ? 1 : 0;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $sql = "INSERT INTO login_attempts (identifier, success, ip_address, created_at) 
            VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sis", $identifier, $success, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Clean old login attempts (run periodically)
function cleanOldLoginAttempts() {
    global $conn;
    $time_threshold = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);
    
    $sql = "DELETE FROM login_attempts WHERE created_at < ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $time_threshold);
        $stmt->execute();
        $stmt->close();
    }
}

// Image upload validation
function validateImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'No file uploaded'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'File size too large (max 5MB)'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'error' => 'Invalid file type'];
    }
    
    return ['valid' => true];
}

// Auto-clean old sessions periodically
if (rand(1, 100) <= 1) { // 1% chance
    cleanOldLoginAttempts();
}
?>