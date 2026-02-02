<?php
// Initialize variables to prevent undefined variable warnings
$theses = $data['theses'] ?? [];
$totalTheses = $data['totalTheses'] ?? 0;
$currentPage = $data['currentPage'] ?? 1;
$totalPages = $data['totalPages'] ?? 1;
$searchQuery = $data['searchQuery'] ?? '';
$filterType = $data['filterType'] ?? '';
$category = $data['category'] ?? '';
$year = $data['year'] ?? '';
$author = $data['author'] ?? '';
$filterOptions = $data['filterOptions'] ?? ['years' => [], 'authors' => [], 'categories' => []];
$hasFilters = $data['hasFilters'] ?? false;

// Additional filters
$title = $_GET['title'] ?? '';
$abstract = $_GET['abstract'] ?? '';
$adviser = $_GET['adviser'] ?? '';
$department = $_GET['department'] ?? '';
$strand = $_GET['strand'] ?? '';

// Get unique values for dropdowns
$departments = ['Senior High School', 'College', 'Graduate School'];
$strands = ['STEM', 'HUMSS', 'ABM', 'TVL-HE', 'TVL-ICT', 'ADT'];
$years = range(2020, date('Y'));

// Handle potential errors
try {
    // Existing code should work, this is just error handling wrapper
} catch (Exception $e) {
    error_log("Research page error: " . $e->getMessage());
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
            <h1 class="page-title">Research Paper Search</h1>
            <p class="page-subtitle">
                Search and browse published research papers
                <?php if ($totalTheses > 0): ?>
                    <span class="results-summary">(<?= number_format($totalTheses) ?> papers found)</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Search Section -->
        <div class="search-section-container">
            <form method="GET" action="<?= route('research') ?>" class="search-form" id="searchForm">

                <!-- Basic Search -->
                <div class="search-box">
                    <h3 class="section-title">Search for Research Papers</h3>
                    <p class="help-text">Enter keywords to search across all papers (title, abstract, author, etc.)</p>
                    <div class="search-bar">
                        <input
                            type="text"
                            name="search"
                            id="searchInput"
                            value="<?= htmlspecialchars($searchQuery) ?>"
                            placeholder="Enter keywords (e.g., climate change, education, technology)"
                            class="search-input"
                        >
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                </div>

                <!-- Filter Options -->
                <div class="filters-section">
                    <div class="filters-header">
                        <h3 class="section-title">Refine Your Search</h3>
                        <button type="button" class="toggle-filters" onclick="toggleFilters()">
                            <span id="filterToggleText">Show Filters</span>
                        </button>
                    </div>

                    <div class="filters-container" id="filtersContainer" style="display: none;">

                        <!-- Search by Category -->
                        <div class="filter-section">
                            <h4 class="filter-heading">Filter by Category</h4>
                            <div class="filter-row">
                                <div class="filter-field">
                                    <label>Academic Strand</label>
                                    <select name="strand" class="form-select">
                                        <option value="">All Strands</option>
                                        <?php foreach ($strands as $strandOption): ?>
                                            <option value="<?= htmlspecialchars($strandOption) ?>"
                                                    <?= $strand === $strandOption ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($strandOption) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="field-hint">Select a specific program</small>
                                </div>

                                <div class="filter-field">
                                    <label>Year</label>
                                    <select name="year" class="form-select">
                                        <option value="">All Years</option>
                                        <?php foreach (array_reverse($years) as $yearOption): ?>
                                            <option value="<?= $yearOption ?>"
                                                    <?= $year == $yearOption ? 'selected' : '' ?>>
                                                <?= $yearOption ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="field-hint">Filter by publication year</small>
                                </div>
                            </div>
                        </div>

                        <!-- Search by Specific Fields -->
                        <div class="filter-section">
                            <h4 class="filter-heading">Search Specific Fields</h4>
                            <div class="filter-row">
                                <div class="filter-field">
                                    <label>Title</label>
                                    <input
                                        type="text"
                                        name="title"
                                        value="<?= htmlspecialchars($title) ?>"
                                        placeholder="Search within paper titles"
                                        class="form-input"
                                    >
                                    <small class="field-hint">Search only in research titles</small>
                                </div>

                                <div class="filter-field">
                                    <label>Author Name</label>
                                    <input
                                        type="text"
                                        name="author"
                                        value="<?= htmlspecialchars($author) ?>"
                                        placeholder="Enter student author name"
                                        class="form-input"
                                    >
                                    <small class="field-hint">Find papers by specific author</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sort Options -->
                        <div class="filter-section">
                            <h4 class="filter-heading">Sort Results</h4>
                            <div class="filter-row">
                                <div class="filter-field">
                                    <label>Order By</label>
                                    <select name="filter" class="form-select">
                                        <option value="">Most Relevant</option>
                                        <option value="recent" <?= $filterType === 'recent' ? 'selected' : '' ?>>Newest First</option>
                                        <option value="oldest" <?= $filterType === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                        <option value="alphabetical" <?= $filterType === 'alphabetical' ? 'selected' : '' ?>>Title A-Z</option>
                                        <option value="most_viewed" <?= $filterType === 'most_viewed' ? 'selected' : '' ?>>Most Popular</option>
                                    </select>
                                    <small class="field-hint">Change result ordering</small>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <?php if ($hasFilters): ?>
                                <a href="<?= route('research') ?>" class="btn btn-secondary">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <?php if (!empty($theses)): ?>
                <!-- Results Header -->
                <div class="results-header">
                    <div class="results-info">
                        <strong><?= number_format($totalTheses) ?></strong> research paper<?= $totalTheses != 1 ? 's' : '' ?> found
                        <?php if ($totalPages > 1): ?>
                            <span class="page-info">| Page <?= $currentPage ?> of <?= $totalPages ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Research Cards Grid -->
                <div class="research-grid">
                    <?php foreach ($theses as $thesis): ?>
                        <div class="research-card">
                            <!-- Card Header -->
                            <div class="card-header">
                                <div class="card-badges">
                                    <?php if (!empty($thesis['strand'])): ?>
                                        <span class="badge strand-<?= strtolower($thesis['strand']) ?>">
                                            <?= htmlspecialchars($thesis['strand']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge date-badge">
                                        <?= date('Y', strtotime($thesis['created_at'] ?? 'now')) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body">
                                <h3 class="paper-title">
                                    <a href="<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?>">
                                        <?= htmlspecialchars($thesis['title'] ?? 'Untitled') ?>
                                    </a>
                                </h3>

                                <div class="paper-info">
                                    <div class="info-row">
                                        <span class="info-label">Author:</span>
                                        <span class="info-value"><?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown') ?></span>
                                    </div>
                                    <?php if (!empty($thesis['adviser'])): ?>
                                    <div class="info-row">
                                        <span class="info-label">Adviser:</span>
                                        <span class="info-value"><?= htmlspecialchars($thesis['adviser']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="info-row">
                                        <span class="info-label">Views:</span>
                                        <span class="info-value"><?= number_format($thesis['view_count'] ?? 0) ?></span>
                                    </div>
                                </div>

                                <?php if (!empty($thesis['abstract'])): ?>
                                    <div class="paper-abstract">
                                        <?= htmlspecialchars(substr($thesis['abstract'], 0, 200)) ?>
                                        <?= strlen($thesis['abstract']) > 200 ? '...' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Card Footer -->
                            <div class="card-footer">
                                <a href="<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?>"
                                   class="btn btn-outline">
                                    View Details
                                </a>
                                <?php if (!empty($thesis['file_path'])): ?>
                                    <a href="<?= route('research/download') ?>&id=<?= $thesis['id'] ?>"
                                       class="btn btn-primary"
                                       target="_blank">
                                        Download PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>"
                                   class="page-link">First</a>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
                                   class="page-link">Previous</a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="page-link active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                       class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
                                   class="page-link">Next</a>
                                <a href="<?= route('research') ?>?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"
                                   class="page-link">Last</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- No Results -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </div>
                    <h3>No Research Papers Found</h3>
                    <?php if ($hasFilters): ?>
                        <p>No papers match your search criteria. Try adjusting your filters or search terms.</p>
                        <a href="<?= route('research') ?>" class="btn btn-primary">Clear All Filters</a>
                    <?php else: ?>
                        <p>Start by entering keywords in the search box above, or use the filters to browse by category.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Research Page Styles */
.research-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2rem;
    color: #3d2817;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #6b5847;
    font-family: 'Georgia', serif;
}

.results-summary {
    color: #7b3f00;
    font-weight: 600;
}

/* Search Section */
.search-section-container {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
}

.search-box {
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.3rem;
    color: #3d2817;
    margin-bottom: 8px;
    font-family: 'Georgia', serif;
}

.help-text {
    color: #6b5847;
    font-size: 0.95rem;
    margin-bottom: 15px;
    font-family: 'Georgia', serif;
}

.search-bar {
    display: flex;
    gap: 10px;
}

.search-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #d4a574;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
    background: white;
    font-family: 'Georgia', serif;
}

.search-input:focus {
    outline: none;
    border-color: #7b3f00;
    box-shadow: 0 0 0 3px rgba(123, 63, 0, 0.1);
}

.search-btn {
    padding: 12px 30px;
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-family: 'Georgia', serif;
}

.search-btn:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    box-shadow: 0 4px 15px rgba(123, 63, 0, 0.3);
}

