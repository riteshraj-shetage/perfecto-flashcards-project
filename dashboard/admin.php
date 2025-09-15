<?php
include_once '../includes/config.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage("Access denied. Admin privileges required.", "error");
    redirectTo(SITE_URL);
}

// Initialize variables
$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'dashboard';
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$success = false;

// Handle AJAX requests
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    // Validate CSRF token for AJAX requests
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $response['message'] = 'Invalid request token';
        echo json_encode($response);
        exit;
    }
    
    switch ($_POST['ajax_action']) {
        case 'toggle_user_status':
            $user_id = (int)$_POST['user_id'];
            if ($user_id > 0 && $user_id !== (int)$_SESSION['user_id']) {
                // Toggle user status logic would go here
                $response = ['success' => true, 'message' => 'User status updated'];
            }
            break;
            
        case 'get_stats':
            $stats = getAdminStats();
            $response = ['success' => true, 'data' => $stats];
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Handle regular form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_action'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message = "Invalid request. Please try again.";
    } else {
        try {
            $conn->begin_transaction();
            
            switch (true) {
                case isset($_POST['add_user']):
                    $result = addUser($_POST);
                    break;
                    
                case isset($_POST['add_language']):
                    $result = addLanguage($_POST);
                    break;
                    
                case isset($_POST['add_category']):
                    $result = addCategory($_POST);
                    break;
                    
                case isset($_POST['add_flashcard']):
                    $result = addFlashcard($_POST);
                    break;
                    
                case isset($_POST['add_quiz']):
                    $result = addQuizQuestion($_POST);
                    break;
                    
                case isset($_POST['bulk_import']):
                    $result = bulkImportContent($_POST, $_FILES);
                    break;
                    
                default:
                    $result = ['success' => false, 'message' => 'Unknown action'];
            }
            
            if ($result['success']) {
                $conn->commit();
                $message = $result['message'];
                $success = true;
                logActivity($_SESSION['user_id'], 'admin_action', $result['message']);
            } else {
                $conn->rollback();
                $message = $result['message'];
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "An error occurred: " . $e->getMessage();
            logError("Admin action failed", ['user_id' => $_SESSION['user_id'], 'error' => $e->getMessage()]);
        }
    }
}

// Handle delete actions
if ($action === 'delete' && $id > 0) {
    if (!isset($_GET['csrf_token']) || !validateCSRFToken($_GET['csrf_token'])) {
        $message = "Invalid request";
    } else {
        $result = handleDelete($tab, $id);
        $message = $result['message'];
        $success = $result['success'];
    }
}

// Helper functions
function getAdminStats() {
    global $conn;
    
    $stats = [];
    
    // User statistics
    $user_stats = $conn->query("SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users,
        COUNT(CASE WHEN last_activity_date >= CURDATE() - INTERVAL 7 DAY THEN 1 END) as active_users
        FROM users")->fetch_assoc();
    
    // Content statistics
    $content_stats = $conn->query("SELECT 
        (SELECT COUNT(*) FROM languages) as total_languages,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM flashcards) as total_flashcards,
        (SELECT COUNT(*) FROM quiz_questions) as total_quiz_questions")->fetch_assoc();
    
    // Activity statistics
    $activity_stats = $conn->query("SELECT 
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_logins,
        COUNT(CASE WHEN DATE(created_at) >= CURDATE() - INTERVAL 7 DAY THEN 1 END) as week_logins
        FROM activity_logs WHERE action = 'login'")->fetch_assoc();
    
    return array_merge($user_stats, $content_stats, $activity_stats);
}

function addUser($data) {
    global $conn;
    
    $username = sanitizeInput($data['username']);
    $email = sanitizeInput($data['email']);
    $password = $data['password'];
    $role = sanitizeInput($data['role']);
    
    // Validate input
    if (empty($username) || !validateUsername($username)) {
        return ['success' => false, 'message' => 'Invalid username'];
    }
    
    if (empty($email) || !validateEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email'];
    }
    
    if (empty($password) || !validatePassword($password)) {
        return ['success' => false, 'message' => 'Invalid password'];
    }
    
    if (!in_array($role, ['user', 'admin'])) {
        return ['success' => false, 'message' => 'Invalid role'];
    }
    
    // Check for existing user
    $check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'User with this email or username already exists'];
    }
    
    // Insert new user
    $hashed_password = hashPassword($password);
    $insert_sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'User added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add user'];
    }
}

