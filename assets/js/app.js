// Main Application JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (mobileNavToggle && mainNav) {
        mobileNavToggle.addEventListener('click', function() {
            mainNav.style.display = mainNav.style.display === 'flex' ? 'none' : 'flex';
        });
    }
    
    // Flash message auto-dismiss
    const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(function() {
            flashMessage.style.opacity = '0';
            setTimeout(function() {
                flashMessage.style.display = 'none';
            }, 600);
        }, 5000);
    }
});

// Angular module and controllers
var perfectoApp = angular.module('perfectoApp', []);

// Home Controller
perfectoApp.controller('HomeController', function($scope, $window) {
    // Initialize variables
    $scope.isLoggedIn = false; // This will be overridden by PHP inline script
    $scope.selectedLanguage = '';
    $scope.languages = [];
    
    // Load languages data from PHP
    $scope.loadLanguages = function(languagesData) {
        $scope.languages = languagesData;
    };
    
    // Select language and redirect to learn page
    $scope.selectLanguage = function(languageCode) {
        $scope.selectedLanguage = languageCode;
        $window.location.href = 'learn/index.php?lang=' + languageCode;
    };
});

// Dashboard Controller
perfectoApp.controller('DashboardController', function($scope) {
    // Dashboard functionality if needed
});

// Admin Controller
perfectoApp.controller('AdminController', function($scope) {
    // Admin functionality if needed
});

// Learn Controller
perfectoApp.controller('LearnController', function($scope) {
    $scope.currentIndex = 0;
    $scope.flashcards = [];
    
    // Initialize flashcards from PHP data
    $scope.initFlashcards = function(flashcardsData) {
        $scope.flashcards = flashcardsData;
        
        // Initialize flashcard properties
        angular.forEach($scope.flashcards, function(flashcard) {
            flashcard.flipped = false;
            flashcard.completed = false; // This will be overridden by PHP if needed
        });
    };
    
    // Flip card function
    $scope.flipCard = function(flashcard) {
        flashcard.flipped = !flashcard.flipped;
    };
    
    // Navigation functions
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

// Quiz Controller
perfectoApp.controller('QuizController', function($scope) {
    // Quiz functionality if needed
});