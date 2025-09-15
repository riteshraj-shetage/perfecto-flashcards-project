<div class="admin-panel">
    <div class="panel-header">
        <h2>Dashboard Overview</h2>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Here's what's happening in your language learning platform.</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon users-icon"></div>
            <div class="stat-content">
                <h3 id="total-users"><?php echo $data['stats']['total_users']; ?></h3>
                <p>Total Users</p>
                <span class="stat-detail"><?php echo $data['stats']['admin_users']; ?> admins</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active-icon"></div>
            <div class="stat-content">
                <h3 id="active-users"><?php echo $data['stats']['active_users']; ?></h3>
                <p>Active Users</p>
                <span class="stat-detail">Last 7 days</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon languages-icon"></div>
            <div class="stat-content">
                <h3 id="total-languages"><?php echo $data['stats']['total_languages']; ?></h3>
                <p>Languages</p>
                <span class="stat-detail"><?php echo $data['stats']['total_categories']; ?> categories</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon flashcards-icon"></div>
            <div class="stat-content">
                <h3 id="total-flashcards"><?php echo $data['stats']['total_flashcards']; ?></h3>
                <p>Flashcards</p>
                <span class="stat-detail"><?php echo $data['stats']['total_quiz_questions']; ?> quiz questions</span>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h3>Recent Users</h3>
            <div class="recent-list">
                <?php if (!empty($data['recent_users'])): ?>
                    <?php foreach ($data['recent_users'] as $user): ?>
                        <div class="recent-item">
                            <div class="item-avatar">
                                <img src="https://placehold.co/40x40/58CC02/FFFFFF?text=<?php echo substr($user['username'], 0, 1); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>
                            <div class="item-content">
                                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                <span class="item-meta">
                                    <?php echo ucfirst($user['role']); ?> • 
                                    Joined <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </span>
                            </div>
                            <div class="item-actions">
                                <a href="?tab=users&action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No users found</p>
                <?php endif; ?>
            </div>
            <a href="?tab=users" class="view-all-link">View all users →</a>
        </div>
        
        <div class="dashboard-section">
            <h3>Recent Activity</h3>
            <div class="activity-list">
                <?php if (!empty($data['recent_activity'])): ?>
                    <?php foreach ($data['recent_activity'] as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $activity['action']; ?>-icon"></div>
                            <div class="activity-content">
                                <p>
                                    <strong><?php echo $activity['username'] ?: 'Unknown User'; ?></strong>
                                    <?php echo formatActivityAction($activity['action']); ?>
                                </p>
                                <span class="activity-time"><?php echo timeAgo($activity['created_at']); ?></span>
                                <?php if (!empty($activity['details'])): ?>
                                    <div class="activity-details"><?php echo htmlspecialchars($activity['details']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-grid">
            <a href="?tab=users" class="action-card">
                <div class="action-icon users-icon"></div>
                <h4>Manage Users</h4>
                <p>Add, edit, or remove user accounts</p>
            </a>
            
            <a href="?tab=languages" class="action-card">
                <div class="action-icon languages-icon"></div>
                <h4>Add Language</h4>
                <p>Create new language courses</p>
            </a>
            
            <a href="?tab=flashcards" class="action-card">
                <div class="action-icon flashcards-icon"></div>
                <h4>Add Content</h4>
                <p>Create flashcards and lessons</p>
            </a>
            
            <a href="?tab=settings" class="action-card">
                <div class="action-icon settings-icon"></div>
                <h4>Settings</h4>
                <p>Configure system settings</p>
            </a>
        </div>
    </div>
</div>

<?php
function formatActivityAction($action) {
    $actions = [
        'login' => 'logged in',
        'logout' => 'logged out',
        'register' => 'registered',
        'password_reset' => 'reset their password',
        'password_reset_request' => 'requested a password reset',
        'admin_action' => 'performed an admin action',
        'failed_login' => 'failed to log in'
    ];
    
    return $actions[$action] ?? $action;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>