function addLanguage($data) {
    global $conn;
    
    $name = sanitizeInput($data['name']);
    $code = sanitizeInput($data['code']);
    $flag_url = sanitizeInput($data['flag_url']);
    $display_order = (int)$data['display_order'];
    
    if (empty($name) || empty($code)) {
        return ['success' => false, 'message' => 'Name and code are required'];
    }
    
    if (strlen($code) > 10) {
        return ['success' => false, 'message' => 'Language code must be 10 characters or less'];
    }
    
    // Check for existing language
    $check_sql = "SELECT id FROM languages WHERE code = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Language with this code already exists'];
    }
    
    // Insert new language
    $insert_sql = "INSERT INTO languages (name, code, flag_url, display_order) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssi", $name, $code, $flag_url, $display_order);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Language added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add language'];
    }
}

function addCategory($data) {
    global $conn;
    
    $name = sanitizeInput($data['name']);
    $slug = sanitizeInput($data['slug']);
    $language_id = (int)$data['language_id'];
    $description = sanitizeInput($data['description']);
    $display_order = (int)($data['display_order'] ?? 0);
    
    if (empty($name) || empty($slug) || $language_id <= 0) {
        return ['success' => false, 'message' => 'Name, slug, and language are required'];
    }
    
    // Validate slug format
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        return ['success' => false, 'message' => 'Slug must contain only lowercase letters, numbers, and hyphens'];
    }
    
    // Check for existing category
    $check_sql = "SELECT id FROM categories WHERE slug = ? AND language_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("si", $slug, $language_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Category with this slug already exists for this language'];
    }
    
    // Insert new category
    $insert_sql = "INSERT INTO categories (name, slug, language_id, description, display_order) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssisi", $name, $slug, $language_id, $description, $display_order);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Category added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add category'];
    }
}

function addFlashcard($data) {
    global $conn;
    
    $category_id = (int)$data['category_id'];
    $native_text = sanitizeInput($data['native_text']);
    $foreign_text = sanitizeInput($data['foreign_text']);
    $pronunciation = sanitizeInput($data['pronunciation'] ?? '');
    $image_url = sanitizeInput($data['image_url'] ?? 'https://placehold.co/300x200');
    $xp_value = (int)($data['xp_value'] ?? 10);
    $difficulty = sanitizeInput($data['difficulty'] ?? 'beginner');
    $display_order = (int)($data['display_order'] ?? 0);
    
    if (empty($native_text) || empty($foreign_text) || $category_id <= 0) {
        return ['success' => false, 'message' => 'Native text, foreign text, and category are required'];
    }
    
    if (!in_array($difficulty, ['beginner', 'intermediate', 'advanced'])) {
        $difficulty = 'beginner';
    }
    
    if ($xp_value < 1 || $xp_value > 100) {
        $xp_value = 10;
    }
    
    // Insert new flashcard
    $insert_sql = "INSERT INTO flashcards (category_id, native_text, foreign_text, pronunciation, image_url, xp_value, difficulty, display_order) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("issssisi", $category_id, $native_text, $foreign_text, $pronunciation, $image_url, $xp_value, $difficulty, $display_order);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Flashcard added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add flashcard'];
    }
}

