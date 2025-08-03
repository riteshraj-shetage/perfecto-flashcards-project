<?php
include_once '../includes/config.php';

// Get selected language and category
$lang_code = isset($_GET['lang']) ? sanitizeInput($_GET['lang']) : 'es'; // Default to Spanish
$category_slug = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// Get language information
$language_sql = "SELECT * FROM languages WHERE code = '$lang_code'";
$language_result = $conn->query($language_sql);

if ($language_result->num_rows == 0) {
    // Language not found, redirect to home
    redirectTo(SITE_URL);
}

$language = $language_result->fetch_assoc();
$language_id = $language['id'];

// Get categories for this language
$categories_sql = "SELECT * FROM categories WHERE language_id = $language_id ORDER BY display_order";
$categories_result = $conn->query($categories_sql);

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
$category_sql = "SELECT * FROM categories WHERE language_id = $language_id AND slug = '$category_slug'";
$category_result = $conn->query($category_sql);

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