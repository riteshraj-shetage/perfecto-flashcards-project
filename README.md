# Perfecto Language Learning App

A modern, Duolingo-inspired language learning platform built with PHP, MySQL, and AngularJS. This comprehensive application provides an engaging way to learn new languages through flashcards, quizzes, and gamified progress tracking.

## 🌟 Features

### Core Learning Features
- **Interactive Flashcards**: Visual learning with images, pronunciations, and translations
- **Multiple Languages**: Support for Spanish, French, German, and easy addition of new languages
- **Categorized Lessons**: Organized content by topics (Greetings, Food, Travel, etc.)
- **Progress Tracking**: Comprehensive progress monitoring with XP and completion tracking
- **Quiz System**: Test knowledge with multiple-choice quizzes unlocked after completing lessons
- **Streak Tracking**: Maintain learning streaks to encourage daily practice
- **Difficulty Levels**: Beginner, Intermediate, and Advanced content classification

### User Experience
- **Responsive Design**: Optimized for desktop, tablet, and mobile devices
- **Duolingo-Inspired UI**: Clean, modern interface with engaging animations
- **Keyboard Navigation**: Full keyboard support for efficient learning
- **Grid and Card Views**: Multiple viewing options for different learning preferences
- **Real-time Feedback**: Instant validation and progress updates

### Security & Administration
- **Secure Authentication**: bcrypt password hashing with rate limiting
- **CSRF Protection**: Complete form security with token validation
- **Role-Based Access**: User and admin roles with appropriate permissions
- **Activity Logging**: Comprehensive audit trail for all user actions
- **Password Reset**: Secure token-based password recovery system

### Admin Panel
- **Dashboard Overview**: Real-time statistics and recent activity monitoring
- **User Management**: Advanced user administration with search and filtering
- **Content Management**: Complete CRUD operations for languages, categories, and flashcards
- **Quiz Management**: Create and test quiz questions with interactive preview
- **System Settings**: Configure application behavior and security settings
- **Maintenance Tools**: Database cleanup, backups, and system information

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher with MySQLi extension
- MySQL 5.7 or higher
- Web server (Apache/Nginx) with mod_rewrite enabled
- Git for version control

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/riteshraj-shetage/perfecto-flashcards-project.git
   cd perfecto-flashcards-project
   ```

2. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p < database/db_schema.sql
   
   # Seed with sample data
   mysql -u root -p < database/db_seed.sql
   ```

3. **Configuration**
   ```php
   # Update includes/config.php with your database credentials
   $db_host = 'localhost';
   $db_name = 'perfectopro';
   $db_user = 'your_username';
   $db_pass = 'your_password';
   ```

4. **Environment Setup**
   ```bash
   # Set appropriate file permissions
   chmod 755 logs/
   chmod 644 assets/css/* assets/js/*
   
   # Create logs directory if it doesn't exist
   mkdir -p logs
   ```

5. **Web Server Configuration**
   - Point your web server to the project root directory
   - Ensure mod_rewrite is enabled for clean URLs
   - Set up virtual host if using Apache/Nginx

### Demo Credentials

**Administrator Account:**
- Email: `admin@perfecto.com`
- Password: `admin123`

**Regular User Account:**
- Email: `user@perfecto.com`
- Password: `user123`

## 📁 Project Structure

```
perfecto/
├── assets/                    # Static assets
│   ├── css/
│   │   └── style.css         # Main stylesheet with responsive design
│   ├── images/               # Image assets and flags
│   └── js/
│       └── app.js           # AngularJS application logic
├── auth/                     # Authentication system
│   ├── login.php            # Secure login with rate limiting
│   ├── register.php         # User registration with validation
│   ├── logout.php           # Session cleanup
│   ├── forgot-password.php  # Password recovery
│   └── reset-password.php   # Secure password reset
├── dashboard/               # User and admin dashboards
│   ├── admin.php           # Main admin panel controller
│   ├── admin-tabs/         # Modular admin interface
│   │   ├── dashboard.php   # Admin overview
│   │   ├── users.php       # User management
│   │   ├── languages.php   # Language administration
│   │   ├── categories.php  # Category management
│   │   ├── flashcards.php  # Flashcard administration
│   │   ├── quiz.php        # Quiz management
│   │   └── settings.php    # System configuration
│   └── user.php            # User dashboard
├── database/               # Database schema and seed data
│   ├── db_schema.sql       # Complete database structure
│   └── db_seed.sql         # Sample data for testing
├── includes/               # Shared components
│   ├── config.php          # Configuration and helper functions
│   ├── header.php          # Common header
│   └── footer.php          # Common footer
├── learn/                  # Learning interface
│   └── index.php           # Interactive flashcard learning
├── quiz/                   # Quiz system
│   └── index.php           # Quiz interface with scoring
├── logs/                   # Application logs
├── index.php               # Application entry point
└── README.md              # This documentation
```

## 🏗️ Architecture Overview

### Technology Stack
- **Backend**: PHP 7.4+ with MySQLi
- **Frontend**: HTML5, CSS3, AngularJS 1.8
- **Database**: MySQL 5.7+ with UTF-8 support
- **Security**: bcrypt, CSRF tokens, prepared statements

