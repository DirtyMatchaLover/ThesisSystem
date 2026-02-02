<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<style>
.analytics-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background: #f8f9fa;
    min-height: 100vh;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    color: #d32f2f;
    font-size: 2rem;
    margin-bottom: 5px;
}

.page-header p {
    color: #666;
    font-size: 1rem;
}

/* Overview Cards */
.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-card .icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.stat-card .value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-card .label {
    color: #666;
    font-size: 0.9rem;
    font-weight: 600;
}

.stat-card .sub-label {
    color: #999;
    font-size: 0.8rem;
    margin-top: 8px;
}

/* Status breakdown */
.status-breakdown {
    display: flex;
    gap: 10px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-approved { background: #e8f5e9; color: #2e7d32; }
.status-submitted { background: #e3f2fd; color: #1565c0; }
.status-under_review { background: #fff3e0; color: #ef6c00; }
.status-rejected { background: #ffebee; color: #c62828; }
.status-draft { background: #f5f5f5; color: #666; }

/* Two column layout */
.two-column {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

/* Section card */
.section-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-card h2 {
    color: #d32f2f;
    font-size: 1.3rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Table styles */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.9rem;
}

.data-table tr:hover {
    background: #fafafa;
}

.thesis-title {
    color: #333;
    font-weight: 500;
}

.thesis-meta {
    color: #999;
    font-size: 0.8rem;
    margin-top: 3px;
}

/* Activity item */
.activity-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-title {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.activity-meta {
    color: #999;
    font-size: 0.8rem;
    margin-top: 4px;
}

.activity-time {
    color: #666;
    font-size: 0.75rem;
}

/* Chart container */
.chart-container {
    height: 300px;
    margin-top: 20px;
}

/* Strand stats */
.strand-grid {
    display: grid;
    gap: 15px;
}

.strand-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #d32f2f;
}

.strand-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.strand-name {
    font-weight: 600;
    color: #333;
}

.strand-count {
    font-size: 1.5rem;
    font-weight: bold;
    color: #d32f2f;
}

.strand-details {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #666;
}

.strand-stat {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Responsive */
@media (max-width: 968px) {
    .two-column {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .overview-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .overview-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="analytics-container">
    <div class="page-header">
        <h1>📊 System Analytics</h1>
        <p>Real-time system metrics and performance data</p>
    </div>

    <!-- Overview Statistics -->
    <div class="overview-grid">
        <div class="stat-card">
            <div class="icon">📚</div>
            <div class="value"><?= number_format($overview['total']) ?></div>
            <div class="label">Total Theses</div>
            <div class="status-breakdown">
                <span class="status-badge status-approved"><?= $overview['approved'] ?> Approved</span>
                <span class="status-badge status-submitted"><?= $overview['submitted'] ?> Submitted</span>
                <span class="status-badge status-under_review"><?= $overview['under_review'] ?> Review</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">👥</div>
            <div class="value"><?= number_format($overview['students']) ?></div>
            <div class="label">Total Students</div>
            <div class="sub-label">
                <?= $user_stats['students_with_submissions'] ?> with submissions
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">📥</div>
            <div class="value"><?= number_format($overview['total_downloads']) ?></div>
            <div class="label">Total Downloads</div>
            <div class="sub-label">
                <?= number_format($overview['total_views']) ?> views
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">✅</div>
            <div class="value"><?= number_format($thesis_stats['approval_rate'], 1) ?>%</div>
            <div class="label">Approval Rate</div>
            <div class="sub-label">
                Avg: <?= number_format($thesis_stats['avg_approval_days'], 1) ?> days
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">📅</div>
            <div class="value"><?= number_format($thesis_stats['this_month']) ?></div>
            <div class="label">This Month</div>
            <div class="sub-label">
                New submissions
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">👤</div>
            <div class="value"><?= number_format($user_stats['active_users']) ?></div>
            <div class="label">Active Users</div>
            <div class="sub-label">
                Last 30 days
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">👨‍🏫</div>
            <div class="value"><?= number_format($overview['faculty']) ?></div>
            <div class="label">Faculty Members</div>
            <div class="sub-label">
                <?= $overview['librarians'] ?> librarians, <?= $overview['admins'] ?> admins
            </div>
        </div>

        <div class="stat-card">
            <div class="icon">💾</div>
            <div class="value"><?= number_format($overview['total_files']) ?></div>
            <div class="label">Files Stored</div>
            <div class="sub-label">
                PDF documents
            </div>
        </div>
    </div>

    <!-- Recent Activity & Popular Theses -->
    <div class="two-column">
        <div class="section-card">
            <h2>🕒 Recent Activity</h2>
            <?php if (empty($recent_activity)): ?>
                <p style="color: #999; text-align: center; padding: 20px;">No recent activity</p>
            <?php else: ?>
                <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-title"><?= htmlspecialchars($activity['title']) ?></div>
                        <div class="activity-meta">
                            <span class="status-badge status-<?= $activity['status'] ?>">
                                <?= ucwords(str_replace('_', ' ', $activity['status'])) ?>
                            </span>
                            <span style="margin: 0 8px;">•</span>
                            <?= htmlspecialchars($activity['author_name']) ?>
                            <?php if ($activity['strand']): ?>
                                <span style="margin: 0 8px;">•</span>
                                <?= htmlspecialchars($activity['strand']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="activity-time">
                            Updated: <?= date('M j, Y g:i A', strtotime($activity['updated_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <h2>🔥 Popular Theses</h2>
            <?php if (empty($popular_theses)): ?>
                <p style="color: #999; text-align: center; padding: 20px;">No data available</p>
            <?php else: ?>
                <?php foreach ($popular_theses as $thesis): ?>
                    <div class="activity-item">
                        <div class="activity-title">
                            <a href="?route=admin/pdfview&id=<?= $thesis['id'] ?>" style="color: #d32f2f; text-decoration: none;">
                                <?= htmlspecialchars(substr($thesis['title'], 0, 50)) . (strlen($thesis['title']) > 50 ? '...' : '') ?>
                            </a>
                        </div>
                        <div class="activity-meta">
                            <?= htmlspecialchars($thesis['author_name']) ?>
                            <?php if ($thesis['strand']): ?>
                                <span style="margin: 0 8px;">•</span>
                                <?= htmlspecialchars($thesis['strand']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="activity-time">
                            📥 <?= $thesis['download_count'] ?> downloads • 👁️ <?= $thesis['view_count'] ?> views
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Performance by Strand -->
    <div class="section-card">
        <h2>📊 Performance by Academic Strand</h2>
        <?php if (empty($strand_stats)): ?>
            <p style="color: #999; text-align: center; padding: 20px;">No data available</p>
        <?php else: ?>
            <div class="strand-grid">
                <?php foreach ($strand_stats as $strand): ?>
                    <div class="strand-item">
                        <div class="strand-header">
                            <span class="strand-name"><?= htmlspecialchars($strand['name']) ?></span>
                            <span class="strand-count"><?= $strand['submissions'] ?></span>
                        </div>
                        <div class="strand-details">
                            <div class="strand-stat">
                                <span>✅</span>
                                <span><?= number_format($strand['approval_rate'], 1) ?>% approved</span>
                            </div>
                            <div class="strand-stat">
                                <span>⏱️</span>
                                <span><?= number_format($strand['avg_processing_days'], 1) ?> days avg</span>
                            </div>
                            <div class="strand-stat">
                                <span>📥</span>
                                <span><?= $strand['downloads'] ?> downloads</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Monthly Trends -->
    <?php if (!empty($monthly_trends)): ?>
    <div class="section-card" style="margin-top: 20px;">
        <h2>📈 Monthly Submission Trends (Last 6 Months)</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Submissions</th>
                    <th>Approved</th>
                    <th>Approval Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly_trends as $trend): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($trend['month'] . '-01')) ?></td>
                        <td><?= $trend['submissions'] ?></td>
                        <td><?= $trend['approved'] ?></td>
                        <td>
                            <?php
                            $rate = $trend['submissions'] > 0 ? ($trend['approved'] / $trend['submissions'] * 100) : 0;
                            ?>
                            <span class="status-badge <?= $rate >= 80 ? 'status-approved' : ($rate >= 60 ? 'status-submitted' : 'status-rejected') ?>">
                                <?= number_format($rate, 1) ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Key Metrics Summary -->
    <div class="section-card" style="margin-top: 20px;">
        <h2>📋 Key Performance Metrics</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Average Approval Time</td>
                    <td><strong><?= number_format($thesis_stats['avg_approval_days'], 1) ?> days</strong></td>
                    <td>Min: <?= number_format($thesis_stats['min_approval_days'], 1) ?> days | Max: <?= number_format($thesis_stats['max_approval_days'], 1) ?> days</td>
                </tr>
                <tr>
                    <td>Approval Success Rate</td>
                    <td><strong><?= number_format($thesis_stats['approval_rate'], 1) ?>%</strong></td>
                    <td><?= $overview['approved'] ?> approved out of <?= $overview['approved'] + $overview['rejected'] ?> reviewed</td>
                </tr>
                <tr>
                    <td>User Engagement</td>
                    <td><strong><?= number_format($overview['total_downloads'] + $overview['total_views']) ?></strong></td>
                    <td><?= number_format($overview['total_downloads']) ?> downloads + <?= number_format($overview['total_views']) ?> views</td>
                </tr>
                <tr>
                    <td>Active Student Participation</td>
                    <td><strong><?= number_format(($user_stats['students_with_submissions'] / max($overview['students'], 1)) * 100, 1) ?>%</strong></td>
                    <td><?= $user_stats['students_with_submissions'] ?> out of <?= $overview['students'] ?> students have submitted</td>
                </tr>
                <tr>
                    <td>System Activity</td>
                    <td><strong><?= $user_stats['active_users'] ?> active users</strong></td>
                    <td>Users who logged in within the last 30 days</td>
                </tr>
                <tr>
                    <td>Growth This Month</td>
                    <td><strong><?= $thesis_stats['this_month'] ?> submissions</strong></td>
                    <td><?= $user_stats['new_this_month'] ?> new users joined</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
