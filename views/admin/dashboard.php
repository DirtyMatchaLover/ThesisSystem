<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="container">
    <h2 class="page-title">Admin Dashboard</h2>

    <!-- Flash Messages -->
    <?php if (has_flash('success')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars(get_flash('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (has_flash('error')): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars(get_flash('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Stats Section -->
    <div class="stats-container">
        <div class="stat-card submitted">
            <h3><?= $stats['submitted'] ?? 0 ?></h3>
            <p>Submitted</p>
            <small>Pending Review</small>
        </div>
        <div class="stat-card review">
            <h3><?= $stats['under_review'] ?? 0 ?></h3>
            <p>Under Review</p>
            <small>In Progress</small>
        </div>
        <div class="stat-card approved">
            <h3><?= $stats['approved'] ?? 0 ?></h3>
            <p>Approved</p>
            <small>Published</small>
        </div>
        <div class="stat-card rejected">
            <h3><?= $stats['rejected'] ?? 0 ?></h3>
            <p>Rejected</p>
            <small>Need Revision</small>
        </div>
    </div>

    <!-- Automated System Metrics Section -->
    <?php if (isset($systemMetrics)): ?>
    <div class="system-metrics-section">
        <div class="section-header">
            <h3> Automated System Metrics</h3>
            <span class="refresh-indicator" title="Last updated: <?= date('g:i A') ?>"> Auto-refreshed</span>
        </div>

        <!-- System Health Overview -->
        <div class="metrics-grid">
            <!-- System Health -->
            <div class="metric-card health-card">
                <div class="metric-header">
                    <span class="metric-icon">⚡</span>
                    <h4>System Health</h4>
                </div>
                <div class="metric-value <?= $systemMetrics['performance']['system_health'] === 'Healthy' ? 'value-success' : 'value-warning' ?>">
                    <?= htmlspecialchars($systemMetrics['performance']['system_health']) ?>
                </div>
                <div class="metric-details">
                    <div class="detail-item">
                        <span>Database:</span>
                        <strong class="<?= $systemMetrics['performance']['database_status'] === 'Connected' ? 'text-success' : 'text-error' ?>">
                            <?= htmlspecialchars($systemMetrics['performance']['database_status']) ?>
                        </strong>
                    </div>
                    <div class="detail-item">
                        <span>Upload Dir:</span>
                        <strong class="<?= $systemMetrics['performance']['uploads_writable'] === 'Yes' ? 'text-success' : 'text-error' ?>">
                            <?= htmlspecialchars($systemMetrics['performance']['uploads_writable']) ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="metric-card users-card">
                <div class="metric-header">
                    <span class="metric-icon">👥</span>
                    <h4>User Statistics</h4>
                </div>
                <div class="metric-value"><?= number_format($systemMetrics['users']['total_users']) ?></div>
                <div class="metric-details">
                    <div class="detail-item">
                        <span>Students:</span>
                        <strong><?= number_format($systemMetrics['users']['students']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Faculty:</span>
                        <strong><?= number_format($systemMetrics['users']['faculty']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>New (7d):</span>
                        <strong class="text-info"><?= number_format($systemMetrics['users']['new_users_week']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Thesis Metrics -->
            <div class="metric-card theses-card">
                <div class="metric-header">
                    <span class="metric-icon">📚</span>
                    <h4>Thesis Database</h4>
                </div>
                <div class="metric-value"><?= number_format($systemMetrics['theses']['total_theses']) ?></div>
                <div class="metric-details">
                    <div class="detail-item">
                        <span>Published:</span>
                        <strong class="text-success"><?= number_format($systemMetrics['theses']['approved']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>New (7d):</span>
                        <strong class="text-info"><?= number_format($systemMetrics['theses']['new_theses_week']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Downloads:</span>
                        <strong><?= number_format($systemMetrics['theses']['total_downloads']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Storage Metrics -->
            <div class="metric-card storage-card">
                <div class="metric-header">
                    <span class="metric-icon">💾</span>
                    <h4>Storage Usage</h4>
                </div>
                <div class="metric-value"><?= $systemMetrics['storage']['total_size_mb'] ?> MB</div>
                <div class="metric-details">
                    <div class="detail-item">
                        <span>Total Files:</span>
                        <strong><?= number_format($systemMetrics['storage']['total_files']) ?></strong>
                    </div>
                    <div class="detail-item">
                        <span>Avg Size:</span>
                        <strong><?= $systemMetrics['storage']['avg_file_size_mb'] ?> MB</strong>
                    </div>
                    <div class="detail-item">
                        <span>Published:</span>
                        <strong><?= number_format($systemMetrics['storage']['published_files']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="metric-card performance-card">
                <div class="metric-header">
                    <span class="metric-icon">⚙️</span>
                    <h4>Performance</h4>
                </div>
                <div class="metric-value"><?= $systemMetrics['performance']['approval_rate'] ?>%</div>
                <div class="metric-details">
                    <div class="detail-item">
                        <span>Approval Rate:</span>
                        <strong class="text-success"><?= $systemMetrics['performance']['approval_rate'] ?>%</strong>
                    </div>
                    <div class="detail-item">
                        <span>Avg Process Time:</span>
                        <strong><?= $systemMetrics['performance']['avg_approval_days'] ?> days</strong>
                    </div>
                </div>
            </div>

            <!-- Activity Metrics -->
            <div class="metric-card activity-card">
                <div class="metric-header">
                    <span class="metric-icon">📊</span>
                    <h4>Recent Activity</h4>
                </div>
                <div class="metric-value"><?= number_format($systemMetrics['activity']['last_24_hours']) ?></div>
                <div class="metric-details">
                    <div class="detail-item">
                        <span>Last 24h:</span>
                        <strong><?= number_format($systemMetrics['activity']['last_24_hours']) ?> submissions</strong>
                    </div>
                    <div class="detail-item">
                        <span>Active Strand:</span>
                        <strong><?= htmlspecialchars($systemMetrics['activity']['most_active_strand']) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Log -->
        <?php if (!empty($recentActivity)): ?>
        <div class="recent-activity-section">
            <h4> Recent System Activity</h4>
            <div class="activity-list">
                <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon status-<?= $activity['status'] ?>"></div>
                    <div class="activity-content">
                        <div class="activity-title"><?= htmlspecialchars($activity['title']) ?></div>
                        <div class="activity-meta">
                            <span class="author"><?= htmlspecialchars($activity['author_name']) ?> (<?= htmlspecialchars($activity['author_role']) ?>)</span>
                            <span class="time"><?= date('M j, g:i A', strtotime($activity['updated_at'])) ?></span>
                            <span class="status-badge status-<?= $activity['status'] ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $activity['status']))) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="dashboard-actions">
        <a href="<?= route('admin/reports') ?>" class="btn btn-info">
             View Reports
        </a>
        <?php if (current_user()['role'] === 'admin'): ?>
            <a href="<?= route('admin/users') ?>" class="btn btn-secondary">
                 Manage Users
            </a>
        <?php endif; ?>
        <a href="<?= route('admin/dashboard') ?>" class="btn btn-warning">
             Refresh
        </a>
    </div>

    <!-- Theses Management Section -->
    <div class="table-container">
        <div class="table-header">
            <h3>Thesis Management</h3>
            <div class="table-filters">
                <!-- Simple HTML form for filtering (no JavaScript) -->
                <form method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" name="route" value="admin/dashboard">
                    
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="submitted" <?= ($_GET['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="under_review" <?= ($_GET['status'] ?? '') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                        <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    
                    <input type="text" 
                           name="search" 
                           placeholder="Search titles..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    
                    <button type="submit" class="btn btn-info" style="padding: 8px 12px;"> Filter</button>
                    
                    <?php if (!empty($_GET['status']) || !empty($_GET['search'])): ?>
                        <a href="<?= route('admin/dashboard') ?>" class="btn btn-secondary" style="padding: 8px 12px;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (!empty($all)): ?>
            <?php 
            // Apply simple filtering if needed
            $filteredTheses = $all;
            if (!empty($_GET['status'])) {
                $filteredTheses = array_filter($filteredTheses, function($thesis) {
                    return $thesis['status'] === $_GET['status'];
                });
            }
            if (!empty($_GET['search'])) {
                $search = strtolower($_GET['search']);
                $filteredTheses = array_filter($filteredTheses, function($thesis) use ($search) {
                    return strpos(strtolower($thesis['title'] ?? ''), $search) !== false;
                });
            }
            ?>
            
            <div class="theses-grid">
                <?php foreach ($filteredTheses as $thesis): ?>
                    <div class="thesis-management-card" data-status="<?= $thesis['status'] ?>">
                        <!-- Thesis Header -->
                        <div class="thesis-header">
                            <div class="thesis-info">
                                <h4 class="thesis-title"><?= htmlspecialchars($thesis['title'] ?? 'Untitled') ?></h4>
                                <div class="thesis-meta">
                                    <span class="author"> <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown') ?></span>
                                    <span class="date"> <?= date('M j, Y', strtotime($thesis['created_at'] ?? 'now')) ?></span>
                                    <?php if (!empty($thesis['strand'])): ?>
                                        <span class="strand"> <?= htmlspecialchars($thesis['strand']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="status-badge">
                                <span class="status status-<?= $thesis['status'] ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status']))) ?></span>
                            </div>
                        </div>

                        <!-- Thesis Abstract Preview -->
                        <?php if (!empty($thesis['abstract'])): ?>
                            <div class="thesis-abstract">
                                <?= htmlspecialchars(strlen($thesis['abstract']) > 150 ? substr($thesis['abstract'], 0, 150) . '...' : $thesis['abstract']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons - Pure HTML Links -->
                        <div class="thesis-actions">
                            <div class="primary-actions">
                                <!-- View PDF Button -->
                                <a href="<?= route('admin/pdfview') ?>&id=<?= $thesis['id'] ?>" 
                                   class="btn btn-view" 
                                   title="View PDF and Details"
                                   target="_blank">
                                     View PDF
                                </a>
                                
                                <!-- Download Button -->
                                <?php if (!empty($thesis['file_path'])): ?>
                                    <a href="<?= route('admin/download') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-download" 
                                       title="Download PDF"
                                       target="_blank">
                                         Download
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Comments Button -->
                                <a href="<?= route('admin/comments') ?>&id=<?= $thesis['id'] ?>" 
                                   class="btn btn-comments" 
                                   title="Comments & Feedback"
                                   target="_blank">
                                     Comments
                                </a>
                            </div>
                            
                            <!-- Status Action Buttons -->
                            <div class="status-actions">
                                <?php if ($thesis['status'] !== 'approved'): ?>
                                    <a href="<?= route('admin/approve') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-approve" 
                                       title="Approve for Publication"
                                       onclick="return confirm('Are you sure you want to approve this thesis?')">
                                         Approve
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($thesis['status'] === 'approved'): ?>
                                    <a href="<?= route('admin/reject') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-reject" 
                                       title="Mark for Review"
                                       onclick="return confirm('Mark this thesis for review?')">
                                         Review
                                    </a>
                                <?php else: ?>
                                    <a href="<?= route('admin/reject') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-reject" 
                                       title="Reject Submission"
                                       onclick="return confirm('Are you sure you want to reject this thesis?')">
                                         Reject
                                    </a>
                                <?php endif; ?>

                                <?php
                                // Show delete button for admin and faculty (both can delete any thesis)
                                $currentUser = current_user();
                                $canShowDelete = in_array($currentUser['role'], ['admin', 'faculty']);

                                if ($canShowDelete):
                                    // Different confirmation message for approved vs non-approved
                                    if ($thesis['status'] === 'approved') {
                                        $confirmMsg = "⚠️ WARNING: This is an APPROVED and PUBLISHED thesis!\\n\\nTitle: " . addslashes($thesis['title']) . "\\n\\nDeleting this will remove it from public view and cannot be undone.\\n\\nAre you ABSOLUTELY SURE you want to delete this?";
                                    } else {
                                        $confirmMsg = "Are you sure you want to delete this thesis?\\n\\nTitle: " . addslashes($thesis['title']) . "\\n\\nThis action cannot be undone.";
                                    }
                                ?>
                                    <a href="<?= route('admin/delete') ?>&id=<?= $thesis['id'] ?>"
                                       class="btn btn-delete <?= $thesis['status'] === 'approved' ? 'btn-delete-approved' : '' ?>"
                                       title="Delete Thesis"
                                       onclick="return confirm('<?= $confirmMsg ?>')">
                                         Delete<?= $thesis['status'] === 'approved' ? ' (Published)' : '' ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="thesis-stats">
                            <?php if (!empty($thesis['view_count'])): ?>
                                <span class="stat"> <?= number_format($thesis['view_count']) ?> views</span>
                            <?php endif; ?>
                            <?php if (!empty($thesis['download_count'])): ?>
                                <span class="stat"> <?= number_format($thesis['download_count']) ?> downloads</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($filteredTheses)): ?>
                    <div class="no-data" style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;"></div>
                        <h3>No Results Found</h3>
                        <p>No theses match your current filters.</p>
                        <a href="<?= route('admin/dashboard') ?>" class="btn btn-primary" style="margin-top: 15px;">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <div class="no-data-icon"></div>
                <h3>No Thesis Submissions</h3>
                <p>No thesis submissions have been received yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Enhanced Dashboard Styles */
.page-title {
    color: #d32f2f;
    margin-bottom: 30px;
    font-size: 2.2rem;
    font-weight: 600;
    text-align: center;
}

/* Stats Cards */
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
    border-left: 5px solid;
    transition: all 0.3s ease;
}

.stat-card.submitted { border-left-color: #17a2b8; }
.stat-card.review { border-left-color: #ffc107; }
.stat-card.approved { border-left-color: #28a745; }
.stat-card.rejected { border-left-color: #dc3545; }

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-card h3 {
    font-size: 2rem;
    margin: 0 0 10px 0;
    color: #d32f2f;
}

/* Dashboard Actions */
.dashboard-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Table Container */
.table-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.table-header h3 {
    color: #333;
    margin: 0;
    font-size: 1.5rem;
}

/* Theses Grid */
.theses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.thesis-management-card {
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.thesis-management-card:hover {
    border-color: #d32f2f;
    box-shadow: 0 8px 25px rgba(211, 47, 47, 0.15);
    transform: translateY(-3px);
}

.thesis-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.thesis-title {
    color: #d32f2f;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.thesis-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 0.85rem;
    color: #666;
}

.thesis-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.status-badge .status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-submitted { background: #e3f2fd; color: #1976d2; }
.status-under_review { background: #fff3e0; color: #f57c00; }
.status-approved { background: #e8f5e8; color: #2e7d32; }
.status-rejected { background: #ffebee; color: #d32f2f; }

.thesis-abstract {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

/* Action Buttons */
.thesis-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.primary-actions,
.status-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.thesis-actions .btn {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-view { background: #e3f2fd; color: #1976d2; }
.btn-download { background: #e8f5e8; color: #2e7d32; }
.btn-comments { background: #fff3e0; color: #f57c00; }
.btn-approve { background: #e8f5e8; color: #2e7d32; }
.btn-reject { background: #ffebee; color: #d32f2f; }
.btn-delete { background: #ffcdd2; color: #c62828; }
.btn-delete-approved {
    background: #ff5252 !important;
    color: white !important;
    font-weight: 600;
    border: 2px solid #d32f2f;
    animation: pulse-warning 2s infinite;
}

@keyframes pulse-warning {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255, 82, 82, 0.7); }
    50% { box-shadow: 0 0 0 8px rgba(255, 82, 82, 0); }
}

.thesis-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
}

/* Thesis Stats */
.thesis-stats {
    display: flex;
    gap: 15px;
    font-size: 0.8rem;
    color: #999;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
}

/* General Button Styles */
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
.btn-warning { background: #ffc107; color: #212529; }

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0.9;
}

/* No Data State */
.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-data-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-data h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

/* Alerts */
.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
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

/* System Metrics Section */
.system-metrics-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: 2px solid #dee2e6;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #dee2e6;
}

.section-header h3 {
    color: #d32f2f;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.refresh-indicator {
    background: #28a745;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.metric-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.health-card { border-left-color: #28a745; }
.users-card { border-left-color: #17a2b8; }
.theses-card { border-left-color: #6f42c1; }
.storage-card { border-left-color: #fd7e14; }
.performance-card { border-left-color: #20c997; }
.activity-card { border-left-color: #ffc107; }

.metric-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.metric-icon {
    font-size: 1.8rem;
}

.metric-header h4 {
    color: #495057;
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 15px;
}

.value-success { color: #28a745; }
.value-warning { color: #ffc107; }
.value-error { color: #dc3545; }

.metric-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    padding: 6px 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.detail-item span {
    color: #6c757d;
}

.detail-item strong {
    color: #212529;
}

.text-success { color: #28a745 !important; }
.text-info { color: #17a2b8 !important; }
.text-warning { color: #ffc107 !important; }
.text-error { color: #dc3545 !important; }

/* Recent Activity Section */
.recent-activity-section {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.recent-activity-section h4 {
    color: #495057;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.activity-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.activity-icon.status-submitted { background: #e3f2fd; }
.activity-icon.status-under_review { background: #fff3e0; }
.activity-icon.status-approved { background: #e8f5e8; }
.activity-icon.status-rejected { background: #ffebee; }
.activity-icon.status-draft { background: #f3e5f5; }

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 6px;
    font-size: 0.9rem;
}

.activity-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 0.8rem;
    color: #6c757d;
}

.activity-meta .author {
    font-weight: 500;
}

.activity-meta .time {
    color: #999;
}

.activity-meta .status-badge {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .theses-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-actions {
        justify-content: center;
    }

    .table-header {
        flex-direction: column;
        align-items: stretch;
    }

    .table-filters form {
        justify-content: center;
    }

    .thesis-header {
        flex-direction: column;
        gap: 10px;
    }

    .thesis-meta {
        justify-content: flex-start;
    }

    .metrics-grid {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>