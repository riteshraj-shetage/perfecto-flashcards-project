<?php
include_once '../includes/config.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    redirectTo(SITE_URL);
}

// Initialize variables
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Handle user actions
if ($tab === 'users' && $action === 'delete' && $id > 0) {
    // Prevent deleting self
    if ($id !== (int)$_SESSION['user_id']) {
        $delete_sql = "DELETE FROM users WHERE id = $id AND role != 'admin'";
        if ($conn->query($delete_sql)) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user: " . $conn->error;
        }
    } else {
        $message = "You cannot delete your own account.";
    }
}

// Handle content actions
if ($tab === 'content' && $action === 'delete' && $id > 0) {
    // Determine the table based on the content type
    $content_type = isset($_GET['type']) ? $_GET['type'] : '';
    $table = '';
    
    switch ($content_type) {
        case 'category':
            $table = 'categories';
            break;
        case 'flashcard':
            $table = 'flashcards';
            break;
        case 'quiz':
            $table = 'quiz_questions';
            break;
    }
    
    if (!empty($table)) {
        $delete_sql = "DELETE FROM $table WHERE id = $id";
        if ($conn->query($delete_sql)) {
            $message = ucfirst($content_type) . " deleted successfully.";
        } else {
            $message = "Error deleting " . $content_type . ": " . $conn->error;
        }
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password']; // No hashing as per requirements
        $role = sanitizeInput($_POST['role']);
        
        $insert_sql = "INSERT INTO users (username, email, password, role, created_at) 
                      VALUES ('$username', '$email', '$password', '$role', NOW())";
        
        if ($conn->query($insert_sql)) {
            $message = "User added successfully.";
        } else {
            $message = "Error adding user: " . $conn->error;
        }
    }
    
    if (isset($_POST['add_category'])) {
        $name = sanitizeInput($_POST['name']);
        $slug = sanitizeInput($_POST['slug']);
        $language_id = (int)$_POST['language_id'];
        $description = sanitizeInput($_POST['description']);
        
        $insert_sql = "INSERT INTO categories (name, slug, language_id, description) 
                      VALUES ('$name', '$slug', $language_id, '$description')";
        
        if ($conn->query($insert_sql)) {
            $message = "Category added successfully.";
        } else {
            $message = "Error adding category: " . $conn->error;
        }
    }
    
    if (isset($_POST['add_flashcard'])) {
        $category_id = (int)$_POST['category_id'];
        $native_text = sanitizeInput($_POST['native_text']);
        $foreign_text = sanitizeInput($_POST['foreign_text']);
        $image_url = sanitizeInput($_POST['image_url']);
        
        $insert_sql = "INSERT INTO flashcards (category_id, native_text, foreign_text, image_url) 
                      VALUES ($category_id, '$native_text', '$foreign_text', '$image_url')";
        
        if ($conn->query($insert_sql)) {
            $message = "Flashcard added successfully.";
        } else {
            $message = "Error adding flashcard: " . $conn->error;
        }
    }
    
    if (isset($_POST['add_quiz'])) {
        $flashcard_id = (int)$_POST['flashcard_id'];
        $question = sanitizeInput($_POST['question']);
        $correct_answer = sanitizeInput($_POST['correct_answer']);
        $wrong_answer1 = sanitizeInput($_POST['wrong_answer1']);
        $wrong_answer2 = sanitizeInput($_POST['wrong_answer2']);
        $wrong_answer3 = sanitizeInput($_POST['wrong_answer3']);
        
        $insert_sql = "INSERT INTO quiz_questions (flashcard_id, question, correct_answer, wrong_answer1, wrong_answer2, wrong_answer3) 
                      VALUES ($flashcard_id, '$question', '$correct_answer', '$wrong_answer1', '$wrong_answer2', '$wrong_answer3')";
        
        if ($conn->query($insert_sql)) {
            $message = "Quiz question added successfully.";
        } else {
            $message = "Error adding quiz question: " . $conn->error;
        }
    }
}

// Fetch data based on current tab
$users = [];
$languages = [];
$categories = [];
$flashcards = [];
$quiz_questions = [];