function addQuizQuestion($data) {
    global $conn;
    
    $flashcard_id = (int)$data['flashcard_id'];
    $question = sanitizeInput($data['question']);
    $correct_answer = sanitizeInput($data['correct_answer']);
    $wrong_answer1 = sanitizeInput($data['wrong_answer1']);
    $wrong_answer2 = sanitizeInput($data['wrong_answer2']);
    $wrong_answer3 = sanitizeInput($data['wrong_answer3']);
    
    if ($flashcard_id <= 0 || empty($question) || empty($correct_answer) || empty($wrong_answer1) || empty($wrong_answer2) || empty($wrong_answer3)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    // Insert new quiz question
    $insert_sql = "INSERT INTO quiz_questions (flashcard_id, question, correct_answer, wrong_answer1, wrong_answer2, wrong_answer3) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("isssss", $flashcard_id, $question, $correct_answer, $wrong_answer1, $wrong_answer2, $wrong_answer3);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Quiz question added successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to add quiz question'];
    }
}

function handleDelete($tab, $id) {
    global $conn;
    
    $table_map = [
        'users' => ['table' => 'users', 'name' => 'user'],
        'languages' => ['table' => 'languages', 'name' => 'language'],
        'categories' => ['table' => 'categories', 'name' => 'category'],
        'flashcards' => ['table' => 'flashcards', 'name' => 'flashcard'],
        'quiz' => ['table' => 'quiz_questions', 'name' => 'quiz question']
    ];
    
    if (!isset($table_map[$tab])) {
        return ['success' => false, 'message' => 'Invalid item type'];
    }
    
    $table_info = $table_map[$tab];
    
    // Prevent admin from deleting themselves
    if ($tab === 'users' && $id === (int)$_SESSION['user_id']) {
        return ['success' => false, 'message' => 'Cannot delete your own account'];
    }
    
    // Prevent deletion of the last admin
    if ($tab === 'users') {
        $admin_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
        if ($admin_count <= 1) {
            $user = $conn->query("SELECT role FROM users WHERE id = $id")->fetch_assoc();
            if ($user && $user['role'] === 'admin') {
                return ['success' => false, 'message' => 'Cannot delete the last admin user'];
            }
        }
    }
    
    $delete_sql = "DELETE FROM {$table_info['table']} WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => ucfirst($table_info['name']) . ' deleted successfully'];
    } else {
        return ['success' => false, 'message' => 'Failed to delete ' . $table_info['name']];
    }
}

