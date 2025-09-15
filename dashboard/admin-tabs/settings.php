<div class="admin-panel">
    <div class="panel-header">
        <h2>System Settings</h2>
        <p>Configure global application settings and preferences.</p>
    </div>
    
    <div class="settings-grid">
        <!-- General Settings -->
        <div class="settings-section">
            <h3>General Settings</h3>
            <div class="settings-card">
                <form method="post" action="" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="settings_section" value="general">
                    
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" value="<?php echo SITE_NAME; ?>" maxlength="100">
                        <div class="field-help">The name of your language learning platform</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_url">Site URL</label>
                        <input type="url" id="site_url" name="site_url" value="<?php echo SITE_URL; ?>">
                        <div class="field-help">The base URL of your application</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="default_language">Default Language</label>
                        <select id="default_language" name="default_language">
                            <option value="">Select default language...</option>
                            <?php foreach ($languages as $language): ?>
                                <option value="<?php echo $language['code']; ?>">
                                    <?php echo htmlspecialchars($language['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="field-help">Default language for new users</div>
                    </div>
                    
                    <button type="submit" name="save_general" class="btn btn-primary">Save General Settings</button>
                </form>
            </div>
        </div>
        
        <!-- User Settings -->
        <div class="settings-section">
            <h3>User Settings</h3>
            <div class="settings-card">
                <form method="post" action="" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="settings_section" value="users">
                    
                    <div class="form-group">
                        <label for="allow_registration">Allow User Registration</label>
                        <select id="allow_registration" name="allow_registration">
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                        <div class="field-help">Allow new users to register accounts</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="default_daily_goal">Default Daily XP Goal</label>
                        <input type="number" id="default_daily_goal" name="default_daily_goal" value="50" min="10" max="500">
                        <div class="field-help">Default daily XP goal for new users</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_min_length">Minimum Password Length</label>
                        <input type="number" id="password_min_length" name="password_min_length" value="<?php echo PASSWORD_MIN_LENGTH; ?>" min="6" max="32">
                        <div class="field-help">Minimum required password length</div>
                    </div>
                    
                    <button type="submit" name="save_users" class="btn btn-primary">Save User Settings</button>
                </form>
            </div>
        </div>
        
        <!-- Learning Settings -->
        <div class="settings-section">
            <h3>Learning Settings</h3>
            <div class="settings-card">
                <form method="post" action="" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="settings_section" value="learning">
                    
                    <div class="form-group">
                        <label for="default_xp_per_flashcard">Default XP per Flashcard</label>
                        <input type="number" id="default_xp_per_flashcard" name="default_xp_per_flashcard" value="10" min="1" max="100">
                        <div class="field-help">Default XP awarded for completing a flashcard</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quiz_unlock_threshold">Quiz Unlock Threshold</label>
                        <input type="number" id="quiz_unlock_threshold" name="quiz_unlock_threshold" value="100" min="0" max="100">
                        <div class="field-help">Percentage of lessons that must be completed to unlock quiz (%)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="streak_freeze_days">Streak Freeze Days</label>
                        <input type="number" id="streak_freeze_days" name="streak_freeze_days" value="1" min="0" max="7">
                        <div class="field-help">Number of days a user can miss before losing their streak</div>
                    </div>
                    
                    <button type="submit" name="save_learning" class="btn btn-primary">Save Learning Settings</button>
                </form>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="settings-section">
            <h3>System Information</h3>
            <div class="settings-card">
                <div class="system-info">
                    <div class="info-item">
                        <label>Application Version</label>
                        <span>1.0.0</span>
                    </div>
                    
                    <div class="info-item">
                        <label>PHP Version</label>
                        <span><?php echo PHP_VERSION; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Database Server</label>
                        <span><?php echo $conn->server_info; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Environment</label>
                        <span class="env-badge <?php echo APP_ENV; ?>">
                            <?php echo ucfirst(APP_ENV); ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <label>Server Time</label>
                        <span><?php echo date('Y-m-d H:i:s T'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Disk Space</label>
                        <span>
                            <?php 
                            $bytes = disk_free_space('.');
                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                            for ($i = 0; $bytes > 1024; $i++) {
                                $bytes /= 1024;
                            }
                            echo round($bytes, 2) . ' ' . $units[$i] . ' free';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Settings -->
        <div class="settings-section">
            <h3>Security Settings</h3>
            <div class="settings-card">
                <form method="post" action="" class="settings-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="settings_section" value="security">
                    
                    <div class="form-group">
                        <label for="max_login_attempts">Max Login Attempts</label>
                        <input type="number" id="max_login_attempts" name="max_login_attempts" value="<?php echo MAX_LOGIN_ATTEMPTS; ?>" min="3" max="20">
                        <div class="field-help">Maximum failed login attempts before lockout</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="lockout_duration">Lockout Duration (minutes)</label>
                        <input type="number" id="lockout_duration" name="lockout_duration" value="<?php echo LOGIN_LOCKOUT_TIME / 60; ?>" min="5" max="1440">
                        <div class="field-help">How long to lock users out after failed attempts</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="session_timeout">Session Timeout (hours)</label>
                        <input type="number" id="session_timeout" name="session_timeout" value="24" min="1" max="720">
                        <div class="field-help">How long user sessions remain active</div>
                    </div>
                    
                    <button type="submit" name="save_security" class="btn btn-primary">Save Security Settings</button>
                </form>
            </div>
        </div>
        
        <!-- Maintenance -->
        <div class="settings-section">
            <h3>Maintenance</h3>
            <div class="settings-card">
                <div class="maintenance-actions">
                    <div class="maintenance-item">
                        <h4>Clear Activity Logs</h4>
                        <p>Remove old activity logs to free up database space.</p>
                        <button class="btn btn-warning" onclick="clearLogs()">Clear Old Logs</button>
                    </div>
                    
                    <div class="maintenance-item">
                        <h4>Reset User Streaks</h4>
                        <p>Reset all user streaks (use with caution).</p>
                        <button class="btn btn-danger" onclick="resetStreaks()">Reset All Streaks</button>
                    </div>
                    
                    <div class="maintenance-item">
                        <h4>Database Backup</h4>
                        <p>Create a backup of the current database.</p>
                        <button class="btn btn-primary" onclick="createBackup()">Create Backup</button>
                    </div>
                    
                    <div class="maintenance-item">
                        <h4>Cache Management</h4>
                        <p>Clear application cache and temporary files.</p>
                        <button class="btn btn-secondary" onclick="clearCache()">Clear Cache</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearLogs() {
    if (confirm('Are you sure you want to clear old activity logs? This action cannot be undone.')) {
        // Implement log clearing functionality
        adminAjax('clear_logs', {}, function(response) {
            if (response.success) {
                alert('Activity logs cleared successfully.');
            } else {
                alert('Error clearing logs: ' + response.message);
            }
        });
    }
}

function resetStreaks() {
    if (confirm('Are you sure you want to reset ALL user streaks? This will affect all users and cannot be undone.')) {
        // Implement streak reset functionality
        adminAjax('reset_streaks', {}, function(response) {
            if (response.success) {
                alert('All user streaks have been reset.');
            } else {
                alert('Error resetting streaks: ' + response.message);
            }
        });
    }
}

function createBackup() {
    if (confirm('Create a database backup? This may take a few minutes.')) {
        // Implement backup functionality
        adminAjax('create_backup', {}, function(response) {
            if (response.success) {
                alert('Database backup created successfully.');
            } else {
                alert('Error creating backup: ' + response.message);
            }
        });
    }
}

function clearCache() {
    if (confirm('Clear application cache?')) {
        // Implement cache clearing functionality
        adminAjax('clear_cache', {}, function(response) {
            if (response.success) {
                alert('Cache cleared successfully.');
            } else {
                alert('Error clearing cache: ' + response.message);
            }
        });
    }
}

// Form validation
document.querySelectorAll('.settings-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input[required]');
        let valid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.focus();
                alert('Please fill in all required fields');
                return false;
            }
        });
        
        if (!valid) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.settings-section {
    background-color: var(--background-light);
    border-radius: 12px;
    overflow: hidden;
}

