<?php
$user = current_user();
$theses = $data['theses'] ?? [];
$stats = $data['stats'] ?? [];
$comments = $data['comments'] ?? [];
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="main-container">
    <div class="stats-dashboard-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title"> My Thesis Statistics</h1>
            <p class="page-subtitle">Track your research impact and submission progress</p>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['total_theses'] ?? 0) ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">️</div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['total_views'] ?? 0) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['total_downloads'] ?? 0) ?></div>
                    <div class="stat-label">Total Downloads</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['approved'] ?? 0) ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['pending'] ?? 0) ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"></div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($stats['rejected'] ?? 0) ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <!-- My Theses Table -->
        <div class="theses-section">
            <h2 class="section-title"> My Theses</h2>

            <?php if (!empty($theses)): ?>
                <div class="theses-table-wrapper">
                    <table class="theses-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Views</th>
                                <th>Downloads</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($theses as $thesis): ?>
                                <tr>
                                    <td class="thesis-title-cell">
                                        <strong><?= htmlspecialchars($thesis['title'] ?? 'Untitled') ?></strong>
                                        <?php if (!empty($thesis['strand'])): ?>
                                            <br><span class="strand-tag"><?= htmlspecialchars($thesis['strand']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($thesis['status'] ?? 'unknown') ?>">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status'] ?? 'unknown'))) ?>
                                        </span>
                                    </td>
                                    <td><?= format_date($thesis['created_at'] ?? '') ?></td>
                                    <td class="text-center">
                                        <strong><?= number_format($thesis['view_count'] ?? 0) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <strong><?= number_format($thesis['download_count'] ?? 0) ?></strong>
                                    </td>
                                    <td>
                                        <a href="<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?>" class="btn-small btn-view">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"></div>
                    <h3>No Submissions Yet</h3>
                    <p>You haven't submitted any thesis papers yet.</p>
                    <a href="<?= route('thesis/create') ?>" class="btn btn-primary">
                        Submit Your First Thesis
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Comments Section -->
        <?php if (!empty($comments)): ?>
            <div class="comments-section">
                <h2 class="section-title"> Recent Feedback</h2>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <span class="comment-author"><?= htmlspecialchars($comment['commenter_name'] ?? 'Unknown') ?></span>
                                <span class="comment-date"><?= format_datetime($comment['created_at'] ?? '') ?></span>
                            </div>
                            <div class="comment-thesis">
                                On: <strong><?= htmlspecialchars($comment['thesis_title'] ?? 'Unknown Thesis') ?></strong>
                            </div>
                            <div class="comment-text">
                                <?= nl2br(htmlspecialchars($comment['comment'] ?? 'No comment text')) ?>
                            </div>
                            <?php if (!empty($comment['type'])): ?>
                                <span class="comment-type-badge type-<?= strtolower($comment['type']) ?>">
                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $comment['type']))) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.stats-dashboard-container {
    max-width: 1400px;
    margin: 40px auto;
    padding: 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    color: #3d2817;
    font-weight: 600;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.page-subtitle {
    color: #8b6f47;
    font-size: 1.1rem;
    font-family: 'Georgia', serif;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(123, 63, 0, 0.25);
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #7b3f00;
    font-family: 'Georgia', serif;
    line-height: 1;
}

.stat-label {
    font-size: 0.9rem;
    color: #8b6f47;
    margin-top: 5px;
    font-family: 'Georgia', serif;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Sections */
.theses-section,
.comments-section {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
}

.section-title {
    color: #3d2817;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    font-family: 'Georgia', serif;
}

/* Theses Table */
.theses-table-wrapper {
    overflow-x: auto;
}

.theses-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Georgia', serif;
}

.theses-table thead {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
}

.theses-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
}

.theses-table tbody tr {
    border-bottom: 1px solid #d4a574;
    transition: all 0.2s ease;
}

.theses-table tbody tr:hover {
    background: rgba(212, 165, 116, 0.1);
}

.theses-table td {
    padding: 15px;
    color: #2c2416;
}

.thesis-title-cell {
    max-width: 300px;
}

.strand-tag {
    display: inline-block;
    padding: 3px 8px;
    background: rgba(123, 63, 0, 0.1);
    border-radius: 4px;
    font-size: 0.75rem;
    color: #7b3f00;
    margin-top: 5px;
}

.text-center {
    text-align: center;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
}

.status-approved { background: #e8f5e9; color: #2e7d32; }
.status-submitted { background: rgba(212, 165, 116, 0.3); color: #7b3f00; }
.status-under_review { background: #e3f2fd; color: #1976d2; }
.status-rejected { background: #ffebee; color: #d32f2f; }
.status-draft { background: rgba(139, 111, 71, 0.2); color: #5a2d00; }

.btn-small {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
    font-weight: 600;
}

.btn-view {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 1px solid #d4a574;
}

.btn-view:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(123, 63, 0, 0.3);
    color: #f5e6d3;
}

/* Comments List */
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.comment-card {
    background: #fff;
    border-left: 4px solid #7b3f00;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(61, 40, 23, 0.1);
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.comment-author {
    font-weight: bold;
    color: #7b3f00;
}

.comment-date {
    font-size: 0.85rem;
    color: #8b6f47;
}

.comment-thesis {
    font-size: 0.9rem;
    color: #8b6f47;
    margin-bottom: 10px;
}

.comment-text {
    color: #2c2416;
    line-height: 1.6;
    margin-bottom: 10px;
}

.comment-type-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.type-review { background: #e3f2fd; color: #1976d2; }
.type-feedback { background: #fff3e0; color: #f57c00; }
.type-approval { background: #e8f5e9; color: #2e7d32; }
.type-rejection { background: #ffebee; color: #d32f2f; }
.type-revision_request { background: #fff9c4; color: #f57f17; }

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #3d2817;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.6;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #7b3f00;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.empty-state p {
    font-size: 1rem;
    margin-bottom: 20px;
    font-family: 'Georgia', serif;
    color: #8b6f47;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    font-weight: 600;
    transition: all 0.3s ease;
    font-family: 'Georgia', serif;
}

.btn-primary {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(123, 63, 0, 0.3);
    color: #f5e6d3;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .page-title {
        font-size: 2rem;
    }

    .theses-table {
        font-size: 0.85rem;
    }

    .theses-table th,
    .theses-table td {
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
