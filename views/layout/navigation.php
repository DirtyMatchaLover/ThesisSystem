<?php require_once __DIR__ . '/../../helpers.php'; ?>

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
            <?php if (current_user()['role'] === 'student'): ?>
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
        <?php if (in_array($user['role'], ['faculty', 'admin', 'librarian'])): ?>
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
            <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
            <span class="dropdown-arrow">▼</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right">
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
        
      <?php else: ?>
        <a href="<?= route('auth/select') ?>" class="mobile-nav-item">Login</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
// Search functionality
function performSearch() {
    const query = document.getElementById('globalSearch').value.trim();
    if (query.length > 0) {
        window.location.href = '<?= route('research') ?>?search=' + encodeURIComponent(query);
    }
}

// Live search results
document.getElementById('globalSearch')?.addEventListener('input', function() {
    const query = this.value.trim();
    const resultsDiv = document.getElementById('searchResults');
    
    if (query.length >= 2) {
        resultsDiv.style.display = 'block';
        resultsDiv.innerHTML = '<div class="search-loading">Searching...</div>';
        
        // Debounced search - wait 300ms after user stops typing
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            fetch('<?= route('api/search') ?>?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        let html = '';
                        data.results.slice(0, 5).forEach(item => {
                            html += `<a href="${item.url}" class="search-result-item">
                                <div class="search-result-title">${item.title}</div>
                                <div class="search-result-meta">${item.author} • ${item.year}</div>
                            </a>`;
                        });
                        if (data.results.length > 5) {
                            html += `<a href="<?= route('research') ?>?search=${encodeURIComponent(query)}" class="search-show-all">
                                Show all ${data.results.length} results
                            </a>`;
                        }
                        resultsDiv.innerHTML = html;
                    } else {
                        resultsDiv.innerHTML = '<div class="search-no-results">No results found</div>';
                    }
                })
                .catch(() => {
                    resultsDiv.innerHTML = '<div class="search-error">Search temporarily unavailable</div>';
                });
        }, 300);
    } else {
        resultsDiv.style.display = 'none';
    }
});

// Hide search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        document.getElementById('searchResults').style.display = 'none';
    }
});

// Voice search (basic implementation)
function startVoiceSearch() {
    if ('webkitSpeechRecognition' in window) {
        const recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;
        recognition.lang = 'en-US';
        
        recognition.onresult = function(event) {
            document.getElementById('globalSearch').value = event.results[0][0].transcript;
            performSearch();
        };
        
        recognition.start();
    } else {
        alert('Voice search not supported in this browser');
    }
}

// Mobile menu toggle
function toggleMobileMenu() {
    const overlay = document.getElementById('mobileNavOverlay');
    const isOpen = overlay.classList.contains('active');
    
    if (isOpen) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    } else {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Enhanced keyboard navigation
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for quick search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('globalSearch')?.focus();
    }
    
    // Escape to close search results
    if (e.key === 'Escape') {
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('globalSearch')?.blur();
    }
});

// Enter key to search
document.getElementById('globalSearch')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        performSearch();
    }
});
</script>