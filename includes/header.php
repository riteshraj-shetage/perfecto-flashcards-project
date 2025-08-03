<?php
// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Learn Languages</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <!-- Angular JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <!-- Optional: jQuery (minimal use) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>">
                    <span class="brand">perfecto</span>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li<?php echo $current_page == 'index.php' ? ' class="active"' : ''; ?>>
                        <a href="<?php echo SITE_URL; ?>">Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li<?php echo strpos($current_page, 'learn') !== false ? ' class="active"' : ''; ?>>
                            <a href="<?php echo SITE_URL; ?>/learn/index.php">Learn</a>
                        </li>
                        <li<?php echo $current_page == 'user.php' ? ' class="active"' : ''; ?>>
                            <a href="<?php echo SITE_URL; ?>/dashboard/user.php">My Progress</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li<?php echo $current_page == 'admin.php' ? ' class="active"' : ''; ?>>
                                <a href="<?php echo SITE_URL; ?>/dashboard/admin.php">Admin</a>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li<?php echo $current_page == 'login.php' ? ' class="active"' : ''; ?>>
                            <a href="<?php echo SITE_URL; ?>/auth/login.php">Login</a>
                        </li>
                        <li<?php echo $current_page == 'register.php' ? ' class="active"' : ''; ?>>
                            <a href="<?php echo SITE_URL; ?>/auth/register.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="mobile-nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>
    
    <?php if ($flash_message = getFlashMessage()): ?>
    <div class="flash-message">
        <?php echo $flash_message; ?>
    </div>
    <?php endif; ?>