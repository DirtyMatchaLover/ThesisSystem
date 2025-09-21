<?php
// Advanced Research Search Page (No JavaScript Required)
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers.php';

// Get search parameters
$searchQuery = trim($_GET['search'] ?? '');
$title = trim($_GET['title'] ?? '');
$author = trim($_GET['author'] ?? '');
$adviser = trim($_GET['adviser'] ?? '');
$department = $_GET['department'] ?? '';
$strand = $_GET['strand'] ?? '';
$academic_year = $_GET['academic_year'] ?? '';
$keywords = trim($_GET['keywords'] ?? '');
$abstract_contains = trim($_GET['abstract'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$min_views = $_GET['min_views'] ?? '';
$max_views = $_GET['max_views'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'newest';
$results_per_page = (int)($_GET['per_page'] ?? 12);

// Pagination
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset = ($currentPage - 1) * $results_per_page;

// Check if any filters are applied
$hasFilters = !empty($searchQuery) || !empty($title) || !empty($author) || !empty($adviser) || 
              !empty($department) || !empty($strand) || !empty($academic_year) || !empty($keywords) ||
              !empty($abstract_contains) || !empty($date_from) || !empty($date_to) || 
              !empty($min_views) || !empty($max_views);

// Build advanced search query
try {
    $db = Database::getInstance();
    
    // Base query with LEFT JOINs for comprehensive search
    $sql = "SELECT DISTINCT t.*, 
                   u.name as author_name, 
                   u.strand as author_strand,
                   u.department as author_department,
                   DATE_FORMAT(t.created_at, '%M %d, %Y') as formatted_date,
                   DATE_FORMAT(t.created_at, '%Y') as year_only
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.status = 'approved'";
    
    $params = [];
    
    // General search (searches across multiple fields)
    if (!empty($searchQuery)) {
        $sql .= " AND (
            t.title LIKE ? OR 
            t.abstract LIKE ? OR 
            u.name LIKE ? OR 
            t.adviser_name LIKE ? OR
            t.department LIKE ? OR
            u.strand LIKE ?
        )";
        $searchTerm = "%$searchQuery%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    // Specific title search
    if (!empty($title)) {
        $sql .= " AND t.title LIKE ?";
        $params[] = "%$title%";
    }
    
    // Specific author search
    if (!empty($author)) {
        $sql .= " AND (u.name LIKE ? OR t.author_name LIKE ?)";
        $authorTerm = "%$author%";
        $params[] = $authorTerm;
        $params[] = $authorTerm;
    }
    
    // Specific adviser search
    if (!empty($adviser)) {
        $sql .= " AND t.adviser_name LIKE ?";
        $params[] = "%$adviser%";
    }
    
    // Department filter
    if (!empty($department)) {
        $sql .= " AND (t.department = ? OR u.department = ?)";
        $params[] = $department;
        $params[] = $department;
    }
    
    // Strand filter
    if (!empty($strand)) {
        $sql .= " AND u.strand = ?";
        $params[] = $strand;
    }
    
    // Academic year filter
    if (!empty($academic_year)) {
        $sql .= " AND t.academic_year = ?";
        $params[] = $academic_year;
    }
    
    // Keywords search (searches in title and abstract)
    if (!empty($keywords)) {
        $keywordList = explode(',', $keywords);
        $keywordConditions = [];
        foreach ($keywordList as $keyword) {
            $keyword = trim($keyword);
            if (!empty($keyword)) {
                $keywordConditions[] = "(t.title LIKE ? OR t.abstract LIKE ?)";
                $keywordTerm = "%$keyword%";
                $params[] = $keywordTerm;
                $params[] = $keywordTerm;
            }
        }
        if (!empty($keywordConditions)) {
            $sql .= " AND (" . implode(' OR ', $keywordConditions) . ")";
        }
    }
    
    // Abstract contains specific text
    if (!empty($abstract_contains)) {
        $sql .= " AND t.abstract LIKE ?";
        $params[] = "%$abstract_contains%";
    }
    
    // Date range filters
    if (!empty($date_from)) {
        $sql .= " AND t.created_at >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $sql .= " AND t.created_at <= ?";
        $params[] = $date_to . ' 23:59:59';
    }
    
    // View count filters
    if (!empty($min_views)) {
        $sql .= " AND t.view_count >= ?";
        $params[] = (int)$min_views;
    }
    
    if (!empty($max_views)) {
        $sql .= " AND t.view_count <= ?";
        $params[] = (int)$max_views;
    }
    
    // Sorting
    switch ($sort_by) {
        case 'oldest':
            $sql .= " ORDER BY t.created_at ASC";
            break;
        case 'title_az':
            $sql .= " ORDER BY t.title ASC";
            break;
        case 'title_za':
            $sql .= " ORDER BY t.title DESC";
            break;
        case 'author_az':
            $sql .= " ORDER BY u.name ASC, t.author_name ASC";
            break;
        case 'most_viewed':
            $sql .= " ORDER BY t.view_count DESC";
            break;
        case 'least_viewed':
            $sql .= " ORDER BY t.view_count ASC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY t.created_at DESC";
            break;
    }
    
    // Count total results for pagination
    $countSql = str_replace(
        "SELECT DISTINCT t.*, u.name as author_name, u.strand as author_strand, u.department as author_department, DATE_FORMAT(t.created_at, '%M %d, %Y') as formatted_date, DATE_FORMAT(t.created_at, '%Y') as year_only",
        "SELECT COUNT(DISTINCT t.id)",
        $sql
    );
    $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
    
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalTheses = $countStmt->fetchColumn();
    $totalPages = ceil($totalTheses / $results_per_page);
    
    // Get paginated results
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $results_per_page;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $theses = $stmt->fetchAll();
    
    // Get filter options for dropdowns
    $departments = $db->query("SELECT DISTINCT department FROM theses WHERE status = 'approved' AND department IS NOT NULL ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);
    $strands = $db->query("SELECT DISTINCT u.strand FROM users u JOIN theses t ON u.id = t.user_id WHERE t.status = 'approved' AND u.strand IS NOT NULL ORDER BY u.strand")->fetchAll(PDO::FETCH_COLUMN);
    $years = $db->query("SELECT DISTINCT academic_year FROM theses WHERE status = 'approved' ORDER BY academic_year DESC")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (Exception $e) {
    error_log("Research search error: " . $e->getMessage());
    $theses = [];
    $totalTheses = 0;
    $totalPages = 1;
    $departments = [];
    $strands = [];
    $years = [];
}
?>

<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/navigation.php'; ?>

<div class="main-container">
    <div class="home-container research-page">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Advanced Research Search</h1>
            <p class="page-subtitle">
                Find specific research papers with powerful search filters
                <?php if ($totalTheses > 0): ?>
                    <span class="results-summary">(<?= number_format($totalTheses) ?> papers found)</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Advanced Search Section -->
        <div class="advanced-search-section">
            <form method="GET" action="<?= route('research') ?>" class="search-form">
                
                <!-- Quick Search -->
                <div class="search-section">
                    <h3 class="section-title">🔍 Quick Search</h3>
                    <div class="main-search-bar">
                        <input 
                            type="text" 
                            name="search"
                            value="<?= htmlspecialchars($searchQuery) ?>"
                            placeholder="Search everything (title, author, abstract, adviser...)" 
                            class="search-input-large"
                        >
                        <button type="submit" class="search-btn-large">
                            <span class="search-icon-large">🔍</span>
                            Search
                        </button>
                    </div>
                </div>

                <!-- Specific Field Search -->
                <div class="search-section">
                    <h3 class="section-title">🎯 Specific Field Search</h3>
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Title Contains:</label>
                            <input 
                                type="text" 
                                name="title" 
                                value="<?= htmlspecialchars($title) ?>"
                                placeholder="Search in thesis titles only"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Author Name:</label>
                            <input 
                                type="text" 
                                name="author" 
                                value="<?= htmlspecialchars($author) ?>"
                                placeholder="Student author name"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Thesis Adviser:</label>
                            <input 
                                type="text" 
                                name="adviser" 
                                value="<?= htmlspecialchars($adviser) ?>"
                                placeholder="Research adviser name"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Keywords (comma-separated):</label>
                            <input 
                                type="text" 
                                name="keywords" 
                                value="<?= htmlspecialchars($keywords) ?>"
                                placeholder="e.g. machine learning, AI, data analysis"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Abstract Contains:</label>
                            <input 
                                type="text" 
                                name="abstract" 
                                value="<?= htmlspecialchars($abstract_contains) ?>"
                                placeholder="Search inside thesis abstracts"
                                class="filter-input"
                            >
                        </div>
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="search-section">
                    <h3 class="section-title">📚 Category & Academic Filters</h3>
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Department:</label>
                            <select name="department" class="filter-select">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept) ?>" <?= $department === $dept ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Academic Strand:</label>
                            <select name="strand" class="filter-select">
                                <option value="">All Strands</option>
                                <?php foreach ($strands as $strandOption): ?>
                                    <option value="<?= htmlspecialchars($strandOption) ?>" <?= $strand === $strandOption ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($strandOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Academic Year:</label>
                            <select name="academic_year" class="filter-select">
                                <option value="">All Years</option>
                                <?php foreach ($years as $yearOption): ?>
                                    <option value="<?= htmlspecialchars($yearOption) ?>" <?= $academic_year === $yearOption ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($yearOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Date & Popularity Filters -->
                <div class="search-section">
                    <h3 class="section-title">📅 Date & Popularity Filters</h3>
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Published From:</label>
                            <input 
                                type="date" 
                                name="date_from" 
                                value="<?= htmlspecialchars($date_from) ?>"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Published To:</label>
                            <input 
                                type="date" 
                                name="date_to" 
                                value="<?= htmlspecialchars($date_to) ?>"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Minimum Views:</label>
                            <input 
                                type="number" 
                                name="min_views" 
                                value="<?= htmlspecialchars($min_views) ?>"
                                placeholder="e.g. 10"
                                min="0"
                                class="filter-input"
                            >
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Maximum Views:</label>
                            <input 
                                type="number" 
                                name="max_views" 
                                value="<?= htmlspecialchars($max_views) ?>"
                                placeholder="e.g. 1000"
                                min="0"
                                class="filter-input"
                            >
                        </div>
                    </div>
                </div>

                <!-- Sort & Results Options -->
                <div class="search-section">
                    <h3 class="section-title">⚙️ Display Options</h3>
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label class="filter-label">Sort Results By:</label>
                            <select name="sort_by" class="filter-select">
                                <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="oldest" <?= $sort_by === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                <option value="title_az" <?= $sort_by === 'title_az' ? 'selected' : '' ?>>Title A-Z</option>
                                <option value="title_za" <?= $sort_by === 'title_za' ? 'selected' : '' ?>>Title Z-A</option>
                                <option value="author_az" <?= $sort_by === 'author_az' ? 'selected' : '' ?>>Author A-Z</option>
                                <option value="most_viewed" <?= $sort_by === 'most_viewed' ? 'selected' : '' ?>>Most Viewed</option>
                                <option value="least_viewed" <?= $sort_by === 'least_viewed' ? 'selected' : '' ?>>Least Viewed</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Results Per Page:</label>
                            <select name="per_page" class="filter-select">
                                <option value="6" <?= $results_per_page === 6 ? 'selected' : '' ?>>6 papers</option>
                                <option value="12" <?= $results_per_page === 12 ? 'selected' : '' ?>>12 papers</option>
                                <option value="24" <?= $results_per_page === 24 ? 'selected' : '' ?>>24 papers</option>
                                <option value="48" <?= $results_per_page === 48 ? 'selected' : '' ?>>48 papers</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="search-actions">
                    <button type="submit" class="btn-search-advanced">
                        🔍 Search with Filters
                    </button>
                    <a href="<?= route('research') ?>" class="btn-clear-all">
                        🗑️ Clear All Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Search Results Section -->
        <section class="research-results">
            <?php if (!empty($theses)): ?>
                <div class="results-header">
                    <h3 class="results-title">
                        <?php if ($hasFilters): ?>
                            🔍 Advanced Search Results
                        <?php else: ?>
                            📚 All Research Papers
                        <?php endif; ?>
                    </h3>
                    <div class="results-count">
                        <span class="count-text">
                            Showing <?= count($theses) ?> of <?= number_format($totalTheses) ?> papers
                            <?php if ($totalPages > 1): ?>
                                (Page <?= $currentPage ?> of <?= $totalPages ?>)
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="thesis-grid">
                    <?php foreach ($theses as $index => $thesis): ?>
                        <div class="thesis-card research-card" onclick="location.href='<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?>'">
                            <div class="thesis-image">
                                <div class="thesis-type-badge">
                                    <?= file_icon($thesis['file_path'] ?? '') ?>
                                </div>
                                <?php if (!empty($thesis['view_count']) && $thesis['view_count'] > 0): ?>
                                    <div class="thesis-stats">
                                        <span class="view-count" title="Views">👁 <?= number_format($thesis['view_count']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="thesis-content">
                                <h4 class="thesis-title">
                                    <?= htmlspecialchars(str_limit($thesis['title'] ?? 'Untitled', 70)) ?>
                                </h4>
                                
                                <div class="thesis-meta">
                                    <div class="thesis-author">
                                        <strong>Author:</strong> <?= htmlspecialchars($thesis['author_name'] ?? $thesis['author'] ?? 'Unknown') ?>
                                    </div>
                                    <?php if (!empty($thesis['adviser_name'])): ?>
                                        <div class="thesis-adviser">
                                            <strong>Adviser:</strong> <?= htmlspecialchars($thesis['adviser_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="thesis-year">
                                        <strong>Year:</strong> <?= htmlspecialchars($thesis['academic_year'] ?? 'N/A') ?>
                                        <?php if (!empty($thesis['author_strand'])): ?>
                                            | <strong>Strand:</strong> <?= htmlspecialchars($thesis['author_strand']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="thesis-date">
                                        <strong>Published:</strong> <?= htmlspecialchars($thesis['formatted_date']) ?>
                                    </div>
                                </div>

                                <?php if (!empty($thesis['abstract'])): ?>
                                    <div class="thesis-abstract">
                                        <?= htmlspecialchars(str_limit($thesis['abstract'], 120)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <button class="play-button" aria-label="View research paper">
                                <span class="play-icon">👁</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="page-link">« First</a>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>" class="page-link">‹ Previous</a>
                            <?php endif; ?>

                            <?php 
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="page-link active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>" class="page-link">Next ›</a>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>" class="page-link">Last »</a>
                            <?php endif; ?>
                        </nav>
                        
                        <div class="pagination-info">
                            Page <?= $currentPage ?> of <?= $totalPages ?> • <?= number_format($totalTheses) ?> total results
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h3>No Research Papers Found</h3>
                    <?php if ($hasFilters): ?>
                        <p>
                            No papers match your search criteria.<br>
                            Try adjusting your filters or search terms.
                        </p>
                        <a href="<?= route('research') ?>" class="btn btn-primary">Clear All Filters</a>
                    <?php else: ?>
                        <p>
                            No research papers have been published yet.<br>
                            Be the first to contribute to the research repository!
                        </p>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= route('thesis/create') ?>" class="btn btn-primary">Submit Your Research</a>
                        <?php else: ?>
                            <a href="<?= route('auth/select') ?>" class="btn btn-primary">Get Started</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>