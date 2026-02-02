<?php 
// Ensure helpers are loaded
if (file_exists(__DIR__ . '/../../helpers.php')) {
    require_once __DIR__ . '/../../helpers.php';
}

// Safe fallback functions if helpers fail
if (!function_exists('route')) {
    function route($path) { return "/" . ltrim($path, '/'); }
}
if (!function_exists('asset')) {
    function asset($path) { return "/" . ltrim($path, '/'); }
}
if (!function_exists('is_logged_in')) {
    function is_logged_in() { return isset($_SESSION['user']); }
}
if (!function_exists('current_user')) {
    function current_user() { return $_SESSION['user'] ?? null; }
}
?>

<header class="site-header">
  <div class="logo">
    <a href="<?= route('home') ?>">
      <img src="<?= asset('assets/images/pcc-logo.png') ?>" alt="PCC Logo">
    </a>
    <div class="logo-text">
      <span class="college-name">ResearchHub</span>
      <span class="thesis-flow">Academic Research Portal</span>
    </div>
  </div>

  <div class="header-right">
    <nav class="navbar">
      <!-- Home -->
      <a href="<?= route('home') ?>" class="nav-link <?= ($_GET['route'] ?? 'home') === 'home' ? 'active' : '' ?>">
        Home
      </a>
      <span class="separator">|</span>

      <!-- About -->
      <a href="<?= route('about') ?>" class="nav-link <?= ($_GET['route'] ?? '') === 'about' ? 'active' : '' ?>">
        About
      </a>
      <span class="separator">|</span>

      <!-- Browse Papers -->
      <a href="<?= route('research') ?>" class="nav-link <?= ($_GET['route'] ?? '') === 'research' ? 'active' : '' ?>">
        Browse Papers
      </a>
      <span class="separator">|</span>

      <!-- Upload Thesis -->
      <?php if (is_logged_in()): ?>
        <a href="<?= route('thesis/create') ?>" class="nav-link <?= ($_GET['route'] ?? '') === 'thesis/create' ? 'active' : '' ?>">
          Upload Thesis
        </a>
      <?php else: ?>
        <a href="<?= route('auth/select') ?>" class="nav-link">
          Upload Thesis
        </a>
      <?php endif; ?>
      <span class="separator">|</span>

      <!-- Theme Toggle -->
      <button class="theme-toggle" onclick="toggleTheme()" aria-label="Toggle dark mode">
        <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        <span id="themeText">Dark</span>
      </button>

      <!-- Role-specific Navigation -->
      <?php if (is_logged_in()): ?>
        <?php $user = current_user(); ?>
        
        <!-- Faculty/Admin Dashboard -->
        <?php if ($user && in_array($user['role'] ?? '', ['faculty', 'admin', 'librarian'])): ?>
          <span class="separator">|</span>
          <a href="<?= route('admin/dashboard') ?>" class="nav-link <?= ($_GET['route'] ?? '') === 'admin/dashboard' ? 'active' : '' ?>">
            Dashboard
          </a>
          <span class="separator">|</span>
          <a href="<?= route('admin/analytics/research') ?>" class="nav-link <?= ($_GET['route'] ?? '') === 'admin/analytics/research' ? 'active' : '' ?>">
            Research Analytics
          </a>
        <?php endif; ?>

        <!-- User Menu Dropdown -->
        <span class="separator">|</span>
        <div class="nav-dropdown user-dropdown">
          <a href="#" class="dropdown-trigger nav-link">
            <span class="user-icon"></span>
            <?= $user ? htmlspecialchars(explode(' ', $user['name'])[0]) : 'User' ?>
            <span class="dropdown-arrow">▼</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
            <?php if ($user): ?>
              <div class="dropdown-header">
                <div class="user-info">
                  <strong><?= htmlspecialchars($user['name']) ?></strong>
                  <small><?= htmlspecialchars(ucfirst($user['role'])) ?></small>
                  <small class="user-email"><?= htmlspecialchars($user['email']) ?></small>
                </div>
              </div>
              <hr class="dropdown-divider">
              
              <?php if ($user['role'] === 'student'): ?>
                <a href="<?= route('thesis/list') ?>" class="dropdown-item">
                  <span class="dropdown-icon"></span>
                  My Submissions
                </a>
                <a href="<?= route('thesis/create') ?>" class="dropdown-item">
                  <span class="dropdown-icon"></span>
                  New Submission
                </a>
              <?php endif; ?>
              
              <a href="<?= route('profile') ?>" class="dropdown-item">
                <span class="dropdown-icon">️</span>
                Profile Settings
              </a>
              
              <?php if (in_array($user['role'], ['admin', 'faculty', 'librarian'])): ?>
                <a href="<?= route('admin/users') ?>" class="dropdown-item">
                  <span class="dropdown-icon"></span>
                  Manage Users
                </a>
                <a href="<?= route('admin/analytics') ?>" class="dropdown-item">
                  <span class="dropdown-icon"></span>
                  System Analytics
                </a>
                <a href="<?= route('admin/analytics/research') ?>" class="dropdown-item">
                  <span class="dropdown-icon"></span>
                  Research Analytics
                </a>
                <a href="<?= route('admin/reports') ?>" class="dropdown-item">
                  <span class="dropdown-icon"></span>
                  Reports
                </a>
                <a href="<?= route('admin/combined-report') ?>" class="dropdown-item" style="color: #4caf50; font-weight: 600;">
                  <span class="dropdown-icon">📊</span>
                  All Users Activity (SOP Data)
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                  <hr class="dropdown-divider">
                  <a href="/setup_activity_tracking.php" class="dropdown-item" style="color: #2196f3; font-weight: 600;">
                    <span class="dropdown-icon">⚙️</span>
                    Setup Activity Tracking
                  </a>
                  <a href="/clear_all_data.php" class="dropdown-item" style="color: #dc3545; font-weight: 600;">
                    <span class="dropdown-icon">🗑️</span>
                    Clear All Data
                  </a>
                <?php endif; ?>
              <?php endif; ?>
              
              <hr class="dropdown-divider">
              <a href="<?= route('auth/logout') ?>" class="dropdown-item logout-item">
                <span class="dropdown-icon"></span>
                Logout
              </a>
            <?php endif; ?>
          </div>
        </div>

      <?php else: ?>
        <!-- Guest Navigation -->
        <span class="separator">|</span>
        <a href="<?= route('auth/select') ?>" class="nav-link login-btn">
          Login
        </a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Mobile Menu Toggle -->
  <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
    <span></span>
    <span></span>
    <span></span>
  </div>
