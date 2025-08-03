<?php
include_once '../includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirectTo(SITE_URL . '/auth/login.php');
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_data_sql = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_data_sql);
$user = $user_result->fetch_assoc();

// Get user progress
$progress_sql = "SELECT p.*, l.name as language_name, l.code as language_code, 
                 c.name as category_name, c.slug as category_slug
                 FROM progress p
                 JOIN languages l ON p.language_id = l.id
                 JOIN categories c ON p.category_id = c.id
                 WHERE p.user_id = '$user_id'
                 ORDER BY p.updated_at DESC";
$progress_result = $conn->query($progress_sql);

// Get user achievements
$achievement_sql = "SELECT a.*, ua.earned_at
                    FROM user_achievements ua
                    JOIN achievements a ON ua.achievement_id = a.id
                    WHERE ua.user_id = '$user_id'
                    ORDER BY ua.earned_at DESC";
$achievement_result = $conn->query($achievement_sql);

// Get available languages
$languages_sql = "SELECT * FROM languages ORDER BY name";
$languages_result = $conn->query($languages_sql);

include_once '../includes/header.php';
?>

<main class="dashboard-page" ng-app="perfectoApp" ng-controller="DashboardController">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <img src="https://placehold.co/150x150/58CC02/FFFFFF?text=<?php echo substr($_SESSION['username'], 0, 1); ?>" alt="Profile">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                        <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    </div>
                </div>
                
                <div class="language-selector">
                    <h3>My Languages</h3>
                    <div class="language-options">
                        <?php while ($language = $languages_result->fetch_assoc()): ?>
                            <a href="<?php echo SITE_URL; ?>/learn/index.php?lang=<?php echo $language['code']; ?>" class="language-option">
                                <img src="<?php echo $language['flag_url']; ?>" alt="<?php echo $language['name']; ?>">
                                <span><?php echo $language['name']; ?></span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-main">
                <div class="dashboard-section">
                    <h2>My Progress</h2>
                    <?php if ($progress_result->num_rows > 0): ?>
                        <div class="progress-cards">
                            <?php while ($progress = $progress_result->fetch_assoc()): ?>
                                <div class="progress-card">
                                    <div class="progress-header">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/flags/<?php echo $progress['language_code']; ?>.svg" alt="<?php echo $progress['language_name']; ?>">
                                        <h3><?php echo $progress['language_name']; ?> - <?php echo $progress['category_name']; ?></h3>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress['completion_percentage']; ?>%"></div>
                                    </div>
                                    <div class="progress-stats">
                                        <div class="stat">
                                            <span class="stat-label">Completed</span>
                                            <span class="stat-value"><?php echo $progress['lessons_completed']; ?> / <?php echo $progress['total_lessons']; ?> lessons</span>
                                        </div>
                                        <div class="stat">
                                            <span class="stat-label">Quiz Score</span>
                                            <span class="stat-value"><?php echo $progress['quiz_score']; ?>%</span>
                                        </div>
                                    </div>
                                    <a href="<?php echo SITE_URL; ?>/learn/index.php?lang=<?php echo $progress['language_code']; ?>&category=<?php echo $progress['category_slug']; ?>" class="btn btn-primary">Continue</a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-progress">
                            <p>You haven't started learning any language yet.</p>
                            <a href="<?php echo SITE_URL; ?>/learn/index.php" class="btn btn-primary">Start Learning</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-section">
                    <h2>My Achievements</h2>
                    <?php if ($achievement_result->num_rows > 0): ?>
                        <div class="achievement-grid">
                            <?php while ($achievement = $achievement_result->fetch_assoc()): ?>
                                <div class="achievement-card">
                                    <div class="achievement-icon">
                                        <img src="<?php echo $achievement['icon_url']; ?>" alt="<?php echo $achievement['name']; ?>">
                                    </div>
                                    <div class="achievement-details">
                                        <h3><?php echo $achievement['name']; ?></h3>
                                        <p><?php echo $achievement['description']; ?></p>
                                        <span class="achievement-date">Earned: <?php echo date('M d, Y', strtotime($achievement['earned_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-achievements">
                            <p>Keep learning to earn achievements!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once '../includes/footer.php'; ?>