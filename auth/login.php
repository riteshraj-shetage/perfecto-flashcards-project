<?php
include_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/dashboard/user.php');
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // Validate input
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!validateEmail($email)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        }
        
        // Check rate limiting
        if (!checkRateLimit($email)) {
            $errors[] = "Too many login attempts. Please try again in " . (LOGIN_LOCKOUT_TIME / 60) . " minutes.";
        }
        
        // Proceed if no errors
        if (empty($errors)) {
            try {
                // Query the database using prepared statement
                $sql = "SELECT id, username, email, password, role FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Verify password (supports both new hashed and old plain passwords for migration)
                        $password_valid = false;
                        if (password_get_info($user['password'])['algo'] !== null) {
                            // New hashed password
                            $password_valid = verifyPassword($password, $user['password']);
                        } else {
                            // Old plain password - check and upgrade
                            if ($password === $user['password']) {
                                $password_valid = true;
                                // Upgrade to hashed password
                                $new_hash = hashPassword($password);
                                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                                $update_stmt = $conn->prepare($update_sql);
                                if ($update_stmt) {
                                    $update_stmt->bind_param("si", $new_hash, $user['id']);
                                    $update_stmt->execute();
                                    $update_stmt->close();
                                }
                            }
                        }
                        
                        if ($password_valid) {
                            // Regenerate session ID for security
                            session_regenerate_id(true);
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['login_time'] = time();
                            
                            // Log successful login
                            recordLoginAttempt($email, true);
                            logActivity($user['id'], 'login', 'Successful login');
                            
                            // Update last activity
                            $update_activity = "UPDATE users SET last_activity_date = CURDATE() WHERE id = ?";
                            $activity_stmt = $conn->prepare($update_activity);
                            if ($activity_stmt) {
                                $activity_stmt->bind_param("i", $user['id']);
                                $activity_stmt->execute();
                                $activity_stmt->close();
                            }
                            
                            // Redirect to appropriate dashboard
                            if ($user['role'] === 'admin') {
                                redirectTo(SITE_URL . '/dashboard/admin.php');
                            } else {
                                redirectTo(SITE_URL . '/dashboard/user.php');
                            }
                        } else {
                            $errors[] = "Invalid email or password";
                            recordLoginAttempt($email, false);
                            logActivity(0, 'failed_login', "Failed login attempt for email: $email");
                        }
                    } else {
                        $errors[] = "Invalid email or password";
                        recordLoginAttempt($email, false);
                        logActivity(0, 'failed_login', "Failed login attempt for non-existent email: $email");
                    }
                    
                    $stmt->close();
                } else {
                    $errors[] = "Database error. Please try again.";
                    logError("Failed to prepare login statement", ['email' => $email]);
                }
            } catch (Exception $e) {
                $errors[] = "An error occurred. Please try again.";
                logError("Login exception: " . $e->getMessage(), ['email' => $email]);
            }
        }
    }
}

include_once '../includes/header.php';
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Login to <span class="brand">perfecto</span></h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required 
                           autocomplete="email"
                           aria-describedby="email-error">
                    <div id="email-error" class="field-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           aria-describedby="password-error">
                    <div id="password-error" class="field-error"></div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="login-btn">
                    <span class="btn-text">Login</span>
                    <span class="btn-loading" style="display: none;">Logging in...</span>
                </button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/auth/register.php">Sign up</a></p>
                <p><a href="<?php echo SITE_URL; ?>/auth/forgot-password.php">Forgot your password?</a></p>
            </div>
            
            <div class="demo-credentials">
                <h4>Demo Credentials:</h4>
                <p><strong>Admin:</strong> admin@perfecto.com / admin123</p>
                <p><strong>User:</strong> user@perfecto.com / user123</p>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const submitBtn = document.getElementById('login-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    // Client-side validation
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function showFieldError(field, message) {
        const errorDiv = document.getElementById(field + '-error');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        document.getElementById(field).classList.add('error');
    }
    
    function clearFieldError(field) {
        const errorDiv = document.getElementById(field + '-error');
        errorDiv.style.display = 'none';
        document.getElementById(field).classList.remove('error');
    }
    
    emailInput.addEventListener('blur', function() {
        if (this.value && !validateEmail(this.value)) {
            showFieldError('email', 'Please enter a valid email address');
        } else {
            clearFieldError('email');
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (this.value) {
            clearFieldError('password');
        }
    });
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate email
        if (!emailInput.value) {
            showFieldError('email', 'Email is required');
            isValid = false;
        } else if (!validateEmail(emailInput.value)) {
            showFieldError('email', 'Please enter a valid email address');
            isValid = false;
        } else {
            clearFieldError('email');
        }
        
        // Validate password
        if (!passwordInput.value) {
            showFieldError('password', 'Password is required');
            isValid = false;
        } else {
            clearFieldError('password');
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>