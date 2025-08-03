<?php
include_once '../includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirectTo(SITE_URL . '/auth/login.php');
}

// Get selected language and category
$lang_code = isset($_GET['lang']) ? sanitizeInput($_GET['lang']) : '';
$category_slug = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

if (empty($lang_code) || empty($category_slug)) {
    redirectTo(SITE_URL . '/learn/index.php');
}

// Get language information
$language_sql = "SELECT * FROM languages WHERE code = '$lang_code'";
$language_result = $conn->query($language_sql);

if ($language_result->num_rows == 0) {
    // Language not found, redirect to home
    redirectTo(SITE_URL);
}

$language = $language_result->fetch_assoc();
$language_id = $language['id'];

// Get category information
$category_sql = "SELECT * FROM categories WHERE language_id = $language_id AND slug = '$category_slug'";
$category_result = $conn->query($category_sql);

if ($category_result->num_rows == 0) {
    // Category not found, redirect to learn page
    redirectTo(SITE_URL . "/learn/index.php?lang=$lang_code");
}

$category = $category_result->fetch_assoc();
$category_id = $category['id'];

// Check if user has unlocked this quiz
$user_id = $_SESSION['user_id'];
$progress_sql = "SELECT * FROM progress WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
$progress_result = $conn->query($progress_sql);

if ($progress_result->num_rows == 0 || !$progress_result->fetch_assoc()['quiz_unlocked']) {
    // Quiz not unlocked, redirect to learn page
    setFlashMessage("You need to complete all lessons in this category first to unlock the quiz.");
    redirectTo(SITE_URL . "/learn/index.php?lang=$lang_code&category=$category_slug");
}

// Initialize variables
$is_quiz_complete = false;
$quiz_score = 0;
$total_questions = 0;
$correct_answers = 0;
$user_answers = [];

// Process quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    // Get quiz questions
    $questions_sql = "SELECT q.* FROM quiz_questions q 
                     JOIN flashcards f ON q.flashcard_id = f.id 
                     WHERE f.category_id = $category_id";
    $questions_result = $conn->query($questions_sql);
    
    $total_questions = $questions_result->num_rows;
    
    // Check answers
    while ($question = $questions_result->fetch_assoc()) {
        $question_id = $question['id'];
        
        if (isset($_POST['answer'][$question_id])) {
            $user_answer = $_POST['answer'][$question_id];
            $correct_answer = $question['correct_answer'];
            
            $user_answers[$question_id] = [
                'question' => $question['question'],
                'user_answer' => $user_answer,
                'correct_answer' => $correct_answer,
                'is_correct' => ($user_answer === $correct_answer)
            ];
            
            if ($user_answer === $correct_answer) {
                $correct_answers++;
            }
        }
    }
    
    // Calculate score
    $quiz_score = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;
    
    // Update progress
    $update_sql = "UPDATE progress SET quiz_score = $quiz_score, updated_at = NOW() WHERE user_id = $user_id AND language_id = $language_id AND category_id = $category_id";
    $conn->query($update_sql);
    
    // Award achievements if applicable
    if ($quiz_score >= 80) {
        // Check if the achievement exists
        $achievement_sql = "SELECT * FROM achievements WHERE code = 'quiz_master'";
        $achievement_result = $conn->query($achievement_sql);
        
        if ($achievement_result->num_rows > 0) {
            $achievement = $achievement_result->fetch_assoc();
            $achievement_id = $achievement['id'];
            
            // Check if user already has this achievement
            $user_achievement_sql = "SELECT * FROM user_achievements WHERE user_id = $user_id AND achievement_id = $achievement_id";
            $user_achievement_result = $conn->query($user_achievement_sql);
            
            if ($user_achievement_result->num_rows == 0) {
                // Award achievement
                $award_sql = "INSERT INTO user_achievements (user_id, achievement_id, earned_at) VALUES ($user_id, $achievement_id, NOW())";
                $conn->query($award_sql);
                
                setFlashMessage("Congratulations! You've earned the Quiz Master achievement!");
            }
        }
    }
    
    $is_quiz_complete = true;
}

// Get quiz questions if quiz is not complete
$questions = [];

if (!$is_quiz_complete) {
    $questions_sql = "SELECT q.*, f.native_text, f.foreign_text, f.image_url 
                     FROM quiz_questions q 
                     JOIN flashcards f ON q.flashcard_id = f.id 
                     WHERE f.category_id = $category_id 
                     ORDER BY RAND()";
    $questions_result = $conn->query($questions_sql);
    
    while ($row = $questions_result->fetch_assoc()) {
        // Prepare answer options (shuffle correct and wrong answers)
        $options = [
            $row['correct_answer'],
            $row['wrong_answer1'],
            $row['wrong_answer2'],
            $row['wrong_answer3']
        ];
        shuffle($options);
        
        $row['options'] = $options;
        $questions[] = $row;
    }
}

