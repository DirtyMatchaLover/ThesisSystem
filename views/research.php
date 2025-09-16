<?php
// Initialize data variables with defaults to prevent undefined errors
$theses = $theses ?? [];
$totalTheses = $totalTheses ?? 0;
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$searchQuery = $searchQuery ?? '';
$filterType = $filterType ?? '';
$category = $category ?? '';
$year = $year ?? '';
$author = $author ?? '';
$filterOptions = $filterOptions ?? ['years' => [], 'authors' => [], 'categories' => []];
$hasFilters = $hasFilters ?? false;
?>

<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/navigation.php'; ?>

<div class="main-container">
    <div class="home-container research-page">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Research Papers</h1>
            <p class="page-subtitle">
                Discover and explore academic research from Pasig Catholic College
                <?php if ($totalTheses > 0): ?>
                    <span class="results-summary">(<?= number_format($totalTheses) ?> papers available)</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Advanced Search Section -->
        <div class="advanced-search-section">
            <form method="GET" action="<?= route('research') ?>" class="search-form">
                <!-- Main Search Bar -->
                <div class="main-search-bar">
                    <input 
                        type="text" 
                        name="search"
                        value="<?= htmlspecialchars($searchQuery) ?>"
                        placeholder="Search thesis papers, authors, topics..." 
                        class="search-input-large"
                    >
                    <button type="submit" class="search-btn-large">
                        <span class="search-icon-large">🔍</span>
                        Search
                    </button>
                </div>
                
                <!-- Advanced Filters Toggle -->
                <div class="filters-toggle">
                    <button type="button" class="toggle-filters-btn" onclick="toggleFilters()" <?= $hasFilters ? 'data-active="true"' : '' ?>>
                        <span class="filter-icon">⚙️</span>
                        Advanced Filters
                        <span class="toggle-arrow"><?= $hasFilters ? '▲' : '▼' ?></span>
                    </button>
                    <?php if ($hasFilters): ?>
                        <a href="<?= route('research') ?>" class="clear-all-filters">Clear All</a>
                    <?php endif; ?>
                </div>

                <!-- Advanced Filters Panel -->
                <div class="advanced-filters" id="advancedFilters" style="<?= $hasFilters ? 'display: block;' : 'display: none;' ?>">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label>Sort By</label>
                            <select name="filter" class="filter-select">
                                <option value="">Default Order</option>
                                <option value="recent" <?= $filterType === 'recent' ? 'selected' : '' ?>>Most Recent</option>
                                <option value="popular" <?= $filterType === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                                <option value="alphabetical" <?= $filterType === 'alphabetical' ? 'selected' : '' ?>>Alphabetical</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Publication Year</label>
                            <select name="year" class="filter-select">
                                <option value="">Any Year</option>
                                <?php foreach (array_reverse($filterOptions['years']) as $yearOption): ?>
                                    <option value="<?= $yearOption ?>" <?= $year === $yearOption ? 'selected' : '' ?>>
                                        <?= $yearOption ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Author</label>
                            <select name="author" class="filter-select">
                                <option value="">All Authors</option>
                                <?php foreach ($filterOptions['authors'] as $authorOption): ?>
                                    <option value="<?= htmlspecialchars($authorOption) ?>" <?= $author === $authorOption ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($authorOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (!empty($filterOptions['categories'])): ?>
                            <div class="filter-group">
                                <label>Subject Area</label>
                                <select name="category" class="filter-select">
                                    <option value="">All Subjects</option>
                                    <?php foreach ($filterOptions['categories'] as $categoryOption): ?>
                                        <option value="<?= htmlspecialchars($categoryOption) ?>" <?= $category === $categoryOption ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($categoryOption) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="filters-actions">
                        <button type="submit" class="apply-filters-btn">Apply Filters</button>
                        <a href="<?= route('research') ?>" class="clear-filters-btn">Clear All</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Section -->
        <section class="research-results">
            <?php if (!empty($theses)): ?>
                <div class="results-header">
                    <h3 class="results-title">
                        <?php if ($hasFilters): ?>
                            Search Results
                        <?php else: ?>
                            Browse Research Papers
                        <?php endif; ?>
                    </h3>
                    <div class="results-count">
                        <span class="count-text">
                            Showing <?= count($theses) ?> of <?= number_format($totalTheses) ?> papers
                        </span>
                        <div class="view-options">
                            <button class="view-btn active" data-view="grid" title="Grid View">⊞</button>
                            <button class="view-btn" data-view="list" title="List View">☰</button>
                        </div>
                    </div>
                </div>
                
                <div class="thesis-grid" id="resultsGrid">
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
                                    <?= htmlspecialchars(str_limit($thesis['title'] ?? 'Untitled', 80)) ?>
                                </h4>
                                
                                <div class="thesis-meta">
                                    <span class="thesis-author">
                                        <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown Author') ?>
                                    </span>
                                    <span class="thesis-date">
                                        <?= format_date($thesis['created_at'] ?? null, 'Y') ?> 
                                        <?php if (!empty($thesis['strand'])): ?>
                                            • <?= htmlspecialchars($thesis['strand']) ?>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <?php if (!empty($thesis['abstract'])): ?>
                                    <div class="thesis-abstract">
                                        <?= htmlspecialchars(str_limit($thesis['abstract'], 120)) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Visual lines for design -->
                                <div class="thesis-lines">
                                    <?php 
                                    $patterns = [
                                        ['long', 'medium', 'long', 'short'],
                                        ['medium', 'long', 'short', 'long'],
                                        ['long', 'short', 'medium', 'long'],
                                        ['short', 'long', 'medium', 'short']
                                    ];
                                    $pattern = $patterns[$index % 4];
                                    ?>
                                    <?php foreach ($pattern as $lineType): ?>
                                        <div class="thesis-line <?= $lineType ?>"></div>
                                    <?php endforeach; ?>
                                </div>
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
                            Page <?= $currentPage ?> of <?= $totalPages ?>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h3>No Papers Found</h3>
                    <?php if ($hasFilters): ?>
                        <p>
                            No research papers match your search criteria.<br>
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

<style>
/* Research Page Enhanced Styles */

.results-summary {
    font-size: 0.9em;
    color: #666;
    font-weight: normal;
}

.clear-all-filters {
    color: #d32f2f;
    text-decoration: none;
    font-size: 14px;
    margin-left: 15px;
    padding: 5px 10px;
    border: 1px solid #d32f2f;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.clear-all-filters:hover {
    background: #d32f2f;
    color: white;
    text-decoration: none;
}

/* Enhanced Research Cards */
.thesis-stats {
    position: absolute;
    bottom: 10px;
    left: 10px;
    display: flex;
    gap: 10px;
}

.view-count {
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
}

.thesis-meta {
    margin: 8px 0;
    font-size: 12px;
    color: #666;
}

.thesis-author {
    font-weight: 600;
    color: #d32f2f;
}

.thesis-date {
    color: #999;
}

/* Pagination Styles */
.pagination-container {
    margin-top: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 15px;
}

.pagination {
    display: flex;
    gap: 5px;
    align-items: center;
}

.page-link {
    padding: 8px 12px;
    text-decoration: none;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.page-link:hover {
    background: #f5f5f5;
    text-decoration: none;
    color: #333;
}

.page-link.active {
    background: #d32f2f;
    color: white;
    border-color: #d32f2f;
}

.pagination-info {
    font-size: 14px;
    color: #666;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-top: 20px;
}

.empty-state .empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 15px;
}

.empty-state p {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 25px;
}

/* Enhanced List View */
.thesis-grid.list-view .research-card {
    display: flex;
    flex-direction: row;
    max-height: 140px;
    align-items: stretch;
}

.thesis-grid.list-view .thesis-image {
    width: 100px;
    height: 100px;
    flex-shrink: 0;
    margin-right: 20px;
}

.thesis-grid.list-view .thesis-content {
    flex: 1;
    justify-content: flex-start;
}

.thesis-grid.list-view .thesis-lines {
    display: none;
}

.thesis-grid.list-view .play-button {
    position: relative;
    bottom: auto;
    right: auto;
    margin-left: 15px;
    align-self: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .research-page {
        padding: 20px;
    }
    
    .results-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .pagination {
        flex-wrap: wrap;
        gap: 3px;
    }
    
    .page-link {
        padding: 6px 10px;
        font-size: 13px;
    }
    
    /* Force grid view on mobile */
    .thesis-grid.list-view {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
    }
    
    .thesis-grid.list-view .research-card {
        display: flex !important;
        flex-direction: column !important;
        max-height: none !important;
    }
    
    .thesis-grid.list-view .thesis-image {
        width: 100% !important;
        height: 80px !important;
        margin-right: 0 !important;
        margin-bottom: 15px !important;
    }
    
    .thesis-grid.list-view .thesis-lines {
        display: flex !important;
    }
    
    .thesis-grid.list-view .play-button {
        position: absolute !important;
        bottom: 15px !important;
        right: 15px !important;
        margin-left: 0 !important;
    }
}
</style>

<script>
function toggleFilters() {
    const filtersPanel = document.getElementById('advancedFilters');
    const toggleArrow = document.querySelector('.toggle-arrow');
    const toggleBtn = document.querySelector('.toggle-filters-btn');
    
    if (filtersPanel.style.display === 'none' || filtersPanel.style.display === '') {
        filtersPanel.style.display = 'block';
        toggleArrow.innerHTML = '▲';
        toggleBtn.setAttribute('data-active', 'true');
    } else {
        filtersPanel.style.display = 'none';
        toggleArrow.innerHTML = '▼';
        toggleBtn.removeAttribute('data-active');
    }
}

// View toggle functionality
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        const view = this.dataset.view;
        const grid = document.getElementById('resultsGrid');
        
        if (view === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }
    });
});

// Auto-submit form when filters change
document.querySelectorAll('.filter-select').forEach(select => {
    select.addEventListener('change', function() {
        // Auto-submit on desktop, manual on mobile
        if (window.innerWidth > 768) {
            this.closest('form').submit();
        }
    });
});
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>