// Fetch users
if ($tab === 'users') {
    $users_sql = "SELECT * FROM users ORDER BY id ASC";
    $users_result = $conn->query($users_sql);
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch languages for dropdowns
$languages_sql = "SELECT * FROM languages ORDER BY name";
$languages_result = $conn->query($languages_sql);
while ($row = $languages_result->fetch_assoc()) {
    $languages[] = $row;
}

// Fetch categories
if ($tab === 'content') {
    $categories_sql = "SELECT c.*, l.name as language_name 
                      FROM categories c 
                      JOIN languages l ON c.language_id = l.id 
                      ORDER BY c.id ASC";
    $categories_result = $conn->query($categories_sql);
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    // Fetch flashcards
    $flashcards_sql = "SELECT f.*, c.name as category_name, l.name as language_name 
                      FROM flashcards f 
                      JOIN categories c ON f.category_id = c.id 
                      JOIN languages l ON c.language_id = l.id 
                      ORDER BY f.id ASC";
    $flashcards_result = $conn->query($flashcards_sql);
    while ($row = $flashcards_result->fetch_assoc()) {
        $flashcards[] = $row;
    }
    
    // Fetch quiz questions
    $quiz_sql = "SELECT q.*, f.native_text, f.foreign_text, c.name as category_name, l.name as language_name 
                FROM quiz_questions q 
                JOIN flashcards f ON q.flashcard_id = f.id 
                JOIN categories c ON f.category_id = c.id 
                JOIN languages l ON c.language_id = l.id 
                ORDER BY q.id ASC";
    $quiz_result = $conn->query($quiz_sql);
    while ($row = $quiz_result->fetch_assoc()) {
        $quiz_questions[] = $row;
    }
}

include_once '../includes/header.php';
?>

<main class="admin-page" ng-app="perfectoApp" ng-controller="AdminController">
    <div class="container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <?php if (!empty($message)): ?>
                <div class="admin-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="admin-tabs">
            <a href="?tab=users" class="tab-item <?php echo $tab === 'users' ? 'active' : ''; ?>">
                <i class="tab-icon users-icon"></i>
                <span>Users</span>
            </a>
            <a href="?tab=content" class="tab-item <?php echo $tab === 'content' ? 'active' : ''; ?>">
                <i class="tab-icon content-icon"></i>
                <span>Content</span>
            </a>
        </div>
        
        <div class="admin-content">
            <?php if ($tab === 'users'): ?>
                <div class="panel">
                    <h2>User Management</h2>
                    <button class="btn btn-primary add-btn" onclick="togglePanel('add-user-panel')">Add New User</button>
                    
                    <div id="add-user-panel" class="form-panel" style="display: none;">
                        <h3>Add New User</h3>
                        <form method="post" action="" class="admin-form">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" required>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                                <button type="button" class="btn btn-secondary" onclick="togglePanel('add-user-panel')">Cancel</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td class="actions">
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <a href="?tab=users&action=delete&id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                            <?php else: ?>
                                                <span class="current-user">Current user</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            <?php elseif ($tab === 'content'): ?>
                <div class="content-tabs">
                    <div class="tab-nav">
                        <button class="tab-nav-item active" onclick="showContentTab('categories')">Categories</button>
                        <button class="tab-nav-item" onclick="showContentTab('flashcards')">Flashcards</button>
                        <button class="tab-nav-item" onclick="showContentTab('quizzes')">Quiz Questions</button>
                    </div>
                    
                    <div id="categories-tab" class="content-tab active">
                        <div class="panel">
                            <h2>Categories</h2>
                            <button class="btn btn-primary add-btn" onclick="togglePanel('add-category-panel')">Add New Category</button>
                            
                            <div id="add-category-panel" class="form-panel" style="display: none;">
                                <h3>Add New Category</h3>
                                <form method="post" action="" class="admin-form">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" id="name" name="name" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="slug">Slug</label>
                                        <input type="text" id="slug" name="slug" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="language_id">Language</label>
                                        <select id="language_id" name="language_id" required>
                                            <?php foreach ($languages as $language): ?>
                                                <option value="<?php echo $language['id']; ?>"><?php echo $language['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea id="description" name="description" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="form-buttons">
                                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                                        <button type="button" class="btn btn-secondary" onclick="togglePanel('add-category-panel')">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Language</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $category['id']; ?></td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td><?php echo $category['slug']; ?></td>
                                                <td><?php echo $category['language_name']; ?></td>
                                                <td><?php echo substr(htmlspecialchars($category['description']), 0, 50) . (strlen($category['description']) > 50 ? '...' : ''); ?></td>
                                                <td class="actions">
                                                    <a href="?tab=content&action=delete&type=category&id=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div id="flashcards-tab" class="content-tab">
                        <div class="panel">
                            <h2>Flashcards</h2>
                            <button class="btn btn-primary add-btn" onclick="togglePanel('add-flashcard-panel')">Add New Flashcard</button>
                            
                            <div id="add-flashcard-panel" class="form-panel" style="display: none;">
                                <h3>Add New Flashcard</h3>
                                <form method="post" action="" class="admin-form">
                                    <div class="form-group">
                                        <label for="category_id">Category</label>
                                        <select id="category_id" name="category_id" required>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"><?php echo $category['language_name'] . ' - ' . $category['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="native_text">Native Text</label>
                                        <input type="text" id="native_text" name="native_text" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="foreign_text">Foreign Text</label>
                                        <input type="text" id="foreign_text" name="foreign_text" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="image_url">Image URL</label>
                                        <input type="text" id="image_url" name="image_url" value="https://placehold.co/300x200">
                                    </div>
                                    
                                    <div class="form-buttons">
                                        <button type="submit" name="add_flashcard" class="btn btn-primary">Add Flashcard</button>
                                        <button type="button" class="btn btn-secondary" onclick="togglePanel('add-flashcard-panel')">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Language</th>
                                            <th>Category</th>
                                            <th>Native Text</th>
                                            <th>Foreign Text</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($flashcards as $flashcard): ?>
                                            <tr>
                                                <td><?php echo $flashcard['id']; ?></td>
                                                <td><?php echo $flashcard['language_name']; ?></td>
                                                <td><?php echo $flashcard['category_name']; ?></td>
                                                <td><?php echo htmlspecialchars($flashcard['native_text']); ?></td>
                                                <td><?php echo htmlspecialchars($flashcard['foreign_text']); ?></td>
                                                <td class="actions">
                                                    <a href="?tab=content&action=delete&type=flashcard&id=<?php echo $flashcard['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this flashcard?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div id="quizzes-tab" class="content-tab">
                        <div class="panel">
                            <h2>Quiz Questions</h2>
                            <button class="btn btn-primary add-btn" onclick="togglePanel('add-quiz-panel')">Add New Quiz Question</button>
                            
                            <div id="add-quiz-panel" class="form-panel" style="display: none;">
                                <h3>Add New Quiz Question</h3>
                                <form method="post" action="" class="admin-form">
                                    <div class="form-group">
                                        <label for="flashcard_id">Flashcard</label>
                                        <select id="flashcard_id" name="flashcard_id" required>
                                            <?php foreach ($flashcards as $flashcard): ?>
                                                <option value="<?php echo $flashcard['id']; ?>"><?php echo $flashcard['language_name'] . ' - ' . $flashcard['native_text'] . ' / ' . $flashcard['foreign_text']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="question">Question</label>
                                        <input type="text" id="question" name="question" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="correct_answer">Correct Answer</label>
                                        <input type="text" id="correct_answer" name="correct_answer" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="wrong_answer1">Wrong Answer 1</label>
                                        <input type="text" id="wrong_answer1" name="wrong_answer1" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="wrong_answer2">Wrong Answer 2</label>
                                        <input type="text" id="wrong_answer2" name="wrong_answer2" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="wrong_answer3">Wrong Answer 3</label>
                                        <input type="text" id="wrong_answer3" name="wrong_answer3" required>
                                    </div>
                                    
                                    <div class="form-buttons">
                                        <button type="submit" name="add_quiz" class="btn btn-primary">Add Quiz Question</button>
                                        <button type="button" class="btn btn-secondary" onclick="togglePanel('add-quiz-panel')">Cancel</button>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Language</th>
                                            <th>Flashcard</th>
                                            <th>Question</th>
                                            <th>Correct Answer</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quiz_questions as $quiz): ?>
                                            <tr>
                                                <td><?php echo $quiz['id']; ?></td>
                                                <td><?php echo $quiz['language_name']; ?></td>
                                                <td><?php echo htmlspecialchars($quiz['native_text'] . ' / ' . $quiz['foreign_text']); ?></td>
                                                <td><?php echo htmlspecialchars($quiz['question']); ?></td>
                                                <td><?php echo htmlspecialchars($quiz['correct_answer']); ?></td>
                                                <td class="actions">
                                                    <a href="?tab=content&action=delete&type=quiz&id=<?php echo $quiz['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this quiz question?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function togglePanel(panelId) {
    var panel = document.getElementById(panelId);
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

function showContentTab(tabId) {
    // Hide all tabs
    var tabs = document.getElementsByClassName('content-tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
    }
    
    // Show selected tab
    document.getElementById(tabId + '-tab').classList.add('active');
    
    // Update tab nav
    var tabNavItems = document.getElementsByClassName('tab-nav-item');
    for (var i = 0; i < tabNavItems.length; i++) {
        tabNavItems[i].classList.remove('active');
    }
    
    // Find and activate the clicked tab nav item
    var tabNavs = document.getElementsByClassName('tab-nav-item');
    for (var i = 0; i < tabNavs.length; i++) {
        if (tabNavs[i].textContent.toLowerCase().includes(tabId.replace('-', ' '))) {
            tabNavs[i].classList.add('active');
        }
    }
}
</script>

<?php include_once '../includes/footer.php'; ?>