include_once '../includes/header.php';
?>

<main class="quiz-page" ng-app="perfectoApp" ng-controller="QuizController">
    <div class="container">
        <div class="quiz-header">
            <div class="language-info">
                <img src="<?php echo $language['flag_url']; ?>" alt="<?php echo $language['name']; ?> flag">
                <h1><?php echo $language['name']; ?> - <?php echo $category['name']; ?> Quiz</h1>
            </div>
            
            <?php if (!$is_quiz_complete): ?>
                <div class="quiz-instructions">
                    <p>Select the correct answer for each question. You can retake the quiz as many times as you want.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="quiz-content">
            <?php if ($is_quiz_complete): ?>
                <div class="quiz-results">
                    <div class="result-header">
                        <h2>Quiz Results</h2>
                        <div class="score-display">
                            <div class="score-circle" style="--score: <?php echo $quiz_score; ?>">
                                <div class="score-value"><?php echo $quiz_score; ?>%</div>
                            </div>
                            <div class="score-text">
                                <span><?php echo $correct_answers; ?> correct out of <?php echo $total_questions; ?> questions</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="result-feedback">
                        <?php if ($quiz_score >= 80): ?>
                            <div class="result-message success">
                                <h3>Excellent work!</h3>
                                <p>You've demonstrated a strong understanding of this category.</p>
                            </div>
                        <?php elseif ($quiz_score >= 60): ?>
                            <div class="result-message good">
                                <h3>Good job!</h3>
                                <p>You're making good progress, but there's still room for improvement.</p>
                            </div>
                        <?php else: ?>
                            <div class="result-message needs-work">
                                <h3>Keep practicing!</h3>
                                <p>Review the lessons in this category and try again.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="answer-review">
                        <h3>Answer Review</h3>
                        <div class="answer-list">
                            <?php foreach ($user_answers as $question_id => $answer): ?>
                                <div class="answer-item <?php echo $answer['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                    <div class="question-text">
                                        <span class="question-marker">Q:</span>
                                        <span><?php echo $answer['question']; ?></span>
                                    </div>
                                    <div class="answer-text">
                                        <div class="user-answer">
                                            <span class="answer-label">Your answer:</span>
                                            <span class="answer-value"><?php echo $answer['user_answer']; ?></span>
                                        </div>
                                        <?php if (!$answer['is_correct']): ?>
                                            <div class="correct-answer">
                                                <span class="answer-label">Correct answer:</span>
                                                <span class="answer-value"><?php echo $answer['correct_answer']; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="quiz-actions">
                        <a href="<?php echo SITE_URL; ?>/quiz/index.php?lang=<?php echo $lang_code; ?>&category=<?php echo $category_slug; ?>" class="btn btn-primary">Retake Quiz</a>
                        <a href="<?php echo SITE_URL; ?>/learn/index.php?lang=<?php echo $lang_code; ?>&category=<?php echo $category_slug; ?>" class="btn btn-secondary">Back to Lessons</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="post" action="" class="quiz-form">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="quiz-question">
                            <div class="question-header">
                                <span class="question-number"><?php echo $index + 1; ?></span>
                                <h3><?php echo $question['question']; ?></h3>
                            </div>
                            
                            <?php if (!empty($question['image_url'])): ?>
                                <div class="question-image">
                                    <img src="<?php echo $question['image_url']; ?>" alt="Question image">
                                </div>
                            <?php endif; ?>
                            
                            <div class="answer-options">
                                <?php foreach ($question['options'] as $option): ?>
                                    <div class="answer-option">
                                        <input type="radio" name="answer[<?php echo $question['id']; ?>]" id="option-<?php echo $question['id']; ?>-<?php echo md5($option); ?>" value="<?php echo $option; ?>" required>
                                        <label for="option-<?php echo $question['id']; ?>-<?php echo md5($option); ?>"><?php echo $option; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="quiz-submit">
                        <button type="submit" name="submit_quiz" class="btn btn-primary">Submit Answers</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
var perfectoApp = angular.module('perfectoApp', []);

perfectoApp.controller('QuizController', function($scope) {
    // Quiz controller logic if needed
});
</script>

<?php include_once '../includes/footer.php'; ?>