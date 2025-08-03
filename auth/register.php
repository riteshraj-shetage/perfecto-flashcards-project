<?php
include_once '../includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL . '/dashboard/user.php');
}

$errors = [];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password']; // No hashing as per requirements
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already in use";
    }
    
    // Proceed if no errors
    if (empty($errors)) {
        // Set default role as 'user'
        $role = 'user';
        
        // Insert new user
        $sql = "INSERT INTO users (username, email, password, role, created_at) 
                VALUES ('$username', '$email', '$password', '$role', NOW())";
        
        if ($conn->query($sql) === TRUE) {
            // Set flash message
            setFlashMessage("Registration successful. Please log in.");
            
            // Redirect to login page
            redirectTo(SITE_URL . '/auth/login.php');
        } else {
            $errors[] = "Error: " . $conn->error;
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
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/auth/login.php">Login</a></p>
            </div>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>