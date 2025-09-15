<?php
include_once '../includes/config.php';

// Get selected language and category
$lang_code = isset($_GET['lang']) ? sanitizeInput($_GET['lang']) : 'es'; // Default to Spanish
$category_slug = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// Get language information
$language_sql = "SELECT * FROM languages WHERE code = ?";
$stmt = $conn->prepare($language_sql);
$stmt->bind_param("s", $lang_code);
$stmt->execute();
$language_result = $stmt->get_result();

if ($language_result->num_rows == 0) {
    // Language not found, redirect to home
    redirectTo(SITE_URL);
}

$language = $language_result->fetch_assoc();
$language_id = $language['id'];

// Get categories for this language
$categories_sql = "SELECT * FROM categories WHERE language_id = ? ORDER BY display_order";
$stmt = $conn->prepare($categories_sql);
$stmt->bind_param("i", $language_id);
$stmt->execute();
$categories_result = $stmt->get_result();

$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// If no category is selected and there are categories, select the first one
if (empty($category_slug) && !empty($categories)) {
    $category_slug = $categories[0]['slug'];
    redirectTo(SITE_URL . "/learn/index.php?lang=$lang_code&category=$category_slug");
}

// Get selected category information
$category_sql = "SELECT * FROM categories WHERE language_id = ? AND slug = ?";
$stmt = $conn->prepare($category_sql);
$stmt->bind_param("is", $language_id, $category_slug);
$stmt->execute();
$category_result = $stmt->get_result();

if ($category_result->num_rows == 0 && !empty($category_slug)) {
    // Category not found, redirect to the first category
    if (!empty($categories)) {
        redirectTo(SITE_URL . "/learn/index.php?lang=$lang_code&category=" . $categories[0]['slug']);
    } else {
        redirectTo(SITE_URL);
    }
}

$category = $category_result->fetch_assoc();
$category_id = $category['id'];

