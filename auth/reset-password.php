<?php
include_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/dashboard/user.php');
}

$token = isset($_GET['token']) ? sanitizeInput($_GET['token']) : '';
$errors = [];
$user_data = null;

// Validate token
if (empty($token)) {
    $errors[] = "Invalid reset token.";
} else {
    try {
        $sql = "SELECT id, username, email, reset_expires FROM users WHERE reset_token = ? AND reset_expires > NOW()";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
            } else {
                $errors[] = "Invalid or expired reset token.";
            }
            
            $stmt->close();
        } else {
            $errors[] = "Database error. Please try again.";
        }
    } catch (Exception $e) {
        $errors[] = "An error occurred. Please try again.";
        logError("Reset token validation exception: " . $e->getMessage());
    }
}

// Process password reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_data) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate new password
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (!validatePassword($new_password)) {
            $errors[] = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters and contain uppercase, lowercase, and numeric characters";
        }
        
        // Validate password confirmation
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors)) {
            try {
                // Hash the new password
                $hashed_password = hashPassword($new_password);
                
                // Update password and clear reset token
                $update_sql = "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $hashed_password, $user_data['id']);
                    
                    if ($update_stmt->execute()) {
                        logActivity($user_data['id'], 'password_reset', 'Password reset successfully');
                        
                        setFlashMessage("Password has been reset successfully. Please log in with your new password.", "success");
                        redirectTo(SITE_URL . '/auth/login.php');
                    } else {
                        $errors[] = "Failed to reset password. Please try again.";
                    }
                    
                    $update_stmt->close();
                } else {
                    $errors[] = "Database error. Please try again.";
                }
            } catch (Exception $e) {
                $errors[] = "An error occurred while resetting password. Please try again.";
                logError("Password reset exception: " . $e->getMessage(), ['user_id' => $user_data['id']]);
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
            
            <?php if ($user_data && empty($errors)): ?>
                <p class="reset-info">
                    Setting new password for: <strong><?php echo htmlspecialchars($user_data['email']); ?></strong>
                </p>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . urlencode($token); ?>" class="auth-form" id="reset-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="new_password">New Password <span class="required">*</span></label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
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
                        <label for="confirm_password">Confirm New Password <span class="required">*</span></label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required 
                               autocomplete="new-password"
                               aria-describedby="confirm-password-error">
                        <div id="confirm-password-error" class="field-error"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="reset-btn">
                        <span class="btn-text">Reset Password</span>
                        <span class="btn-loading" style="display: none;">Resetting...</span>
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="auth-links">
                <p><a href="<?php echo SITE_URL; ?>/auth/login.php">Back to Login</a></p>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reset-form');
    if (!form) return;
    
    const submitBtn = document.getElementById('reset-btn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    const passwordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    // Password strength elements
    const strengthFill = document.getElementById('strength-fill');
    const strengthText = document.getElementById('strength-text');
    
    function validatePassword(password) {
        return password.length >= <?php echo PASSWORD_MIN_LENGTH; ?> &&
               /[a-z]/.test(password) &&
               /[A-Z]/.test(password) &&
               /\d/.test(password);
    }
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= <?php echo PASSWORD_MIN_LENGTH; ?>) strength += 1;
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
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password) {
            const strength = calculatePasswordStrength(password);
            strengthFill.style.width = (strength.score / 6 * 100) + '%';
            strengthFill.style.backgroundColor = strength.color;
            strengthText.textContent = strength.level;
            
            if (!validatePassword(password)) {
                showFieldError('password', 'Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters with uppercase, lowercase, and numbers');
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
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        if (!passwordInput.value) {
            showFieldError('password', 'New password is required');
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