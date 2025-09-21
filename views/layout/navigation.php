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
    <span>Pasig Catholic College</span>
  </div>

  <div class="header-right">
    <!-- Enhanced Search with Live Results -->
    <div class="search-container">
      <input 
        type="text" 
        id="globalSearch" 
        placeholder="Search theses, authors, topics..." 
        autocomplete="off"
      >
      <span class="search-icon" onclick="performSearch()">🔍</span>
      <span class="mic-icon" onclick="startVoiceSearch()">🎤</span>
      
      <!-- Search Results Dropdown -->
      <div class="search-results" id="searchResults" style="display: none;">
        <div class="search-loading">Searching...</div>
      </div>
    </div>

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
      
      <!-- Research Dropdown -->
      <div class="nav-dropdown">
        <a href="<?= route('research') ?>" class="dropdown-trigger nav-link <?= ($_GET['route'] ?? '') === 'research' ? 'active' : '' ?>">
          Research
          <span class="dropdown-arrow">▼</span>
        </a>
        <div class="dropdown-menu">
          <a href="<?= route('research') ?>" class="dropdown-item">
            <span class="dropdown-icon">🔍</span>
            Browse Papers
          </a>
          <a href="<?= route('research') ?>?filter=recent" class="dropdown-item">
            <span class="dropdown-icon">⭐</span>
            Latest Research
          </a>
          <a href="<?= route('research') ?>?filter=popular" class="dropdown-item">
            <span class="dropdown-icon">📈</span>
            Popular Papers
          </a>
          <?php if (is_logged_in()): ?>
            <hr class="dropdown-divider">
            <a href="<?= route('thesis/create') ?>" class="dropdown-item">
              <span class="dropdown-icon">📝</span>
              Upload Thesis
            </a>
            <?php if (current_user() && current_user()['role'] === 'student'): ?>
              <a href="<?= route('thesis/list') ?>" class="dropdown-item">
                <span class="dropdown-icon">📄</span>
                My Submissions
              </a>
            <?php endif; ?>
          <?php else: ?>
            <hr class="dropdown-divider">
            <a href="<?= route('auth/select') ?>" class="dropdown-item">
              <span class="dropdown-icon">📝</span>
              Upload Thesis
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Role-specific Navigation -->
      <?php if (is_logged_in()): ?>
        <?php $user = current_user(); ?>
        
        <!-- Faculty/Admin Dashboard -->
        <?php if ($user && in_array($user['role'] ?? '', ['faculty', 'admin', 'librarian'])): ?>
          <span class="separator">|</span>
          <a href="<?= route('admin/dashboard') ?>" class="nav-link <?= ($_GET['route'] ?? '') === 'admin/dashboard' ? 'active' : '' ?>">
            Dashboard
          </a>
        <?php endif; ?>

        <!-- User Menu Dropdown -->
        <span class="separator">|</span>
        <div class="nav-dropdown user-dropdown">
          <a href="#" class="dropdown-trigger nav-link">
            <span class="user-icon">👤</span>
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
                  <span class="dropdown-icon">📄</span>
                  My Submissions
                </a>
                <a href="<?= route('thesis/create') ?>" class="dropdown-item">
                  <span class="dropdown-icon">➕</span>
                  New Submission
                </a>
              <?php endif; ?>
              
              <a href="<?= route('profile') ?>" class="dropdown-item">
                <span class="dropdown-icon">⚙️</span>
                Profile Settings
              </a>
              
              <?php if (in_array($user['role'], ['admin', 'faculty'])): ?>
                <a href="<?= route('admin/users') ?>" class="dropdown-item">
                  <span class="dropdown-icon">👥</span>
                  Manage Users
                </a>
                <a href="<?= route('admin/reports') ?>" class="dropdown-item">
                  <span class="dropdown-icon">📊</span>
                  Reports
                </a>
              <?php endif; ?>
              
              <hr class="dropdown-divider">
              <a href="<?= route('auth/logout') ?>" class="dropdown-item logout-item">
                <span class="dropdown-icon">🚪</span>
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
      <button onclick="toggleMobileMenu()" class="mobile-nav-close">✕</button>
    </div>
    
    <div class="mobile-nav-content">
      <a href="<?= route('home') ?>" class="mobile-nav-item">Home</a>
      <a href="<?= route('research') ?>" class="mobile-nav-item">Research</a>
      
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

function performSearch() {
  const searchInput = document.getElementById('globalSearch');
  if (searchInput && searchInput.value.trim()) {
    window.location.href = '<?= route('research') ?>?search=' + encodeURIComponent(searchInput.value);
  }
}

function startVoiceSearch() {
  // Voice search functionality can be added here
  alert('Voice search not implemented yet');
}
</script>