</header>

<!-- Mobile Navigation Overlay -->
<div class="mobile-nav-overlay" id="mobileNavOverlay">
  <div class="mobile-nav">
    <div class="mobile-nav-header">
      <span>Menu</span>
      <button onclick="toggleMobileMenu()" class="mobile-nav-close"></button>
    </div>
    
    <div class="mobile-nav-content">
      <a href="<?= route('home') ?>" class="mobile-nav-item">Home</a>
      <a href="<?= route('about') ?>" class="mobile-nav-item">About</a>

      <!-- Browse Papers -->
      <a href="<?= route('research') ?>" class="mobile-nav-item">Browse Papers</a>

      <!-- Theme Toggle (Mobile) -->
      <a href="javascript:void(0)" onclick="toggleTheme()" class="mobile-nav-item" style="display: flex; align-items: center; justify-content: space-between;">
        <span><i class="bi bi-moon-stars-fill" id="mobileThemeIcon"></i> Toggle Theme</span>
        <span id="mobileThemeText" style="font-size: 12px; opacity: 0.7;">Dark</span>
      </a>

      <!-- Upload Thesis -->
      <?php if (is_logged_in()): ?>
        <a href="<?= route('thesis/create') ?>" class="mobile-nav-item">Upload Thesis</a>
      <?php else: ?>
        <a href="<?= route('auth/select') ?>" class="mobile-nav-item">Upload Thesis</a>
      <?php endif; ?>

      <?php if (is_logged_in()): ?>
        <?php $user = current_user(); ?>
        <?php if ($user): ?>
          <div class="mobile-nav-section">
            <div class="mobile-nav-user">
              <strong><?= htmlspecialchars($user['name']) ?></strong>
              <small><?= htmlspecialchars(ucfirst($user['role'])) ?></small>
            </div>
          </div>
          
          <?php if ($user['role'] === 'student'): ?>
            <a href="<?= route('thesis/create') ?>" class="mobile-nav-item">Upload Thesis</a>
            <a href="<?= route('thesis/list') ?>" class="mobile-nav-item">My Submissions</a>
          <?php endif; ?>
          
          <?php if (in_array($user['role'], ['faculty', 'admin', 'librarian'])): ?>
            <a href="<?= route('admin/dashboard') ?>" class="mobile-nav-item">Dashboard</a>
            <a href="<?= route('admin/analytics/research') ?>" class="mobile-nav-item">Research Analytics</a>
          <?php endif; ?>
          
          <a href="<?= route('auth/logout') ?>" class="mobile-nav-item logout">Logout</a>
        <?php endif; ?>
        
      <?php else: ?>
        <a href="<?= route('auth/select') ?>" class="mobile-nav-item">Login</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function toggleMobileMenu() {
  const overlay = document.getElementById('mobileNavOverlay');
  if (overlay) {
    overlay.classList.toggle('active');
  }
}