// Fetch data based on current tab
$data = [];
switch ($tab) {
    case 'dashboard':
        $data['stats'] = getAdminStats();
        $data['recent_users'] = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
        $data['recent_activity'] = $conn->query("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'users':
        $data['users'] = $conn->query("SELECT id, username, email, role, current_streak, total_xp, last_activity_date, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'languages':
        $data['languages'] = $conn->query("SELECT * FROM languages ORDER BY display_order, name")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'categories':
        $data['categories'] = $conn->query("SELECT c.*, l.name as language_name FROM categories c LEFT JOIN languages l ON c.language_id = l.id ORDER BY l.name, c.display_order")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'flashcards':
        $data['flashcards'] = $conn->query("SELECT f.*, c.name as category_name, l.name as language_name FROM flashcards f LEFT JOIN categories c ON f.category_id = c.id LEFT JOIN languages l ON c.language_id = l.id ORDER BY l.name, c.name, f.display_order")->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'quiz':
        $data['quiz_questions'] = $conn->query("SELECT q.*, f.native_text, f.foreign_text, c.name as category_name, l.name as language_name FROM quiz_questions q LEFT JOIN flashcards f ON q.flashcard_id = f.id LEFT JOIN categories c ON f.category_id = c.id LEFT JOIN languages l ON c.language_id = l.id ORDER BY l.name, c.name")->fetch_all(MYSQLI_ASSOC);
        break;
}

// Get options for dropdowns
$languages = $conn->query("SELECT * FROM languages ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT c.*, l.name as language_name FROM categories c LEFT JOIN languages l ON c.language_id = l.id ORDER BY l.name, c.name")->fetch_all(MYSQLI_ASSOC);
$flashcards = $conn->query("SELECT f.*, c.name as category_name, l.name as language_name FROM flashcards f LEFT JOIN categories c ON f.category_id = c.id LEFT JOIN languages l ON c.language_id = l.id ORDER BY l.name, c.name, f.native_text")->fetch_all(MYSQLI_ASSOC);

include_once '../includes/header.php';
?>

<main class="admin-page" ng-app="perfectoApp" ng-controller="AdminController">
    <div class="container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <?php if (!empty($message)): ?>
                <div class="admin-message <?php echo $success ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="admin-layout">
            <nav class="admin-sidebar">
                <div class="admin-user">
                    <div class="admin-avatar">
                        <img src="https://placehold.co/60x60/58CC02/FFFFFF?text=<?php echo substr($_SESSION['username'], 0, 1); ?>" alt="Admin">
                    </div>
                    <div class="admin-info">
                        <h3><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                        <p>Administrator</p>
                    </div>
                </div>
                
                <ul class="admin-nav">
                    <li class="<?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
                        <a href="?tab=dashboard">
                            <i class="nav-icon dashboard-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo $tab === 'users' ? 'active' : ''; ?>">
                        <a href="?tab=users">
                            <i class="nav-icon users-icon"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="<?php echo $tab === 'languages' ? 'active' : ''; ?>">
                        <a href="?tab=languages">
                            <i class="nav-icon languages-icon"></i>
                            <span>Languages</span>
                        </a>
                    </li>
                    <li class="<?php echo $tab === 'categories' ? 'active' : ''; ?>">
                        <a href="?tab=categories">
                            <i class="nav-icon categories-icon"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="<?php echo $tab === 'flashcards' ? 'active' : ''; ?>">
                        <a href="?tab=flashcards">
                            <i class="nav-icon flashcards-icon"></i>
                            <span>Flashcards</span>
                        </a>
                    </li>
                    <li class="<?php echo $tab === 'quiz' ? 'active' : ''; ?>">
                        <a href="?tab=quiz">
                            <i class="nav-icon quiz-icon"></i>
                            <span>Quiz Questions</span>
                        </a>
                    </li>
                    <li class="<?php echo $tab === 'settings' ? 'active' : ''; ?>">
                        <a href="?tab=settings">
                            <i class="nav-icon settings-icon"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="admin-content">
                <?php
                // Include the specific tab content
                $tab_file = "admin-tabs/{$tab}.php";
                if (file_exists($tab_file)) {
                    include $tab_file;
                } else {
                    echo '<div class="admin-panel"><h2>Tab not found</h2><p>The requested tab does not exist.</p></div>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<script>
// Admin JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Toggle panels
    window.togglePanel = function(panelId) {
        const panel = document.getElementById(panelId);
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }
    };
    
    // AJAX helper function
    window.adminAjax = function(action, data, callback) {
        const formData = new FormData();
        formData.append('ajax_action', action);
        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
        
        for (const key in data) {
            formData.append(key, data[key]);
        }
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(callback)
        .catch(error => {
            console.error('Error:', error);
            callback({success: false, message: 'Network error'});
        });
    };
    
    // Auto-refresh stats every 30 seconds
    if (document.querySelector('.stats-grid')) {
        setInterval(function() {
            adminAjax('get_stats', {}, function(response) {
                if (response.success) {
                    updateStatsDisplay(response.data);
                }
            });
        }, 30000);
    }
    
    function updateStatsDisplay(stats) {
        const statElements = {
            'total-users': stats.total_users,
            'active-users': stats.active_users,
            'total-languages': stats.total_languages,
            'total-flashcards': stats.total_flashcards
        };
        
        for (const [id, value] of Object.entries(statElements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }
    }
});

// Angular admin controller
perfectoApp.controller('AdminController', function($scope) {
    $scope.currentTab = '<?php echo $tab; ?>';
    $scope.message = '<?php echo addslashes($message); ?>';
    $scope.success = <?php echo $success ? 'true' : 'false'; ?>;
    
    // Auto-hide messages after 5 seconds
    if ($scope.message) {
        setTimeout(function() {
            const messageEl = document.querySelector('.admin-message');
            if (messageEl) {
                messageEl.style.opacity = '0';
                setTimeout(function() {
                    messageEl.style.display = 'none';
                }, 300);
            }
        }, 5000);
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>