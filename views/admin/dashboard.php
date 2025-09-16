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

    <!-- Action Buttons -->
    <div class="dashboard-actions">
        <a href="<?= route('admin/reports') ?>" class="btn btn-info">
            📈 View Reports
        </a>
        <?php if (current_user()['role'] === 'admin'): ?>
            <a href="<?= route('admin/users') ?>" class="btn btn-secondary">
                👥 Manage Users
            </a>
        <?php endif; ?>
        <a href="<?= route('admin/dashboard') ?>" class="btn btn-warning">
            🔄 Refresh
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
                    
                    <button type="submit" class="btn btn-info" style="padding: 8px 12px;">🔍 Filter</button>
                    
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
                                    <span class="author">👤 <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown') ?></span>
                                    <span class="date">📅 <?= date('M j, Y', strtotime($thesis['created_at'] ?? 'now')) ?></span>
                                    <?php if (!empty($thesis['strand'])): ?>
                                        <span class="strand">🎓 <?= htmlspecialchars($thesis['strand']) ?></span>
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
                                    👁 View PDF
                                </a>
                                
                                <!-- Download Button -->
                                <?php if (!empty($thesis['file_path'])): ?>
                                    <a href="<?= route('admin/download') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-download" 
                                       title="Download PDF"
                                       target="_blank">
                                        📥 Download
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Comments Button -->
                                <a href="<?= route('admin/comments') ?>&id=<?= $thesis['id'] ?>" 
                                   class="btn btn-comments" 
                                   title="Comments & Feedback"
                                   target="_blank">
                                    💬 Comments
                                </a>
                            </div>
                            
                            <!-- Status Action Buttons -->
                            <div class="status-actions">
                                <?php if ($thesis['status'] !== 'approved'): ?>
                                    <a href="<?= route('admin/approve') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-approve" 
                                       title="Approve for Publication"
                                       onclick="return confirm('Are you sure you want to approve this thesis?')">
                                        ✅ Approve
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($thesis['status'] === 'approved'): ?>
                                    <a href="<?= route('admin/reject') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-reject" 
                                       title="Mark for Review"
                                       onclick="return confirm('Mark this thesis for review?')">
                                        🔄 Review
                                    </a>
                                <?php else: ?>
                                    <a href="<?= route('admin/reject') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-reject" 
                                       title="Reject Submission"
                                       onclick="return confirm('Are you sure you want to reject this thesis?')">
                                        ❌ Reject
                                    </a>
                                <?php endif; ?>

                                <?php if (current_user()['role'] === 'admin'): ?>
                                    <a href="<?= route('admin/delete') ?>&id=<?= $thesis['id'] ?>" 
                                       class="btn btn-delete" 
                                       title="Delete Thesis"
                                       onclick="return confirm('Are you sure you want to delete this thesis? This action cannot be undone.')">
                                        🗑 Delete
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="thesis-stats">
                            <?php if (!empty($thesis['view_count'])): ?>
                                <span class="stat">👁 <?= number_format($thesis['view_count']) ?> views</span>
                            <?php endif; ?>
                            <?php if (!empty($thesis['download_count'])): ?>
                                <span class="stat">📥 <?= number_format($thesis['download_count']) ?> downloads</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($filteredTheses)): ?>
                    <div class="no-data" style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">🔍</div>
                        <h3>No Results Found</h3>
                        <p>No theses match your current filters.</p>
                        <a href="<?= route('admin/dashboard') ?>" class="btn btn-primary" style="margin-top: 15px;">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-data">
                <div class="no-data-icon">📄</div>
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
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>