### Design Patterns
- **MVC Architecture**: Separation of concerns with modular components
- **Progressive Enhancement**: Works without JavaScript, enhanced with it
- **Mobile-First**: Responsive design starting from mobile devices
- **Component-Based**: Reusable UI components and modular admin tabs

### Security Features
- **Password Security**: bcrypt hashing with secure random salts
- **Session Management**: Secure session handling with regeneration
- **Input Validation**: Server-side and client-side validation
- **SQL Injection Prevention**: Prepared statements throughout
- **CSRF Protection**: Token-based request validation
- **Rate Limiting**: Login attempt throttling and lockout
- **Activity Logging**: Comprehensive audit trail

## 🎯 Learning Flow

### For Learners
1. **Choose Language**: Select from available languages on the homepage
2. **Select Category**: Browse topics like greetings, food, travel
3. **Study Flashcards**: Interactive cards with images and pronunciations
4. **Track Progress**: Monitor completion and earn XP points
5. **Take Quizzes**: Test knowledge when lessons are completed
6. **Maintain Streaks**: Build daily learning habits

### For Administrators
1. **Monitor Dashboard**: View real-time statistics and user activity
2. **Manage Users**: Add, edit, and manage user accounts
3. **Create Content**: Add new languages, categories, and flashcards
4. **Build Quizzes**: Create engaging quiz questions with testing
5. **Configure System**: Adjust settings and maintain the platform

## 🔧 Customization

### Adding New Languages

1. **Admin Panel Method** (Recommended):
   - Login as admin and go to Languages tab
   - Click "Add New Language" and fill in details
   - Add categories and flashcards through the interface

2. **Direct Database Method**:
   ```sql
   INSERT INTO languages (name, code, flag_url, display_order) 
   VALUES ('Italian', 'it', 'flag_url_here', 4);
   ```

### Customizing Themes
- Edit `assets/css/style.css` for visual customization
- CSS variables at the top control the color scheme
- Responsive breakpoints can be adjusted in media queries

### Adding New Features
- Create new admin tabs in `dashboard/admin-tabs/`
- Add corresponding routes in `dashboard/admin.php`
- Extend the database schema as needed

## 📊 Database Schema

### Core Tables
- **users**: User accounts with authentication and progress data
- **languages**: Supported languages with metadata
- **categories**: Topic-based organization of content
- **flashcards**: Learning content with images and difficulty levels
- **quiz_questions**: Multiple-choice questions linked to flashcards
- **progress**: User learning progress and completion tracking

### Security Tables
- **login_attempts**: Rate limiting and security monitoring
- **activity_logs**: Comprehensive user action tracking
- **user_sessions**: Advanced session management
- **user_streaks**: Detailed streak and XP tracking

## 🚀 Production Deployment

### Server Requirements
- PHP 7.4+ with MySQLi, JSON, and session extensions
- MySQL 5.7+ or MariaDB 10.2+
- Apache 2.4+ or Nginx 1.18+ with URL rewriting
- SSL certificate for HTTPS (recommended)
- Minimum 512MB RAM, 1GB+ recommended

### Security Checklist
- [ ] Change default database credentials
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set restrictive file permissions (644 for files, 755 for directories)
- [ ] Configure web server security headers
- [ ] Set up regular database backups
- [ ] Monitor logs directory for size and security events
- [ ] Update default admin credentials
- [ ] Configure environment variables for sensitive data

### Performance Optimization
- Enable MySQL query caching
- Use a CDN for static assets
- Configure web server compression (gzip)
- Implement Redis/Memcached for session storage
- Optimize images and use WebP format when possible

## 🔍 Troubleshooting

### Common Issues

**Database Connection Errors:**
- Verify database credentials in `includes/config.php`
- Ensure MySQL service is running
- Check database user permissions

**Permission Denied Errors:**
- Set proper file permissions: `chmod 755 logs/`
- Ensure web server has write access to logs directory

**Session Issues:**
- Check session configuration in PHP
- Verify cookies are enabled in browser
- Clear browser cache and cookies

**CSS/JS Not Loading:**
- Check file paths in templates
- Verify web server can serve static files
- Clear browser cache

### Debug Mode
Enable debug mode by setting `APP_ENV` to 'development' in `includes/config.php`:
```php
define('APP_ENV', 'development');
```

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make changes and test thoroughly
4. Commit with descriptive messages
5. Push and create a pull request

### Code Standards
- Follow PSR-12 PHP coding standards
- Use meaningful variable and function names
- Comment complex logic and security-sensitive code
- Test all database operations with prepared statements
- Ensure responsive design works on all devices

### Feature Requests
- Open an issue with detailed description
- Include use cases and expected behavior
- Consider backward compatibility
- Discuss implementation approach before coding

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Design Inspiration**: Duolingo for UI/UX patterns
- **Icons**: Various open-source icon libraries
- **Testing**: Community feedback and contributions
- **Frameworks**: AngularJS team for the frontend framework

## 📞 Support

For support, feature requests, or bug reports:
- Create an issue on GitHub
- Email: support@perfecto-lang.com
- Documentation: [Wiki Pages](../../wiki)

---

**Made with ❤️ for language learners worldwide**

*Last updated: 2024 - Version 1.0.0*