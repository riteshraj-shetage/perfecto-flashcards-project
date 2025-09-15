<div class="admin-panel">
    <div class="panel-header">
        <h2>User Management</h2>
        <div class="panel-actions">
            <button class="btn btn-primary" onclick="togglePanel('add-user-panel')">
                <i class="btn-icon add-icon"></i>
                Add New User
            </button>
        </div>
    </div>
    
    <!-- Add User Panel -->
    <div id="add-user-panel" class="form-panel" style="display: none;">
        <div class="panel-header">
            <h3>Add New User</h3>
            <button class="btn btn-icon btn-close" onclick="togglePanel('add-user-panel')">&times;</button>
        </div>
        
        <form method="post" action="" class="admin-form" id="add-user-form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required 
                           minlength="<?php echo USERNAME_MIN_LENGTH; ?>"
                           maxlength="<?php echo USERNAME_MAX_LENGTH; ?>"
                           pattern="[a-zA-Z0-9_-]+"
                           aria-describedby="username-help">
                    <div id="username-help" class="field-help">3-50 characters, letters, numbers, underscores, and hyphens only</div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                           aria-describedby="password-help">
                    <div id="password-help" class="field-help">At least <?php echo PASSWORD_MIN_LENGTH; ?> characters with uppercase, lowercase, and numbers</div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role <span class="required">*</span></label>
                    <select id="role" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_user" class="btn btn-primary">
                    <i class="btn-icon save-icon"></i>
                    Add User
                </button>
                <button type="button" class="btn btn-secondary" onclick="togglePanel('add-user-panel')">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-controls">
                <div class="search-box">
                    <input type="text" id="user-search" placeholder="Search users..." onkeyup="filterTable('users-table', this.value)">
                    <i class="search-icon"></i>
                </div>
                <div class="table-filters">
                    <select id="role-filter" onchange="filterByRole(this.value)">
                        <option value="">All Roles</option>
                        <option value="user">Users</option>
                        <option value="admin">Admins</option>
                    </select>
                </div>
            </div>
        </div>
        
        <table class="admin-table" id="users-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>XP</th>
                    <th>Streak</th>
                    <th>Last Active</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['users'])): ?>
                    <?php foreach ($data['users'] as $user): ?>
                        <tr data-role="<?php echo $user['role']; ?>">
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <img src="https://placehold.co/40x40/58CC02/FFFFFF?text=<?php echo substr($user['username'], 0, 1); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                                    </div>
                                    <div class="user-details">
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <span class="user-id">#<?php echo $user['id']; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge <?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="xp-display">
                                    <span class="xp-amount"><?php echo number_format($user['total_xp']); ?></span>
                                    <span class="xp-label">XP</span>
                                </div>
                            </td>
                            <td>
                                <div class="streak-display">
                                    <span class="streak-flame">🔥</span>
                                    <span class="streak-count"><?php echo $user['current_streak']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($user['last_activity_date']): ?>
                                    <span class="activity-date" title="<?php echo $user['last_activity_date']; ?>">
                                        <?php echo timeAgo($user['last_activity_date'] . ' 00:00:00'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="never-active">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="join-date" title="<?php echo $user['created_at']; ?>">
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-secondary" onclick="editUser(<?php echo $user['id']; ?>)" title="Edit User">
                                            <i class="btn-icon edit-icon"></i>
                                        </button>
                                        <a href="?tab=users&action=delete&id=<?php echo $user['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this user?')"
                                           title="Delete User">
                                            <i class="btn-icon delete-icon"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="self-indicator">You</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length - 1; j++) { // Skip actions column
            if (cells[j].textContent.toLowerCase().indexOf(searchTerm.toLowerCase()) > -1) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

function filterByRole(role) {
    const table = document.getElementById('users-table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const userRole = row.getAttribute('data-role');
        
        if (role === '' || userRole === role) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function editUser(userId) {
    // Implement user editing functionality
    alert('Edit user functionality would go here for user ID: ' + userId);
}

// Form validation
document.getElementById('add-user-form').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    // Basic validation
    if (!username || !email || !password) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return false;
    }
    
    // Username validation
    if (!/^[a-zA-Z0-9_-]+$/.test(username) || username.length < 3 || username.length > 50) {
        e.preventDefault();
        alert('Username must be 3-50 characters and contain only letters, numbers, underscores, and hyphens');
        return false;
    }
    
    // Email validation
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address');
        return false;
    }
    
    // Password validation
    if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?> || 
        !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
        e.preventDefault();
        alert('Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters and contain uppercase, lowercase, and numeric characters');
        return false;
    }
});

// Helper function from dashboard.php
<?php if (!function_exists('timeAgo')): ?>
function timeAgo(datetime) {
    // This would be implemented in JavaScript for real-time updates
    return datetime;
}
<?php endif; ?>
</script>