/* Filters Section */
.filters-section {
    border-top: 2px solid #d4a574;
    padding-top: 20px;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.toggle-filters {
    padding: 8px 16px;
    background: rgba(212, 165, 116, 0.2);
    border: 2px solid #d4a574;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s;
    color: #3d2817;
    font-family: 'Georgia', serif;
    font-weight: 600;
}

.toggle-filters:hover {
    background: rgba(212, 165, 116, 0.3);
    border-color: #7b3f00;
}

.filters-container {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.filter-section {
    margin-bottom: 25px;
}

.filter-heading {
    font-size: 1.1rem;
    color: #3d2817;
    margin-bottom: 15px;
    font-weight: 600;
    font-family: 'Georgia', serif;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.filter-field {
    display: flex;
    flex-direction: column;
}

.filter-field label {
    font-weight: 600;
    color: #3d2817;
    margin-bottom: 6px;
    font-size: 0.9rem;
    font-family: 'Georgia', serif;
}

.form-input,
.form-select {
    padding: 10px 12px;
    border: 2px solid #d4a574;
    border-radius: 6px;
    font-size: 0.95rem;
    transition: border-color 0.3s;
    background: white;
    font-family: 'Georgia', serif;
    color: #2c2416;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #7b3f00;
    box-shadow: 0 0 0 2px rgba(123, 63, 0, 0.1);
}

.field-hint {
    color: #8b7355;
    font-size: 0.85rem;
    margin-top: 4px;
    font-family: 'Georgia', serif;
}

.filter-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #d4a574;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: inline-block;
    font-family: 'Georgia', serif;
}

.btn-primary {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    box-shadow: 0 4px 12px rgba(123, 63, 0, 0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #8b6f47 0%, #6f5635 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #6f5635 0%, #5a4429 100%);
    box-shadow: 0 4px 12px rgba(107, 88, 71, 0.3);
}

.btn-outline {
    background: transparent;
    color: #7b3f00;
    border: 2px solid #7b3f00;
}

.btn-outline:hover {
    background: rgba(123, 63, 0, 0.1);
    color: #5a2d00;
    border-color: #5a2d00;
}

/* Results Section */
.results-header {
    margin-bottom: 25px;
}

.results-info {
    font-size: 1.1rem;
    color: #3d2817;
    font-family: 'Georgia', serif;
}

.page-info {
    color: #6b5847;
    font-size: 0.95rem;
}

/* Research Grid */
.research-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.research-card {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    transition: all 0.3s;
    border: 2px solid #d4a574;
}

.research-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(123, 63, 0, 0.25);
    border-color: #7b3f00;
    background: linear-gradient(135deg, #fff 0%, #faf8f3 100%);
}

.card-header {
    padding: 16px 20px;
    background: rgba(212, 165, 116, 0.2);
    border-bottom: 2px solid #d4a574;
}

.card-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.strand-stem { background: #e3f2fd; color: #1976d2; }
.strand-abm { background: #e8f5e8; color: #2e7d32; }
.strand-humss { background: #fff3e0; color: #f57c00; }
.strand-tvl-he { background: #f3e5f5; color: #7b1fa2; }
.strand-tvl-ict { background: #e0f2f1; color: #00796b; }
.strand-adt { background: #fce4ec; color: #c2185b; }

.date-badge {
    background: rgba(212, 165, 116, 0.3);
    color: #3d2817;
}

.card-body {
    padding: 20px;
}

.paper-title {
    margin: 0 0 15px 0;
    font-size: 1.1rem;
    line-height: 1.4;
    font-family: 'Georgia', serif;
}

.paper-title a {
    color: #3d2817;
    text-decoration: none;
    font-weight: 600;
}

.paper-title a:hover {
    color: #7b3f00;
}

.paper-info {
    margin-bottom: 15px;
}

.info-row {
    display: flex;
    margin-bottom: 6px;
    font-size: 0.9rem;
    font-family: 'Georgia', serif;
}

.info-label {
    font-weight: 600;
    color: #7b3f00;
    min-width: 80px;
}

.info-value {
    color: #2c2416;
}

.paper-abstract {
    color: #6b5847;
    font-size: 0.9rem;
    line-height: 1.6;
    font-family: 'Georgia', serif;
}

.card-footer {
    padding: 16px 20px;
    background: rgba(212, 165, 116, 0.2);
    border-top: 2px solid #d4a574;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, rgba(212, 165, 116, 0.1) 0%, rgba(139, 111, 71, 0.05) 100%);
    border-radius: 12px;
    border: 2px solid #d4a574;
}

.empty-icon {
    color: #d4a574;
    margin-bottom: 20px;
}

.empty-icon svg {
    width: 80px;
    height: 80px;
}

.empty-state h3 {
    color: #3d2817;
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-family: 'Georgia', serif;
}

.empty-state p {
    color: #6b5847;
    font-size: 1rem;
    margin-bottom: 25px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    font-family: 'Georgia', serif;
}

/* Pagination */
.pagination-container {
    text-align: center;
    margin-top: 40px;
}

.pagination {
    display: inline-flex;
    gap: 6px;
}

.page-link {
    padding: 8px 14px;
    background: #faf8f3;
    border: 2px solid #d4a574;
    border-radius: 6px;
    color: #3d2817;
    text-decoration: none;
    transition: all 0.2s;
    font-family: 'Georgia', serif;
}

.page-link:hover {
    background: rgba(212, 165, 116, 0.2);
    border-color: #7b3f00;
    color: #7b3f00;
}

.page-link.active {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    border-color: #7b3f00;
    color: #f5e6d3;
}

/* Responsive Design */
@media (max-width: 768px) {
    .research-page {
        padding: 20px 15px;
    }

    .search-bar {
        flex-direction: column;
    }

    .filter-row {
        grid-template-columns: 1fr;
    }

    .research-grid {
        grid-template-columns: 1fr;
    }

    .filter-actions {
        flex-direction: column;
    }

    .card-footer {
        flex-direction: column;
    }
}
</style>

<script>
function toggleFilters() {
    const container = document.getElementById('filtersContainer');
    const toggleText = document.getElementById('filterToggleText');

    if (container.style.display === 'none') {
        container.style.display = 'block';
        toggleText.textContent = 'Hide Filters';
    } else {
        container.style.display = 'none';
        toggleText.textContent = 'Show Filters';
    }
}

// Show filters automatically if any filter is applied
<?php if ($hasFilters): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('filtersContainer').style.display = 'block';
    document.getElementById('filterToggleText').textContent = 'Hide Filters';
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
