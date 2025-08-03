# perfecto Language Learning App

A simple language learning application that helps users learn new languages through flashcards and quizzes.

## Features

- Learn Spanish, French, and German with structured lessons
- Interactive flashcards with images and pronunciations
- Quizzes to test your knowledge
- Progress tracking and achievements
- User and admin dashboards
- Mobile-responsive design

## Installation

### Prerequisites

- XAMPP (or similar: WAMP, MAMP, etc.)
- PHP 7.4 or higher
- MySQL

### Setup Instructions

1. **Download and Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Follow the installation instructions for your operating system

2. **Clone or Download the Repository**
   - Place the files in the `htdocs` folder of your XAMPP installation
   - Usually located at `C:\xampp\htdocs` on Windows or `/Applications/XAMPP/htdocs` on macOS

3. **Create the Database**
   - Start the Apache and MySQL services from the XAMPP Control Panel
   - Open your browser and navigate to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database named `perfecto_db`
   - Click on the new database and select the "Import" tab
   - Import the `schema.sql` file to create the tables
   - Import the `seed.sql` file to populate the database with sample data

4. **Configure Database Connection**
   - Open `includes/config.php`
   - Update the database credentials if necessary:
     ```php
     $db_host = 'localhost';
     $db_name = 'perfecto_db';
     $db_user = 'root';
     $db_pass = '';
     ```

5. **Access the Application**
   - Navigate to [http://localhost/perfecto](http://localhost/perfecto) in your browser
   - The application should now be running

## Test Credentials

Use these pre-configured accounts to test the application:

- **Admin Account**
  - Email: admin@perfecto.com
  - Password: admin123

- **User Account**
  - Email: user@perfecto.com
  - Password: user123

## Project Structure

```
perfecto/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── images/
│   │   └── flags/
│   └── js/
│       └── app.js
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── dashboard/
│   ├── admin.php
│   └── user.php
├── includes/
│   ├── config.php
│   ├── footer.php
│   └── header.php
├── learn/
│   └── index.php
├── quiz/
│   └── index.php
├── schema.sql
├── seed.sql
├── index.php
└── README.md
```

## Architecture Overview

The perfecto language learning app follows a simple MVC-inspired architecture:

- **Model**: Database interactions are handled directly using PHP's mysqli extension
- **View**: PHP templates render the UI with minimal AngularJS for dynamic components
- **Controller**: PHP script logic in each page handles request processing and business logic

Key components:

1. **Authentication System**
   - Session-based authentication with simple login/register functionality
   - Role-based access control (admin/user)

2. **Learning System**
   - Flashcard-based lessons organized by language and category
   - Quiz system to test knowledge after completing lessons
   - Progress tracking to monitor user advancement

3. **Admin Panel**
   - User management interface
   - Content management for languages, categories, flashcards, and quizzes

## Future Roadmap

Planned languages and features for future updates:

1. **Additional Languages**
   - Italian
   - Portuguese
   - Japanese
   - Mandarin Chinese

2. **Feature Enhancements**
   - Audio pronunciation recordings
   - Speech recognition for speaking practice
   - Spaced repetition system for optimized learning
   - Community features (forums, language exchange)
   - Mobile app version

## Credits

- Design inspired by Duolingo
- Placeholder images provided by Placehold.co