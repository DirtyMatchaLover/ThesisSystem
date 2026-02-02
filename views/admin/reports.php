<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="container">
    <h2 class="page-title"> Reports & Analytics</h2>

    <!-- Flash Messages -->
    <?php if (function_exists('has_flash') && has_flash('success')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars(get_flash('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (function_exists('has_flash') && has_flash('error')): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars(get_flash('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <h3><?= $stats['total'] ?? 0 ?></h3>
            <p>Total Submissions</p>
            <small>All time</small>
        </div>
        <div class="stat-card">
            <h3><?= $stats['approved'] ?? 0 ?></h3>
            <p>Published</p>
            <small>Approved theses</small>
        </div>
        <div class="stat-card">
            <h3><?= $stats['under_review'] ?? 0 ?></h3>
            <p>Under Review</p>
            <small>Pending approval</small>
        </div>
        <div class="stat-card">
            <h3><?= count($topAuthors ?? []) ?></h3>
            <p>Active Authors</p>
            <small>This period</small>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="report-section">
        <h3> Monthly Submission Trends</h3>
        <?php if (!empty($monthlyData)): ?>
            <div class="chart-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Submissions</th>
                            <th>Approved</th>
                            <th>Approval Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthlyData as $data): ?>
                            <tr>
                                <td><?= date('F Y', strtotime($data['month'] . '-01')) ?></td>
                                <td><?= $data['submissions'] ?></td>
                                <td><?= $data['approved'] ?></td>
                                <td>
                                    <?php if ($data['submissions'] > 0): ?>
                                        <?= round(($data['approved'] / $data['submissions']) * 100, 1) ?>%
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <p> No monthly data available yet</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top Authors -->
    <div class="report-section">
        <h3> Top Contributing Authors</h3>
        <?php if (!empty($topAuthors)): ?>
            <div class="authors-grid">
                <?php foreach ($topAuthors as $author): ?>
                    <div class="author-card">
                        <div class="author-info">
                            <h4><?= htmlspecialchars($author['name']) ?></h4>
                            <span class="role"><?= htmlspecialchars(ucwords($author['role'])) ?></span>
                        </div>
                        <div class="author-stats">
                            <div class="stat">
                                <span class="number"><?= $author['thesis_count'] ?></span>
                                <span class="label">Total</span>
                            </div>
                            <div class="stat">
                                <span class="number"><?= $author['approved_count'] ?></span>
                                <span class="label">Approved</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <p> No author data available yet</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Export Options -->
    <div class="report-section">
        <h3> Export Data</h3>
        <div class="export-buttons">
            <a href="<?= route('admin/export') ?>" class="btn btn-primary">
                 Export All Data (CSV)
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                ️ Print Report
            </button>
            <a href="<?= route('admin/dashboard') ?>" class="btn btn-info">
                 Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
.page-title {
    color: #d32f2f;
    margin-bottom: 30px;
    font-size: 2.2rem;
    font-weight: 600;
    text-align: center;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    border-left: 5px solid #d32f2f;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-card h3 {
    font-size: 2rem;
    margin: 0 0 10px 0;
    color: #d32f2f;
}

.report-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.report-section h3 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 1.3rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.authors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.author-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.author-card:hover {
    border-color: #d32f2f;
    transform: translateY(-2px);
}

.author-info h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.1rem;
}

.author-info .role {
    color: #666;
    font-size: 0.9rem;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 12px;
}

.author-stats {
    display: flex;
    gap: 20px;
    margin-top: 15px;
}

.author-stats .stat {
    text-align: center;
}

.author-stats .number {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: #d32f2f;
}

.author-stats .label {
    font-size: 0.8rem;
    color: #666;
}

.export-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary { background: #d32f2f; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-info { background: #17a2b8; color: white; }

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0.9;
}

.no-data {
    text-align: center;
    padding: 40px 20px;
    color: #666;
    font-style: italic;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .export-buttons {
        justify-content: center;
    }
    
    .authors-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>