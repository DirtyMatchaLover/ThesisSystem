<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<style>
.report-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: var(--bg-primary);
}

.report-header {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.report-title {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.user-info {
    background: rgba(255,255,255,0.1);
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid #2196f3;
    box-shadow: 0 2px 8px var(--shadow-color);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2196f3;
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.activity-table {
    background: var(--bg-secondary);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.activity-table table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
}

body.dark-theme .activity-table th {
    background: #2a2a2a;
    border-color: #444;
}

.activity-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

body.dark-theme .activity-table td {
    border-color: #333;
}

.export-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.btn-export {
    padding: 12px 24px;
    background: #4caf50;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    display: inline-block;
}

.btn-export:hover {
    background: #45a049;
}

.btn-back {
    padding: 12px 24px;
    background: #666;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
}

.btn-back:hover {
    background: #555;
}
</style>

<div class="report-container">
    <!-- Header -->
    <div class="report-header">
        <h1 class="report-title">📊 Individual User Activity Report</h1>
        <div class="user-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
            <p><strong>Member Since:</strong> <?= format_date($user['created_at']) ?></p>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="export-buttons">
        <a href="<?= route('admin/export-individual-csv') ?>&user_id=<?= $user['id'] ?>" class="btn-export">
            📥 Export to CSV
        </a>
        <a href="<?= route('admin/users') ?>" class="btn-back">
            ← Back to Users
        </a>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['total_activities']) ?></div>
            <div class="stat-label">Total Activities</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['total_logins']) ?></div>
            <div class="stat-label">Total Logins</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= $stats['avg_session_minutes'] ?> min</div>
            <div class="stat-label">Avg Session Duration</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['thesis_uploads']) ?></div>
            <div class="stat-label">Thesis Uploads</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['thesis_views']) ?></div>
            <div class="stat-label">Thesis Views</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['thesis_downloads']) ?></div>
            <div class="stat-label">Thesis Downloads</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['thesis_searches']) ?></div>
            <div class="stat-label">Search Queries</div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?= $stats['most_active_day'] ?></div>
            <div class="stat-label">Most Active Day (<?= $stats['most_active_count'] ?> activities)</div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="activity-table">
        <h3>Recent Activities (Last 50)</h3>
        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Activity Type</th>
                    <th>Description</th>
                    <th>Thesis</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td><?= date('M d, Y g:i A', strtotime($activity['created_at'])) ?></td>
                            <td><?= htmlspecialchars($activity['activity_type']) ?></td>
                            <td><?= htmlspecialchars($activity['activity_description'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($activity['thesis_title'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            No activities recorded yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
