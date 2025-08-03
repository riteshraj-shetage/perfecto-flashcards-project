<?php
include_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/dashboard/user.php');
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password']; // No hashing as per requirements
    
    // Validate input
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Proceed if no errors
    if (empty($errors)) {
        // Query the database
        $sql = "SELECT id, username, email, password, role FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check password (no hashing as per requirements)
            if ($password === $user['password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to dashboard
                redirectTo(SITE_URL . '/dashboard/user.php');
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
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
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/auth/register.php">Sign up</a></p>
            </div>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>