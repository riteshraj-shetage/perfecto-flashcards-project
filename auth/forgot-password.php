<?php
include_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/dashboard/user.php');
}

$message = '';
$errors = [];

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $email = sanitizeInput($_POST['email']);
        
        // Validate email
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!validateEmail($email)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($errors)) {
            try {
                // Check if email exists
                $sql = "SELECT id, username FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Generate reset token
                        $reset_token = bin2hex(random_bytes(32));
                        $reset_expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour from now
                        
                        // Store reset token in database
                        $update_sql = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        
                        if ($update_stmt) {
                            $update_stmt->bind_param("sss", $reset_token, $reset_expires, $email);
                            
                            if ($update_stmt->execute()) {
                                // In a real application, you would send an email here
                                // For demo purposes, we'll just show the reset link
                                $reset_url = SITE_URL . "/auth/reset-password.php?token=" . $reset_token;
                                
                                logActivity($user['id'], 'password_reset_request', 'Password reset requested');
                                
                                $message = "Password reset link has been generated. In a production environment, this would be sent to your email. For demo purposes, here's your reset link: <a href='$reset_url'>Reset Password</a>";
                            } else {
                                $errors[] = "Failed to generate reset token. Please try again.";
                            }
                            
                            $update_stmt->close();
                        } else {
                            $errors[] = "Database error. Please try again.";
                        }
                    } else {
                        // Don't reveal if email exists or not for security
                        $message = "If the email address exists in our system, you will receive a password reset link shortly.";
                    }
                    
                    $stmt->close();
                } else {
                    $errors[] = "Database error. Please try again.";
                }
            } catch (Exception $e) {
                $errors[] = "An error occurred. Please try again.";
                logError("Forgot password exception: " . $e->getMessage(), ['email' => $email]);
            }
        }
    }
}

include_once '../includes/header.php';
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Reset Password</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="success-message">
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required 
                           autocomplete="email"
                           aria-describedby="email-help">
                    <div id="email-help" class="field-help">Enter your email address to receive a password reset link</div>
                </div>
                
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </form>
            
            <div class="auth-links">
                <p><a href="<?php echo SITE_URL; ?>/auth/login.php">Back to Login</a></p>
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/auth/register.php">Sign up</a></p>
            </div>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>