// Get flashcards for this category
$flashcards_sql = "SELECT * FROM flashcards WHERE category_id = ? ORDER BY display_order";
$stmt = $conn->prepare($flashcards_sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$flashcards_result = $stmt->get_result();

$flashcards = [];
while ($row = $flashcards_result->fetch_assoc()) {
    $flashcards[] = $row;
}

// Initialize user progress variables
$user_progress = null;
$completed_lessons = [];
$quiz_unlocked = false;
$user_xp = 0;
$user_streak = 0;

// Handle lesson completion if logged in
if (isLoggedIn() && isset($_POST['complete_lesson'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage("Invalid request. Please try again.", "error");
    } else {
        $user_id = $_SESSION['user_id'];
        $flashcard_id = (int)$_POST['flashcard_id'];
        
        // Validate flashcard belongs to current category
        $valid_flashcard = false;
        foreach ($flashcards as $flashcard) {
            if ($flashcard['id'] == $flashcard_id) {
                $valid_flashcard = true;
                $xp_to_add = $flashcard['xp_value'];
                break;
            }
        }
        
        if ($valid_flashcard) {
            try {
                $conn->begin_transaction();
                
                // Check if progress record exists
                $check_sql = "SELECT * FROM progress WHERE user_id = ? AND language_id = ? AND category_id = ?";
                $stmt = $conn->prepare($check_sql);
                $stmt->bind_param("iii", $user_id, $language_id, $category_id);
                $stmt->execute();
                $check_result = $stmt->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Update existing record
                    $progress = $check_result->fetch_assoc();
                    $completed_lessons = json_decode($progress['completed_lessons'], true) ?: [];
                    
                    if (!in_array($flashcard_id, $completed_lessons)) {
                        $completed_lessons[] = $flashcard_id;
                        
                        $completed_json = json_encode($completed_lessons);
                        $completion_percentage = (count($completed_lessons) / count($flashcards)) * 100;
                        $quiz_unlocked = $completion_percentage >= 100;
                        
                        $update_sql = "UPDATE progress SET 
                                      completed_lessons = ?, 
                                      lessons_completed = ?, 
                                      total_lessons = ?, 
                                      completion_percentage = ?, 
                                      quiz_unlocked = ?, 
                                      updated_at = NOW() 
                                      WHERE user_id = ? AND language_id = ? AND category_id = ?";
                        $stmt = $conn->prepare($update_sql);
                        $stmt->bind_param("siidiiii", $completed_json, count($completed_lessons), count($flashcards), $completion_percentage, $quiz_unlocked, $user_id, $language_id, $category_id);
                        $stmt->execute();
                        
                        // Update user XP and streak
                        $update_user_sql = "UPDATE users SET 
                                           total_xp = total_xp + ?, 
                                           last_activity_date = CURDATE() 
                                           WHERE id = ?";
                        $stmt = $conn->prepare($update_user_sql);
                        $stmt->bind_param("ii", $xp_to_add, $user_id);
                        $stmt->execute();
                        
                        // Update user streak
                        $streak_sql = "INSERT INTO user_streaks (user_id, streak_date, xp_earned, lessons_completed) 
                                      VALUES (?, CURDATE(), ?, 1) 
                                      ON DUPLICATE KEY UPDATE 
                                      xp_earned = xp_earned + ?, 
                                      lessons_completed = lessons_completed + 1";
                        $stmt = $conn->prepare($streak_sql);
                        $stmt->bind_param("iii", $user_id, $xp_to_add, $xp_to_add);
                        $stmt->execute();
                        
                        $conn->commit();
                        
                        // Log activity
                        logActivity($user_id, 'lesson_completed', "Completed flashcard: {$flashcard['native_text']}");
                        
                        if ($quiz_unlocked) {
                            setFlashMessage("Great job! You've completed all lessons in this category. The quiz is now unlocked!", "success");
                        } else {
                            setFlashMessage("Well done! You earned {$xp_to_add} XP. Keep going!", "success");
                        }
                    }
                } else {
                    // Create new progress record
                    $completed_lessons = [$flashcard_id];
                    $completed_json = json_encode($completed_lessons);
                    $completion_percentage = (1 / count($flashcards)) * 100;
                    $quiz_unlocked = $completion_percentage >= 100;
                    
                    $insert_sql = "INSERT INTO progress (user_id, language_id, category_id, completed_lessons, lessons_completed, total_lessons, completion_percentage, quiz_unlocked, created_at) 
                                  VALUES (?, ?, ?, ?, 1, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("iiisidi", $user_id, $language_id, $category_id, $completed_json, count($flashcards), $completion_percentage, $quiz_unlocked);
                    $stmt->execute();
                    
                    // Update user XP
                    $update_user_sql = "UPDATE users SET 
                                       total_xp = total_xp + ?, 
                                       last_activity_date = CURDATE() 
                                       WHERE id = ?";
                    $stmt = $conn->prepare($update_user_sql);
                    $stmt->bind_param("ii", $xp_to_add, $user_id);
                    $stmt->execute();
                    
                    // Create streak record
                    $streak_sql = "INSERT INTO user_streaks (user_id, streak_date, xp_earned, lessons_completed) 
                                  VALUES (?, CURDATE(), ?, 1) 
                                  ON DUPLICATE KEY UPDATE 
                                  xp_earned = xp_earned + ?, 
                                  lessons_completed = lessons_completed + 1";
                    $stmt = $conn->prepare($streak_sql);
                    $stmt->bind_param("iii", $user_id, $xp_to_add, $xp_to_add);
                    $stmt->execute();
                    
                    $conn->commit();
                    
                    // Log activity
                    logActivity($user_id, 'lesson_completed', "Started learning and completed first flashcard: {$flashcard['native_text']}");
                    
                    setFlashMessage("Great start! You earned {$xp_to_add} XP. Keep learning!", "success");
                }
                
            } catch (Exception $e) {
                $conn->rollback();
                setFlashMessage("An error occurred while saving your progress. Please try again.", "error");
                logError("Progress update failed", ['user_id' => $user_id, 'flashcard_id' => $flashcard_id, 'error' => $e->getMessage()]);
            }
        } else {
            setFlashMessage("Invalid flashcard selection.", "error");
        }
    }
}

// Get user progress if logged in
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    // Get progress for this category
    $progress_sql = "SELECT * FROM progress WHERE user_id = ? AND language_id = ? AND category_id = ?";
    $stmt = $conn->prepare($progress_sql);
    $stmt->bind_param("iii", $user_id, $language_id, $category_id);
    $stmt->execute();
    $progress_result = $stmt->get_result();
    
    if ($progress_result->num_rows > 0) {
        $user_progress = $progress_result->fetch_assoc();
        $completed_lessons = json_decode($user_progress['completed_lessons'], true) ?: [];
        $quiz_unlocked = (bool)$user_progress['quiz_unlocked'];
    }
    
    // Get user stats
    $user_stats_sql = "SELECT total_xp, current_streak FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_stats_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_stats_result = $stmt->get_result();
    
    if ($user_stats_result->num_rows > 0) {
        $user_stats = $user_stats_result->fetch_assoc();
        $user_xp = $user_stats['total_xp'];
        $user_streak = $user_stats['current_streak'];
    }
}

include_once '../includes/header.php';
?>

<main class="learn-page" ng-app="perfectoApp" ng-controller="LearnController">
    <div class="container">
        <!-- Learning Header -->
        <div class="learn-header">
            <div class="language-info">
                <img src="<?php echo htmlspecialchars($language['flag_url']); ?>" alt="<?php echo htmlspecialchars($language['name']); ?> flag" class="language-flag">
                <div class="language-details">
                    <h1>Learning <?php echo htmlspecialchars($language['name']); ?></h1>
                    <p class="category-name"><?php echo htmlspecialchars($category['name']); ?></p>
                    <?php if (!empty($category['description'])): ?>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isLoggedIn()): ?>
                <div class="user-stats">
                    <div class="stat-item">
                        <div class="stat-icon xp-icon"></div>
                        <div class="stat-content">
                            <span class="stat-value"><?php echo number_format($user_xp); ?></span>
                            <span class="stat-label">Total XP</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon streak-icon">🔥</div>
                        <div class="stat-content">
                            <span class="stat-value"><?php echo $user_streak; ?></span>
                            <span class="stat-label">Day Streak</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Progress Bar -->
        <?php if (isLoggedIn() && $user_progress): ?>
            <div class="progress-section">
                <div class="progress-header">
                    <span class="progress-text">
                        <?php echo $user_progress['lessons_completed']; ?> / <?php echo $user_progress['total_lessons']; ?> lessons completed
                    </span>
                    <span class="progress-percentage"><?php echo round($user_progress['completion_percentage']); ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $user_progress['completion_percentage']; ?>%"></div>
                </div>
                <?php if ($quiz_unlocked): ?>
                    <div class="quiz-unlock-notice">
                        <span class="unlock-icon">🎉</span>
                        <span>Quiz unlocked! <a href="<?php echo SITE_URL; ?>/quiz/index.php?lang=<?php echo $lang_code; ?>&category=<?php echo $category_slug; ?>" class="quiz-link">Take the quiz</a></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Category Navigation -->
        <?php if (count($categories) > 1): ?>
            <div class="category-nav">
                <h3>Categories</h3>
                <div class="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <a href="?lang=<?php echo $lang_code; ?>&category=<?php echo $cat['slug']; ?>" 
                           class="category-item <?php echo $cat['slug'] === $category_slug ? 'active' : ''; ?>">
                            <span class="category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                            <?php if (isLoggedIn()): ?>
                                <?php
                                // Check completion for this category
                                $cat_progress_sql = "SELECT completion_percentage FROM progress WHERE user_id = ? AND language_id = ? AND category_id = ?";
                                $stmt = $conn->prepare($cat_progress_sql);
                                $stmt->bind_param("iii", $_SESSION['user_id'], $language_id, $cat['id']);
                                $stmt->execute();
                                $cat_progress_result = $stmt->get_result();
                                $cat_completion = 0;
                                if ($cat_progress_result->num_rows > 0) {
                                    $cat_completion = $cat_progress_result->fetch_assoc()['completion_percentage'];
                                }
                                ?>
                                <span class="category-progress"><?php echo round($cat_completion); ?>%</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Flashcards Section -->
        <div class="flashcards-section">
            <?php if (!empty($flashcards)): ?>
                <div class="flashcards-container" ng-init="initFlashcards(<?php echo htmlspecialchars(json_encode($flashcards)); ?>)">
                    <div class="flashcard-navigation">
                        <button class="nav-btn prev-btn" ng-click="prevCard()" ng-disabled="currentIndex === 0">
                            <i class="nav-icon prev-icon"></i>
                            Previous
                        </button>
                        <span class="card-counter">{{currentIndex + 1}} / {{flashcards.length}}</span>
                        <button class="nav-btn next-btn" ng-click="nextCard()" ng-disabled="currentIndex === flashcards.length - 1">
                            Next
                            <i class="nav-icon next-icon"></i>
                        </button>
                    </div>
                    
                    <div class="flashcard-display">
                        <div class="flashcard" ng-repeat="flashcard in flashcards" ng-show="$index === currentIndex">
                            <div class="flashcard-inner" ng-class="{'flipped': flashcard.flipped}">
                                <!-- Front Side -->
                                <div class="flashcard-front">
                                    <div class="flashcard-image">
                                        <img ng-src="{{flashcard.image_url}}" 
                                             alt="{{flashcard.native_text}}"
                                             onerror="this.src='https://placehold.co/300x200/E5E5E5/999999?text=No+Image'">
                                    </div>
                                    <div class="flashcard-content">
                                        <h2 class="native-text">{{flashcard.native_text}}</h2>
                                        <div class="difficulty-badge {{flashcard.difficulty}}">
                                            {{flashcard.difficulty}}
                                        </div>
                                        <div class="xp-badge">+{{flashcard.xp_value}} XP</div>
                                    </div>
                                    <button class="flip-btn" ng-click="flipCard(flashcard)" ng-if="!flashcard.flipped">
                                        Show Translation
                                    </button>
                                </div>
                                
                                <!-- Back Side -->
                                <div class="flashcard-back">
                                    <div class="flashcard-content">
                                        <h2 class="foreign-text">{{flashcard.foreign_text}}</h2>
                                        <p class="pronunciation" ng-if="flashcard.pronunciation">
                                            <strong>Pronunciation:</strong> {{flashcard.pronunciation}}
                                        </p>
                                        
                                        <div class="flashcard-actions">
                                            <button class="flip-btn secondary" ng-click="flipCard(flashcard)">
                                                Show Original
                                            </button>
                                            
                                            <?php if (isLoggedIn()): ?>
                                                <form method="post" action="" ng-if="!isCompleted(flashcard.id)" class="completion-form">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                    <input type="hidden" name="flashcard_id" value="{{flashcard.id}}">
                                                    <button type="submit" name="complete_lesson" class="btn btn-success complete-btn">
                                                        <i class="btn-icon check-icon"></i>
                                                        Mark as Learned
                                                    </button>
                                                </form>
                                                <div class="completed-badge" ng-if="isCompleted(flashcard.id)">
                                                    <i class="completed-icon"></i>
                                                    <span>Learned!</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="login-prompt">
                                                    <p>
                                                        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-primary">Login</a> 
                                                        to track your progress
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Flashcard Grid View Toggle -->
                    <div class="view-controls">
                        <button class="view-toggle" ng-click="toggleView()" ng-class="{'active': gridView}">
                            <i class="view-icon grid-icon"></i>
                            Grid View
                        </button>
                    </div>
                    
                    <!-- Grid View -->
                    <div class="flashcards-grid" ng-show="gridView">
                        <div class="flashcard-mini" ng-repeat="flashcard in flashcards" ng-click="selectCard($index)">
                            <div class="mini-image">
                                <img ng-src="{{flashcard.image_url}}" alt="{{flashcard.native_text}}">
                            </div>
                            <div class="mini-content">
                                <h4>{{flashcard.native_text}}</h4>
                                <p>{{flashcard.foreign_text}}</p>
                                <?php if (isLoggedIn()): ?>
                                    <div class="mini-status" ng-class="{'completed': isCompleted(flashcard.id)}">
                                        <i class="status-icon" ng-class="isCompleted(flashcard.id) ? 'completed-icon' : 'pending-icon'"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon flashcards-icon"></div>
                    <h3>No flashcards available</h3>
                    <p>This category doesn't have any flashcards yet. Check back later or try another category.</p>
                    <?php if (count($categories) > 1): ?>
                        <a href="?lang=<?php echo $lang_code; ?>" class="btn btn-primary">Browse Other Categories</a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">Choose Another Language</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quiz Section -->
        <?php if (isLoggedIn() && $quiz_unlocked): ?>
            <div class="quiz-section">
                <div class="quiz-card">
                    <div class="quiz-content">
                        <h3>🎯 Quiz Time!</h3>
                        <p>You've completed all lessons in this category. Test your knowledge with a quiz!</p>
                        <a href="<?php echo SITE_URL; ?>/quiz/index.php?lang=<?php echo $lang_code; ?>&category=<?php echo $category_slug; ?>" 
                           class="btn btn-primary quiz-btn">
                            Take Quiz
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Update the Angular controller initialization
perfectoApp.controller('LearnController', function($scope) {
    $scope.currentIndex = 0;
    $scope.flashcards = [];
    $scope.gridView = false;
    $scope.completedLessons = <?php echo json_encode($completed_lessons); ?>;
    
    // Initialize flashcards from PHP data
    $scope.initFlashcards = function(flashcardsData) {
        $scope.flashcards = flashcardsData;
        
        // Initialize flashcard properties
        angular.forEach($scope.flashcards, function(flashcard) {
            flashcard.flipped = false;
        });
    };
    
    // Check if flashcard is completed
    $scope.isCompleted = function(flashcardId) {
        return $scope.completedLessons.indexOf(flashcardId) !== -1;
    };
    
    // Flip card function
    $scope.flipCard = function(flashcard) {
        flashcard.flipped = !flashcard.flipped;
    };
    
    // Navigation functions
    $scope.nextCard = function() {
        if ($scope.currentIndex < $scope.flashcards.length - 1) {
            $scope.currentIndex++;
            // Reset flip state
            $scope.flashcards[$scope.currentIndex].flipped = false;
        }
    };
    
    $scope.prevCard = function() {
        if ($scope.currentIndex > 0) {
            $scope.currentIndex--;
            // Reset flip state
            $scope.flashcards[$scope.currentIndex].flipped = false;
        }
    };
    
    // Select specific card
    $scope.selectCard = function(index) {
        $scope.currentIndex = index;
        $scope.gridView = false;
        $scope.flashcards[index].flipped = false;
    };
    
    // Toggle view
    $scope.toggleView = function() {
        $scope.gridView = !$scope.gridView;
    };
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowRight' || e.key === ' ') {
            e.preventDefault();
            if (!$scope.flashcards[$scope.currentIndex].flipped) {
                $scope.flipCard($scope.flashcards[$scope.currentIndex]);
            } else {
                $scope.nextCard();
            }
            $scope.$apply();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            $scope.prevCard();
            $scope.$apply();
        } else if (e.key === 'f' || e.key === 'F') {
            e.preventDefault();
            $scope.flipCard($scope.flashcards[$scope.currentIndex]);
            $scope.$apply();
        } else if (e.key === 'g' || e.key === 'G') {
            e.preventDefault();
            $scope.toggleView();
            $scope.$apply();
        }
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>

// Get flashcards for this category
$flashcards_sql = "SELECT * FROM flashcards WHERE category_id = $category_id ORDER BY display_order";
$flashcards_result = $conn->query($flashcards_sql);

$flashcards = [];
while ($row = $flashcards_result->fetch_assoc()) {
    $flashcards[] = $row;
}

// Handle lesson completion if logged in
if (isLoggedIn() && isset($_POST['complete_lesson'])) {
    $user_id = $_SESSION['user_id'];
    $flashcard_id = (int)$_POST['flashcard_id'];
    
    // Check if progress record exists
    $check_sql = "SELECT * FROM progress WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        // Update existing record
        $progress = $check_result->fetch_assoc();
        $completed_lessons = json_decode($progress['completed_lessons'], true) ?: [];
        
        if (!in_array($flashcard_id, $completed_lessons)) {
            $completed_lessons[] = $flashcard_id;
        }
        
        $completed_json = json_encode($completed_lessons);
        $completion_percentage = count($completed_lessons) / count($flashcards) * 100;
        
        $update_sql = "UPDATE progress SET 
                      completed_lessons = '$completed_json', 
                      lessons_completed = " . count($completed_lessons) . ", 
                      total_lessons = " . count($flashcards) . ", 
                      completion_percentage = $completion_percentage, 
                      updated_at = NOW() 
                      WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
        
        $conn->query($update_sql);
    } else {
        // Create new progress record
        $completed_lessons = [$flashcard_id];
        $completed_json = json_encode($completed_lessons);
        $completion_percentage = count($completed_lessons) / count($flashcards) * 100;
        
        $insert_sql = "INSERT INTO progress 
                      (user_id, language_id, category_id, completed_lessons, lessons_completed, total_lessons, completion_percentage, quiz_score, created_at, updated_at) 
                      VALUES 
                      ($user_id, $language_id, $category_id, '$completed_json', 1, " . count($flashcards) . ", $completion_percentage, 0, NOW(), NOW())";
        
        $conn->query($insert_sql);
    }
    
    // Check if all lessons are completed, if so, unlock the quiz
    $check_sql = "SELECT * FROM progress WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
    $check_result = $conn->query($check_sql);
    $progress = $check_result->fetch_assoc();
    
    if ($progress['lessons_completed'] >= $progress['total_lessons']) {
        // Unlock quiz
        $unlock_sql = "UPDATE progress SET quiz_unlocked = 1 WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
        $conn->query($unlock_sql);
        
        setFlashMessage("Congratulations! You've completed all lessons in this category. The quiz is now unlocked!");
        redirectTo(SITE_URL . "/quiz/index.php?lang=$lang_code&category=$category_slug");
    }
}

