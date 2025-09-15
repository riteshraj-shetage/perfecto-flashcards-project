-- Database schema for perfecto language learning app

-- Create database
CREATE DATABASE IF NOT EXISTS perfectopro;
USE perfectopro;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password using PHP password_hash()',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    current_streak INT DEFAULT 0,
    longest_streak INT DEFAULT 0,
    last_activity_date DATE,
    total_xp INT DEFAULT 0,
    learning_goal ENUM('casual', 'regular', 'intensive') DEFAULT 'regular',
    daily_goal INT DEFAULT 50 COMMENT 'Daily XP goal',
    timezone VARCHAR(50) DEFAULT 'UTC',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reset_token (reset_token),
    INDEX idx_email (email),
    INDEX idx_last_activity (last_activity_date)
);

-- Create languages table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    flag_url VARCHAR(255) NOT NULL,
    phrase_count INT DEFAULT 0,
    display_order INT DEFAULT 0
);

-- Create categories table (e.g., Greetings, Food, Travel)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
);

-- Create flashcards table
CREATE TABLE IF NOT EXISTS flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    native_text VARCHAR(255) NOT NULL,
    foreign_text VARCHAR(255) NOT NULL,
    pronunciation VARCHAR(255),
    image_url VARCHAR(255) DEFAULT 'https://placehold.co/300x200',
    xp_value INT DEFAULT 10,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    display_order INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create quiz questions table
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flashcard_id INT NOT NULL,
    question VARCHAR(255) NOT NULL,
    correct_answer VARCHAR(255) NOT NULL,
    wrong_answer1 VARCHAR(255) NOT NULL,
    wrong_answer2 VARCHAR(255) NOT NULL,
    wrong_answer3 VARCHAR(255) NOT NULL,
    FOREIGN KEY (flashcard_id) REFERENCES flashcards(id) ON DELETE CASCADE
);

-- Create user progress table
CREATE TABLE IF NOT EXISTS progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    language_id INT NOT NULL,
    category_id INT NOT NULL,
    completed_lessons JSON DEFAULT NULL, -- Stores array of completed flashcard IDs
    lessons_completed INT DEFAULT 0,
    total_lessons INT DEFAULT 0,
    completion_percentage DECIMAL(5,2) DEFAULT 0,
    quiz_unlocked BOOLEAN DEFAULT FALSE,
    quiz_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY user_language_category (user_id, language_id, category_id)
);

-- Create achievements table
CREATE TABLE IF NOT EXISTS achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icon_url VARCHAR(255) DEFAULT 'https://placehold.co/100x100'
);

-- Create user achievements junction table
CREATE TABLE IF NOT EXISTS user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY user_achievement (user_id, achievement_id)
);

-- Create login attempts table for rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL, -- email or IP address
    success BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier_time (identifier, created_at),
    INDEX idx_created_at (created_at)
);

-- Create activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);

-- Create settings table for dynamic configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create user sessions table for better session management
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_activity (user_id, last_activity)
);



-- Create user streaks table for detailed tracking
CREATE TABLE IF NOT EXISTS user_streaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    streak_date DATE NOT NULL,
    xp_earned INT DEFAULT 0,
    lessons_completed INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY user_date (user_id, streak_date),
    INDEX idx_user_date (user_id, streak_date)
);