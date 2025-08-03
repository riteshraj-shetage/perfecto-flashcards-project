<?php
session_start();
include_once 'includes/config.php';
include_once 'includes/header.php';

// Get available languages
$languages_sql = "SELECT * FROM languages ORDER BY display_order";
$languages_result = $conn->query($languages_sql);

$languages = [];
while ($row = $languages_result->fetch_assoc()) {
    $languages[] = $row;
}
?>

<main ng-app="perfectoApp" ng-controller="HomeController" ng-init="loadLanguages(<?php echo htmlspecialchars(json_encode($languages)); ?>)">
  <section class="hero">
    <div class="container">
      <h1>Welcome to <span class="brand">perfecto</span></h1>
      <p class="tagline">Learn a new language in a fun and effective way</p>
      
      <div class="language-cards">
        <div class="language-card" ng-repeat="language in languages" ng-click="selectLanguage(language.code)">
          <div class="card-inner">
            <img ng-src="{{language.flag_url}}" alt="{{language.name}} flag">
            <h3>{{language.name}}</h3>
            <p>{{language.phrase_count}} phrases</p>
            <button class="btn btn-primary">Start Learning</button>
          </div>
        </div>
      </div>
      
      <div class="coming-soon">
        <p>New languages coming soon!</p>
      </div>
      
      <div class="cta-buttons" ng-if="!isLoggedIn">
        <a href="<?php echo SITE_URL; ?>/auth/register.php" class="btn btn-primary">Sign Up Free</a>
        <a href="<?php echo SITE_URL; ?>/auth/login.php" class="btn btn-secondary">Login</a>
      </div>
      
      <div class="user-actions" ng-if="isLoggedIn">
        <a href="<?php echo SITE_URL; ?>/dashboard/user.php" class="btn btn-primary">My Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/learn/index.php" ng-if="selectedLanguage" class="btn btn-success">Continue Learning</a>
      </div>
    </div>
  </section>
  
  <section class="features">
    <div class="container">
      <h2>Why choose <span class="brand">perfecto</span>?</h2>
      <div class="feature-grid">
        <div class="feature">
          <i class="feature-icon lessons-icon"></i>
          <h3>Structured Lessons</h3>
          <p>Learn at your own pace with carefully designed lessons</p>
        </div>
        <div class="feature">
          <i class="feature-icon quiz-icon"></i>
          <h3>Interactive Quizzes</h3>
          <p>Test your knowledge with fun and challenging quizzes</p>
        </div>
        <div class="feature">
          <i class="feature-icon progress-icon"></i>
          <h3>Track Your Progress</h3>
          <p>See how far you've come and what's next on your journey</p>
        </div>
        <div class="feature">
          <i class="feature-icon achievement-icon"></i>
          <h3>Earn Achievements</h3>
          <p>Stay motivated with badges and completion rewards</p>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
var perfectoApp = angular.module('perfectoApp', []);

perfectoApp.controller('HomeController', function($scope, $window) {
    $scope.isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    $scope.selectedLanguage = '';
    
    $scope.loadLanguages = function(languagesData) {
        $scope.languages = languagesData;
    };
    
    $scope.selectLanguage = function(languageCode) {
        $scope.selectedLanguage = languageCode;
        $window.location.href = '<?php echo SITE_URL; ?>/learn/index.php?lang=' + languageCode;
    };
});
</script>

<?php include_once 'includes/footer.php'; ?>