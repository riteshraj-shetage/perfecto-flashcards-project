<?php
include_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/dashboard/user.php');
}

$errors = [];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate username
        if (empty($username)) {
            $errors[] = "Username is required";
        } elseif (!validateUsername($username)) {
            $errors[] = "Username must be 3-50 characters and contain only letters, numbers, underscores, and hyphens";
        }
        
        // Validate email
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!validateEmail($email)) {
            $errors[] = "Invalid email format";
        }
        
        // Validate password
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (!validatePassword($password)) {
            $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters and contain uppercase, lowercase, and numeric characters";
        }
        
        // Validate password confirmation
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        // Check if username already exists
        if (empty($errors)) {
            $check_username_sql = "SELECT id FROM users WHERE username = ?";
            $stmt = $conn->prepare($check_username_sql);
            if ($stmt) {
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $errors[] = "Username already taken";
                }
                $stmt->close();
            }
        }
        
        // Check if email already exists
        if (empty($errors)) {
            $check_email_sql = "SELECT id FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_email_sql);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $errors[] = "Email already registered";
                }
                $stmt->close();
            }
        }
        
        // Proceed if no errors
        if (empty($errors)) {
            try {
                // Hash the password
                $hashed_password = hashPassword($password);
                $role = 'user';
                
                // Begin transaction
                $conn->begin_transaction();
                
                // Insert new user
                $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                    
                    if ($stmt->execute()) {
                        $user_id = $conn->insert_id;
                        
                        // Log registration activity
                        logActivity($user_id, 'register', 'User registered successfully');
                        
                        // Initialize user settings
                        $settings_sql = "INSERT INTO user_streaks (user_id, streak_date, xp_earned) VALUES (?, CURDATE(), 0)";
                        $settings_stmt = $conn->prepare($settings_sql);
                        if ($settings_stmt) {
                            $settings_stmt->bind_param("i", $user_id);
                            $settings_stmt->execute();
                            $settings_stmt->close();
                        }
                        
                        // Commit transaction
                        $conn->commit();
                        
                        // Set flash message
                        setFlashMessage("Registration successful! Please log in to continue.", "success");
                        
                        // Redirect to login page
                        redirectTo(SITE_URL . '/auth/login.php');
                    } else {
                        $conn->rollback();
                        $errors[] = "Registration failed. Please try again.";
                        logError("User registration failed", ['username' => $username, 'email' => $email]);
                    }
                    
                    $stmt->close();
                } else {
                    $conn->rollback();
                    $errors[] = "Database error. Please try again.";
                    logError("Failed to prepare registration statement");
                }
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "An error occurred during registration. Please try again.";
                logError("Registration exception: " . $e->getMessage(), ['username' => $username, 'email' => $email]);
            }
        }
    }
}

include_once '../includes/header.php';
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-card">
            <h1>Join <span class="brand">perfecto</span></h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form" id="register-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required 
                           minlength="<?php echo USERNAME_MIN_LENGTH; ?>"
                           maxlength="<?php echo USERNAME_MAX_LENGTH; ?>"
                           pattern="[a-zA-Z0-9_-]+"
                           autocomplete="username"
                           aria-describedby="username-error username-help">
                    <div id="username-help" class="field-help">3-50 characters, letters, numbers, underscores, and hyphens only</div>
                    <div id="username-error" class="field-error"></div>
                </div>
                
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
                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                           autocomplete="new-password"
                           aria-describedby="password-error password-help">
                    <div id="password-help" class="field-help">At least <?php echo PASSWORD_MIN_LENGTH; ?> characters with uppercase, lowercase, and numbers</div>
                    <div id="password-error" class="field-error"></div>
                    <div class="password-strength" id="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <div class="strength-text" id="strength-text">Password strength</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           autocomplete="new-password"
                           aria-describedby="confirm-password-error">
                    <div id="confirm-password-error" class="field-error"></div>
                </div>
                
                <button type="submit" class="btn btn-primary" id="register-btn">
                    <span class="btn-text">Create Account</span>
                    <span class="btn-loading" style="display: none;">Creating Account...</span>
                </button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/auth/login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const submitBtn = document.getElementById('register-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    // Form inputs
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    // Password strength elements
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    
    // Validation functions
    function validateUsername(username) {
        const re = /^[a-zA-Z0-9_-]+$/;
        return username.length >= 3 && 
               username.length <= 50 && 
               re.test(username);
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validatePassword(password) {
        return password.length >= 8 &&
               /[a-z]/.test(password) &&
               /[A-Z]/.test(password) &&
               /\d/.test(password);
    }
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/\d/.test(password)) strength += 1;
        if (/[^a-zA-Z\d]/.test(password)) strength += 1;
        
        const strengthLevels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
        const strengthColors = ['#ff4444', '#ff8800', '#ffcc00', '#88cc00', '#44cc44', '#00cc88'];
        
        return {
            score: strength,
            level: strengthLevels[Math.min(strength, 5)],
            color: strengthColors[Math.min(strength, 5)]
        };
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
    
    // Real-time validation
    usernameInput.addEventListener('input', function() {
        if (this.value && !validateUsername(this.value)) {
            showFieldError('username', 'Username must be 3-50 characters with letters, numbers, underscores, and hyphens only');
        } else {
            clearFieldError('username');
        }
    });
    
    emailInput.addEventListener('blur', function() {
        if (this.value && !validateEmail(this.value)) {
            showFieldError('email', 'Please enter a valid email address');
        } else {
            clearFieldError('email');
        }
    });
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password) {
            const strength = calculatePasswordStrength(password);
            strengthFill.style.width = (strength.score / 6 * 100) + '%';
            strengthFill.style.backgroundColor = strength.color;
            strengthText.textContent = strength.level;
            
            if (!validatePassword(password)) {
                showFieldError('password', 'Password must be at least 8 characters with uppercase, lowercase, and numbers');
            } else {
                clearFieldError('password');
            }
        } else {
            strengthFill.style.width = '0%';
            strengthText.textContent = 'Password strength';
        }
        
        // Check confirm password match
        if (confirmPasswordInput.value) {
            if (password !== confirmPasswordInput.value) {
                showFieldError('confirm-password', 'Passwords do not match');
            } else {
                clearFieldError('confirm-password');
            }
        }
    });
    
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value && passwordInput.value !== this.value) {
            showFieldError('confirm-password', 'Passwords do not match');
        } else {
            clearFieldError('confirm-password');
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate all fields
        if (!usernameInput.value) {
            showFieldError('username', 'Username is required');
            isValid = false;
        } else if (!validateUsername(usernameInput.value)) {
            showFieldError('username', 'Invalid username format');
            isValid = false;
        } else {
            clearFieldError('username');
        }
        
        if (!emailInput.value) {
            showFieldError('email', 'Email is required');
            isValid = false;
        } else if (!validateEmail(emailInput.value)) {
            showFieldError('email', 'Invalid email format');
            isValid = false;
        } else {
            clearFieldError('email');
        }
        
        if (!passwordInput.value) {
            showFieldError('password', 'Password is required');
            isValid = false;
        } else if (!validatePassword(passwordInput.value)) {
            showFieldError('password', 'Password does not meet requirements');
            isValid = false;
        } else {
            clearFieldError('password');
        }
        
        if (passwordInput.value !== confirmPasswordInput.value) {
            showFieldError('confirm-password', 'Passwords do not match');
            isValid = false;
        } else {
            clearFieldError('confirm-password');
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