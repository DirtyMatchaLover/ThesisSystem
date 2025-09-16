<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #f5f5f5;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .pdf-viewer-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .thesis-info-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-left: 5px solid #d32f2f;
    }

    .thesis-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .thesis-details h1 {
        color: #d32f2f;
        margin: 0 0 15px 0;
        font-size: 1.8rem;
        line-height: 1.3;
    }

    .thesis-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        color: #666;
        font-size: 0.95rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .meta-item strong {
        color: #333;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-submitted { background: #e3f2fd; color: #1976d2; }
    .status-under_review { background: #fff3e0; color: #f57c00; }
    .status-approved { background: #e8f5e8; color: #2e7d32; }
    .status-rejected { background: #ffebee; color: #d32f2f; }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary { background: #d32f2f; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-info { background: #17a2b8; color: white; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-danger { background: #dc3545; color: white; }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        opacity: 0.9;
    }

    .pdf-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .pdf-toolbar {
        background: #f8f9fa;
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: between;
        align-items: center;
        font-size: 0.9rem;
        color: #666;
    }

    .pdf-frame {
        width: 100%;
        height: 80vh;
        border: none;
        background: white;
    }

    .abstract-section {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .abstract-section h3 {
        color: #333;
        margin: 0 0 15px 0;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .abstract-content {
        color: #555;
        line-height: 1.7;
        text-align: justify;
        font-size: 1rem;
    }

    .no-pdf {
        background: white;
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        color: #666;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .no-pdf-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .admin-actions {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #ffc107;
    }

    .admin-actions h3 {
        margin: 0 0 15px 0;
        color: #333;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    @media (max-width: 768px) {
        .pdf-viewer-container {
            padding: 10px;
        }

        .thesis-header {
            flex-direction: column;
        }

        .thesis-meta {
            grid-template-columns: 1fr;
        }

        .pdf-frame {
            height: 60vh;
        }

        .action-buttons {
            width: 100%;
            justify-content: center;
        }

        .btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
    }
</style>

<div class="pdf-viewer-container">
    <?php if ($thesis): ?>
        <!-- Thesis Information Card -->
        <div class="thesis-info-card">
            <div class="thesis-header">
                <div class="thesis-details">
                    <h1><?= htmlspecialchars($thesis['title'] ?? 'Untitled Thesis') ?></h1>
                    
                    <div class="thesis-meta">
                        <div class="meta-item">
                            <span>👤</span>
                            <strong>Author:</strong> <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown Author') ?>
                        </div>
                        
                        <div class="meta-item">
                            <span>📅</span>
                            <strong>Submitted:</strong> <?= date('F j, Y', strtotime($thesis['created_at'] ?? 'now')) ?>
                        </div>
                        
                        <div class="meta-item">
                            <span>📊</span>
                            <strong>Status:</strong> 
                            <span class="status-badge status-<?= $thesis['status'] ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status'] ?? 'unknown'))) ?>
                            </span>
                        </div>

                        <?php if (!empty($thesis['academic_year'])): ?>
                        <div class="meta-item">
                            <span>🎓</span>
                            <strong>Academic Year:</strong> <?= htmlspecialchars($thesis['academic_year']) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($thesis['strand'])): ?>
                        <div class="meta-item">
                            <span>📚</span>
                            <strong>Strand:</strong> <?= htmlspecialchars($thesis['strand']) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($thesis['view_count']) || !empty($thesis['download_count'])): ?>
                        <div class="meta-item">
                            <span>📈</span>
                            <strong>Stats:</strong> 
                            <?= $thesis['view_count'] ?? 0 ?> views, <?= $thesis['download_count'] ?? 0 ?> downloads
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="<?= route('admin/dashboard') ?>" class="btn btn-secondary">
                        ← Back to Dashboard
                    </a>
                    
                    <?php if (!empty($thesis['file_path'])): ?>
                        <a href="<?= route('admin/download') ?>&id=<?= $thesis['id'] ?>" class="btn btn-success">
                            📥 Download PDF
                        </a>
                        
                        <a href="<?= route('admin/viewpdf') ?>&id=<?= $thesis['id'] ?>" target="_blank" class="btn btn-info">
                            🔗 Open PDF in New Tab
                        </a>
                    <?php endif; ?>

                    <a href="<?= route('admin/comments') ?>&id=<?= $thesis['id'] ?>" class="btn btn-warning" target="_blank">
                        💬 Comments
                    </a>
                </div>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="pdf-container">
            <?php if (!empty($thesis['file_path'])): ?>
                <div class="pdf-toolbar">
                    <span>📄 PDF Viewer - <?= htmlspecialchars($thesis['original_filename'] ?? 'thesis.pdf') ?></span>
                </div>
                
                <iframe src="<?= route('admin/viewpdf') ?>&id=<?= $thesis['id'] ?>" 
                        class="pdf-frame"
                        title="Thesis PDF">
                    <div style="padding: 40px; text-align: center; color: #666;">
                        <div style="font-size: 48px; margin-bottom: 20px; opacity: 0.5;">📄</div>
                        <h3>PDF Viewer Not Supported</h3>
                        <p>Your browser doesn't support embedded PDF viewing.</p>
                        <a href="<?= route('admin/download') ?>&id=<?= $thesis['id'] ?>" class="btn btn-primary">
                            📥 Download PDF Instead
                        </a>
                    </div>
                </iframe>
            <?php else: ?>
                <div class="no-pdf">
                    <div class="no-pdf-icon">📄</div>
                    <h3>No PDF Available</h3>
                    <p>This thesis doesn't have an associated PDF file.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Abstract Section -->
        <?php if (!empty($thesis['abstract'])): ?>
        <div class="abstract-section">
            <h3>📝 Abstract</h3>
            <div class="abstract-content">
                <?= nl2br(htmlspecialchars($thesis['abstract'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Admin Actions (for admin/faculty users) -->
        <?php if (current_user()['role'] === 'admin' || current_user()['role'] === 'faculty'): ?>
        <div class="admin-actions">
            <h3>🛠️ Admin Actions</h3>
            <div class="action-buttons">
                <?php if ($thesis['status'] !== 'approved'): ?>
                    <a href="<?= route('admin/approve') ?>&id=<?= $thesis['id'] ?>" 
                       class="btn btn-success"
                       onclick="return confirm('Approve this thesis for publication?')">
                        ✅ Approve Thesis
                    </a>
                <?php endif; ?>
                
                <?php if ($thesis['status'] !== 'rejected'): ?>
                    <a href="<?= route('admin/reject') ?>&id=<?= $thesis['id'] ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Reject this thesis?')">
                        ❌ Reject Thesis
                    </a>
                <?php endif; ?>
                
                <?php if (current_user()['role'] === 'admin'): ?>
                    <a href="<?= route('admin/delete') ?>&id=<?= $thesis['id'] ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this thesis? This action cannot be undone.')">
                        🗑️ Delete Thesis
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Error State -->
        <div class="no-pdf">
            <div class="no-pdf-icon">⚠️</div>
            <h3>Thesis Not Found</h3>
            <p>The thesis you're looking for doesn't exist or you don't have permission to view it.</p>
            <a href="<?= route('admin/dashboard') ?>" class="btn btn-primary">
                ← Back to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>