// Get user progress if logged in
$user_progress = null;
$completed_lessons = [];
$quiz_unlocked = false;

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $progress_sql = "SELECT * FROM progress WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
    $progress_result = $conn->query($progress_sql);
    
    if ($progress_result->num_rows > 0) {
        $user_progress = $progress_result->fetch_assoc();
        $completed_lessons = json_decode($user_progress['completed_lessons'], true) ?: [];
        $quiz_unlocked = (bool)$user_progress['quiz_unlocked'];
    }
}

include_once '../includes/header.php';
?>

<main class="learn-page" ng-app="perfectoApp" ng-controller="LearnController">
    <div class="container">
        <div class="learn-header">
            <div class="language-info">
                <img src="<?php echo $language['flag_url']; ?>" alt="<?php echo $language['name']; ?> flag">
                <h1>Learning <?php echo $language['name']; ?></h1>
            </div>
            
            <?php if (isLoggedIn() && $user_progress): ?>
                <div class="progress-info">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $user_progress['completion_percentage']; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span><?php echo $user_progress['lessons_completed']; ?> / <?php echo $user_progress['total_lessons']; ?> lessons completed</span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($quiz_unlocked): ?>
                <a href="<?php echo SITE_URL; ?>/quiz/index.php?lang=<?php echo $lang_code; ?>&category=<?php echo $category_slug; ?>" class="btn btn-success">Take Quiz</a>
            <?php endif; ?>
        </div>
        
        <div class="learn-content">
            <div class="categories-sidebar">
                <h2>Categories</h2>
                <ul class="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <li class="<?php echo $cat['slug'] === $category_slug ? 'active' : ''; ?>">
                            <a href="<?php echo SITE_URL; ?>/learn/index.php?lang=<?php echo $lang_code; ?>&category=<?php echo $cat['slug']; ?>">
                                <?php echo $cat['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="flashcards-container">
                <h2><?php echo $category['name']; ?></h2>
                <p class="category-description"><?php echo $category['description']; ?></p>
                
                <?php if (empty($flashcards)): ?>
                    <div class="no-flashcards">
                        <p>No flashcards available for this category yet.</p>
                    </div>
                <?php else: ?>
                    <div class="flashcards" ng-init="initFlashcards(<?php echo htmlspecialchars(json_encode($flashcards)); ?>)">
                        <div class="flashcard" ng-repeat="flashcard in flashcards" ng-class="{'completed': flashcard.completed, 'active': currentIndex == $index}">
                            <div class="flashcard-inner" ng-class="{'flipped': flashcard.flipped}">
                                <div class="flashcard-front">
                                    <div class="flashcard-image">
                                        <img ng-src="{{flashcard.image_url}}" alt="{{flashcard.native_text}}">
                                    </div>
                                    <div class="flashcard-content">
                                        <h3>{{flashcard.native_text}}</h3>
                                        <button class="btn btn-primary btn-flip" ng-click="flipCard(flashcard)">Show Translation</button>
                                    </div>
                                </div>
                                <div class="flashcard-back">
                                    <div class="flashcard-content">
                                        <h3>{{flashcard.foreign_text}}</h3>
                                        <p class="pronunciation" ng-if="flashcard.pronunciation">{{flashcard.pronunciation}}</p>
                                        
                                        <?php if (isLoggedIn()): ?>
                                            <form method="post" action="" ng-if="!flashcard.completed">
                                                <input type="hidden" name="flashcard_id" value="{{flashcard.id}}">
                                                <button type="submit" name="complete_lesson" class="btn btn-success">Mark as Learned</button>
                                            </form>
                                            <div class="completed-badge" ng-if="flashcard.completed">
                                                <span>Learned</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-secondary btn-flip" ng-click="flipCard(flashcard)">Show Word</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flashcard-navigation">
                            <button class="nav-btn prev-btn" ng-click="prevCard()" ng-disabled="currentIndex == 0">
                                <i class="nav-icon prev-icon"></i>
                                <span>Previous</span>
                            </button>
                            <div class="nav-indicator">
                                <span>{{currentIndex + 1}} / {{flashcards.length}}</span>
                            </div>
                            <button class="nav-btn next-btn" ng-click="nextCard()" ng-disabled="currentIndex == flashcards.length - 1">
                                <span>Next</span>
                                <i class="nav-icon next-icon"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
var perfectoApp = angular.module('perfectoApp', []);

perfectoApp.controller('LearnController', function($scope) {
    $scope.currentIndex = 0;
    $scope.flashcards = [];
    
    $scope.initFlashcards = function(flashcardsData) {
        $scope.flashcards = flashcardsData;
        
        // Mark completed flashcards
        var completedLessons = <?php echo json_encode($completed_lessons); ?>;
        
        angular.forEach($scope.flashcards, function(flashcard) {
            flashcard.flipped = false;
            flashcard.completed = completedLessons.includes(parseInt(flashcard.id));
        });
    };
    
    $scope.flipCard = function(flashcard) {
        flashcard.flipped = !flashcard.flipped;
    };
    
    $scope.nextCard = function() {
        if ($scope.currentIndex < $scope.flashcards.length - 1) {
            $scope.currentIndex++;
        }
    };
    
    $scope.prevCard = function() {
        if ($scope.currentIndex > 0) {
            $scope.currentIndex--;
        }
    };
});
</script>

<?php include_once '../includes/footer.php'; ?>