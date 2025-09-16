<?php include __DIR__ . '/../layout/header.php'; ?>

<style>
    .comments-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
        background: #f8f9fa;
        min-height: 100vh;
    }

    .thesis-summary {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #d32f2f;
    }

    .thesis-summary h2 {
        color: #d32f2f;
        margin: 0 0 10px 0;
        font-size: 1.3rem;
    }

    .thesis-summary p {
        color: #666;
        margin: 5px 0;
        font-size: 0.9rem;
    }

    .comment-form {
        background: white;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .comment-form h3 {
        color: #333;
        margin: 0 0 20px 0;
        font-size: 1.2rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        font-family: inherit;
        box-sizing: border-box;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .btn {
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-primary { background: #d32f2f; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-info { background: #17a2b8; color: white; }
    
    .btn:hover { 
        opacity: 0.9; 
        transform: translateY(-1px); 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .comments-list {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .comments-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .comments-header h3 {
        margin: 0;
        color: #333;
        font-size: 1.2rem;
    }

    .comment-item {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .comment-item:last-child {
        border-bottom: none;
    }

    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .comment-author {
        font-weight: 600;
        color: #333;
    }

    .comment-date {
        color: #666;
        font-size: 0.9rem;
    }

    .comment-type {
        display: inline-block;
        padding: 4px 8px;
        background: #e9ecef;
        color: #495057;
        border-radius: 4px;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
        margin-left: 10px;
    }

    .comment-type.feedback { background: #cce7ff; color: #004085; }
    .comment-type.revision_request { background: #fff3cd; color: #856404; }
    .comment-type.approval { background: #d4edda; color: #155724; }
    .comment-type.rejection { background: #f8d7da; color: #721c24; }

    .comment-content {
        color: #555;
        line-height: 1.6;
        margin-top: 10px;
    }

    .no-comments {
        padding: 40px;
        text-align: center;
        color: #666;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-size: 0.95rem;
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

    .button-group {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    @media (max-width: 768px) {
        .comments-container {
            padding: 10px;
        }

        .comment-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .button-group {
            justify-content: center;
        }

        .btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
    }
</style>

<div class="comments-container">
    <!-- Flash Messages -->
    <?php if (has_flash('success')): ?>
        <div class="alert alert-success">
            ✅ <?= htmlspecialchars(get_flash('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (has_flash('error')): ?>
        <div class="alert alert-error">
            ❌ <?= htmlspecialchars(get_flash('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Thesis Summary -->
    <div class="thesis-summary">
        <h2><?= htmlspecialchars($thesis['title'] ?? 'Thesis Comments') ?></h2>
        <p><strong>Author:</strong> <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown') ?></p>
        <p><strong>Status:</strong> 
            <span class="comment-type status-<?= $thesis['status'] ?>">
                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status']))) ?>
            </span>
        </p>
        <p><strong>Submitted:</strong> <?= date('F j, Y', strtotime($thesis['created_at'] ?? 'now')) ?></p>
        
        <div class="button-group">
            <a href="<?= route('admin/pdfview') ?>&id=<?= $thesis['id'] ?>" class="btn btn-primary" target="_blank">
                👁 View PDF
            </a>
            <a href="<?= route('admin/dashboard') ?>" class="btn btn-secondary">
                ← Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Add Comment Form -->
    <div class="comment-form">
        <h3>💬 Add Comment or Feedback</h3>
        
        <form method="POST">
            <div class="form-group">
                <label for="type">Comment Type</label>
                <select name="type" id="type">
                    <option value="feedback">General Feedback</option>
                    <option value="revision_request">Revision Request</option>
                    <option value="approval">Approval Note</option>
                    <option value="rejection">Rejection Reason</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="comment">Your Comment</label>
                <textarea name="comment" id="comment" required 
                          placeholder="Enter your comment or feedback here..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">
                💬 Add Comment
            </button>
        </form>
    </div>

    <!-- Comments List -->
    <div class="comments-list">
        <div class="comments-header">
            <h3>📝 Comments & Feedback (<?= count($comments ?? []) ?>)</h3>
        </div>
        
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <div class="comment-header">
                        <div>
                            <span class="comment-author">
                                <?= htmlspecialchars($comment['user_name'] ?? 'Unknown User') ?>
                            </span>
                            <span class="comment-type <?= $comment['comment_type'] ?? 'feedback' ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $comment['comment_type'] ?? 'feedback')) ?>
                            </span>
                        </div>
                        <span class="comment-date">
                            <?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'] ?? 'now')) ?>
                        </span>
                    </div>
                    <div class="comment-content">
                        <?= nl2br(htmlspecialchars($comment['content'] ?? '')) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-comments">
                <div style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;">💭</div>
                <h4>No Comments Yet</h4>
                <p>Be the first to add feedback or comments about this thesis.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <?php if (current_user()['role'] === 'admin' || current_user()['role'] === 'faculty'): ?>
    <div class="comment-form" style="margin-top: 25px;">
        <h3>⚡ Quick Actions</h3>
        <div class="button-group">
            <?php if ($thesis['status'] !== 'approved'): ?>
                <a href="<?= route('admin/approve') ?>&id=<?= $thesis['id'] ?>" 
                   class="btn btn-success"
                   onclick="return confirm('Approve this thesis for publication?')">
                    ✅ Approve Thesis
                </a>
            <?php endif; ?>
            
            <?php if ($thesis['status'] !== 'rejected'): ?>
                <a href="<?= route('admin/reject') ?>&id=<?= $thesis['id'] ?>" 
                   class="btn" style="background: #dc3545; color: white;"
                   onclick="return confirm('Reject this thesis?')">
                    ❌ Reject Thesis
                </a>
            <?php endif; ?>
            
            <?php if (!empty($thesis['file_path'])): ?>
                <a href="<?= route('admin/download') ?>&id=<?= $thesis['id'] ?>" class="btn btn-info">
                    📥 Download PDF
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>