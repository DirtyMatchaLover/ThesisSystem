<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<style>
.combined-report-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background: var(--bg-primary);
}

.combined-header {
    background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.combined-title {
    font-size: 2rem;
    margin-bottom: 10px;
}

.combined-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: var(--bg-secondary);
    padding: 25px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 8px var(--shadow-color);
    border-top: 4px solid #4caf50;
}

.summary-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #4caf50;
    margin-bottom: 10px;
}

.summary-label {
    color: var(--text-secondary);
    font-size: 0.95rem;
    font-weight: 600;
}

.section-box {
    background: var(--bg-secondary);
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px var(--shadow-color);
}

.section-title {
    font-size: 1.3rem;
    color: var(--text-primary);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4caf50;
}

.role-table, .activity-table, .time-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.role-table th, .activity-table th, .time-table th {
    background: #f5f5f5;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
}

body.dark-theme .role-table th,
body.dark-theme .activity-table th,
body.dark-theme .time-table th {
    background: #2a2a2a;
    border-color: #444;
}

.role-table td, .activity-table td, .time-table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

body.dark-theme .role-table td,
body.dark-theme .activity-table td,
body.dark-theme .time-table td {
    border-color: #333;
}

.export-section {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.btn-export-large {
    padding: 15px 30px;
    background: #4caf50;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    display: inline-block;
    text-align: center;
}

.btn-export-large:hover {
    background: #45a049;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.sop-note {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 25px;
}

body.dark-theme .sop-note {
    background: #3a3300;
    border-color: #ffc107;
}

.sop-note strong {
    color: #856404;
}

body.dark-theme .sop-note strong {
    color: #ffc107;
}
</style>

<div class="combined-report-container">
    <!-- Header -->
    <div class="combined-header">
        <h1 class="combined-title">📈 Combined User Activity Report</h1>
        <p class="combined-subtitle">Aggregated Data for All Users (Librarians, Faculty, Students)</p>
        <p class="combined-subtitle">For Statement of Problem (SOP) Analysis</p>
    </div>

    <!-- SOP Note -->
    <div class="sop-note">
        <strong>📋 For Your SOP:</strong> This report combines activity data from all librarians, faculty members, and students.
        Use this data to answer research questions about system usage, user engagement, and effectiveness metrics.
        Export to CSV for detailed analysis in Excel or statistical software.
    </div>

    <!-- Export Button -->
    <div class="export-section">
        <a href="<?= route('admin/export-combined-csv') ?>" class="btn-export-large">
            📥 Export All User Data to CSV (For SOP Analysis)
        </a>
        <a href="<?= route('admin/dashboard') ?>" class="btn-export-large" style="background: #666;">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value"><?= number_format($aggregatedStats['total_users']) ?></div>
            <div class="summary-label">Total Users Tracked</div>
        </div>

        <div class="summary-card">
            <div class="summary-value"><?= number_format($aggregatedStats['total_activities']) ?></div>
            <div class="summary-label">Total Activities</div>
        </div>

        <div class="summary-card">
            <div class="summary-value"><?= $aggregatedStats['avg_activities_per_user'] ?></div>
            <div class="summary-label">Avg Activities/User</div>
        </div>

        <div class="summary-card">
            <div class="summary-value"><?= number_format($aggregatedStats['total_logins']) ?></div>
            <div class="summary-label">Total Logins</div>
        </div>

        <div class="summary-card">
            <div class="summary-value"><?= $aggregatedStats['avg_session_minutes'] ?> min</div>
            <div class="summary-label">Avg Session Time</div>
        </div>

        <div class="summary-card">
            <div class="summary-value"><?= $aggregatedStats['usage_score'] ?>%</div>
            <div class="summary-label">System Usage Score</div>
        </div>
    </div>

    <!-- User Count Breakdown -->
    <div class="section-box">
        <h3 class="section-title">👥 User Distribution</h3>
        <table class="role-table">
            <tr>
                <td><strong>Librarians:</strong></td>
                <td><?= $aggregatedStats['librarians'] ?> users</td>
                <td><?= $aggregatedStats['total_users'] > 0 ? round(($aggregatedStats['librarians'] / $aggregatedStats['total_users']) * 100, 1) : 0 ?>%</td>
            </tr>
            <tr>
                <td><strong>Faculty:</strong></td>
                <td><?= $aggregatedStats['faculty'] ?> users</td>
                <td><?= $aggregatedStats['total_users'] > 0 ? round(($aggregatedStats['faculty'] / $aggregatedStats['total_users']) * 100, 1) : 0 ?>%</td>
            </tr>
            <tr>
                <td><strong>Students:</strong></td>
                <td><?= $aggregatedStats['students'] ?> users</td>
                <td><?= $aggregatedStats['total_users'] > 0 ? round(($aggregatedStats['students'] / $aggregatedStats['total_users']) * 100, 1) : 0 ?>%</td>
            </tr>
        </table>
    </div>

    <!-- Activity Breakdown by Role -->
    <div class="section-box">
        <h3 class="section-title">📊 Activity Breakdown by Role</h3>
        <table class="role-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Active Users</th>
                    <th>Total Activities</th>
                    <th>Avg Activities</th>
                    <th>Avg Logins</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roleBreakdown)): ?>
                    <?php foreach ($roleBreakdown as $role): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(ucfirst($role['role'])) ?></strong></td>
                            <td><?= number_format($role['active_users']) ?></td>
                            <td><?= number_format($role['total_activities']) ?></td>
                            <td><?= round($role['avg_activities'] ?? 0, 1) ?></td>
                            <td><?= round($role['avg_logins'] ?? 0, 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">No data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Most Common Activities -->
    <div class="section-box">
        <h3 class="section-title">🔥 Most Common Activities</h3>
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Activity Type</th>
                    <th>Total Count</th>
                    <th>Unique Users</th>
                    <th>Percentage of Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($commonActivities)): ?>
                    <?php
                    $totalActivitiesSum = array_sum(array_column($commonActivities, 'count'));
                    foreach ($commonActivities as $activity):
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($activity['activity_type']) ?></strong></td>
                            <td><?= number_format($activity['count']) ?></td>
                            <td><?= number_format($activity['unique_users']) ?></td>
                            <td><?= $totalActivitiesSum > 0 ? round(($activity['count'] / $totalActivitiesSum) * 100, 1) : 0 ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">No activities recorded yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Time-Based Analysis -->
    <div class="section-box">
        <h3 class="section-title">📅 Activity Over Time (Last 30 Days)</h3>
        <table class="time-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Activities</th>
                    <th>Active Users</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($timeAnalysis)): ?>
                    <?php foreach (array_slice($timeAnalysis, 0, 15) as $day): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($day['date'])) ?></td>
                            <td><?= number_format($day['activities']) ?></td>
                            <td><?= number_format($day['active_users']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 20px;">No recent activity</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- How to Use This Data for SOP -->
    <div class="section-box" style="background: #e8f5e9;">
        <h3 class="section-title" style="border-color: #4caf50;">💡 How to Use This Data for Your SOP</h3>
        <ol style="line-height: 2; color: var(--text-primary);">
            <li><strong>Export to CSV:</strong> Click the export button above to download all user activity data</li>
            <li><strong>Analyze in Excel/SPSS:</strong> Import CSV into your statistical software for detailed analysis</li>
            <li><strong>Answer Research Questions:</strong>
                <ul style="margin-top: 10px;">
                    <li>How many users actively use the system?</li>
                    <li>What are the most common user behaviors?</li>
                    <li>How engaged are different user roles (librarian vs faculty vs students)?</li>
                    <li>What is the average session duration?</li>
                    <li>How frequently do users interact with theses?</li>
                </ul>
            </li>
            <li><strong>Calculate Metrics:</strong> Use this data to compute effectiveness, engagement, and satisfaction metrics for your thesis</li>
        </ol>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