// Theme Toggle Functionality
function toggleTheme() {
  const body = document.body;
  const themeIcon = document.getElementById('themeIcon');
  const themeText = document.getElementById('themeText');
  const mobileThemeIcon = document.getElementById('mobileThemeIcon');
  const mobileThemeText = document.getElementById('mobileThemeText');

  // Toggle dark theme class
  body.classList.toggle('dark-theme');

  // Get current theme
  const isDark = body.classList.contains('dark-theme');

  // Update icons and text
  if (isDark) {
    if (themeIcon) {
      themeIcon.className = 'bi bi-sun-fill';
    }
    if (themeText) {
      themeText.textContent = 'Light';
    }
    if (mobileThemeIcon) {
      mobileThemeIcon.className = 'bi bi-sun-fill';
    }
    if (mobileThemeText) {
      mobileThemeText.textContent = 'Light';
    }
    // Save preference to localStorage
    localStorage.setItem('theme', 'dark');
  } else {
    if (themeIcon) {
      themeIcon.className = 'bi bi-moon-stars-fill';
    }
    if (themeText) {
      themeText.textContent = 'Dark';
    }
    if (mobileThemeIcon) {
      mobileThemeIcon.className = 'bi bi-moon-stars-fill';
    }
    if (mobileThemeText) {
      mobileThemeText.textContent = 'Dark';
    }
    // Save preference to localStorage
    localStorage.setItem('theme', 'light');
  }
}

// Load saved theme preference on page load
document.addEventListener('DOMContentLoaded', function() {
  const savedTheme = localStorage.getItem('theme');
  const body = document.body;
  const themeIcon = document.getElementById('themeIcon');
  const themeText = document.getElementById('themeText');
  const mobileThemeIcon = document.getElementById('mobileThemeIcon');
  const mobileThemeText = document.getElementById('mobileThemeText');

  if (savedTheme === 'dark') {
    body.classList.add('dark-theme');
    if (themeIcon) {
      themeIcon.className = 'bi bi-sun-fill';
    }
    if (themeText) {
      themeText.textContent = 'Light';
    }
    if (mobileThemeIcon) {
      mobileThemeIcon.className = 'bi bi-sun-fill';
    }
    if (mobileThemeText) {
      mobileThemeText.textContent = 'Light';
    }
  }
});
</script>