.settings-section h3 {
    margin: 0;
    padding: 20px;
    background-color: var(--primary-color);
    color: white;
    font-size: 1.125rem;
}

.settings-card {
    padding: 25px;
    background-color: var(--background-white);
}

.settings-form .form-group {
    margin-bottom: 20px;
}

.settings-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: var(--text-dark);
}

.settings-form input,
.settings-form select {
    width: 100%;
    padding: 10px;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    font-size: 0.875rem;
}

.settings-form input:focus,
.settings-form select:focus {
    border-color: var(--primary-color);
    outline: none;
}

.system-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background-color: var(--background-light);
    border-radius: 6px;
}

.info-item label {
    font-weight: 600;
    color: var(--text-dark);
}

.info-item span {
    color: var(--text-medium);
    font-family: monospace;
}

.env-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.env-badge.development {
    background-color: var(--warning-color);
    color: white;
}

.env-badge.production {
    background-color: var(--success-color);
    color: white;
}

.maintenance-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.maintenance-item {
    padding: 20px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background-color: var(--background-light);
}

.maintenance-item h4 {
    margin: 0 0 8px 0;
    color: var(--text-dark);
}

.maintenance-item p {
    margin: 0 0 15px 0;
    color: var(--text-medium);
    font-size: 0.875rem;
}

.maintenance-item .btn {
    min-width: 140px;
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}
</style>