<footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <span class="brand">perfecto</span>
                    <p>Learn a new language in a fun and effective way</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/learn/index.php">Start Learning</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/dashboard/user.php">My Progress</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo SITE_URL; ?>/auth/login.php">Login</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/auth/register.php">Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-languages">
                    <h3>Languages</h3>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/learn/index.php?lang=es">Spanish</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/learn/index.php?lang=fr">French</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/learn/index.php?lang=de">German</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>/assets/js/app.js"></script>
</body>
</html>