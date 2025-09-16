<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <?php if (!empty($thesis)): ?>
        <h2 style="color: #d32f2f; margin-bottom: 20px;">
            <?= htmlspecialchars($thesis['title'] ?? 'Untitled Thesis') ?>
        </h2>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <p style="margin-bottom: 10px;">
                <strong>Author:</strong> <?= htmlspecialchars($thesis['author'] ?? 'Unknown') ?>
            </p>
            <p style="margin-bottom: 10px;">
                <strong>Academic Year:</strong> <?= htmlspecialchars($thesis['academic_year'] ?? 'N/A') ?>
            </p>
            <p style="margin-bottom: 10px;">
                <strong>Semester:</strong> <?= htmlspecialchars($thesis['semester'] ?? 'N/A') ?>
            </p>
            <p style="margin-bottom: 0;">
                <strong>Status:</strong> 
                <span style="padding: 4px 8px; border-radius: 4px; font-size: 14px; 
                           background-color: <?php 
                             echo $thesis['status'] === 'approved' ? '#28a745' : 
                                  ($thesis['status'] === 'submitted' ? '#ffc107' : 
                                  ($thesis['status'] === 'under_review' ? '#17a2b8' : '#6c757d')); 
                           ?>; color: white;">
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status'] ?? 'unknown'))) ?>
                </span>
            </p>
        </div>
        
        <div style="margin-bottom: 30px;">
            <h3 style="color: #333; margin-bottom: 15px;">Abstract</h3>
            <div style="background: white; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px; line-height: 1.6;">
                <?= nl2br(htmlspecialchars($thesis['abstract'] ?? 'No abstract available.')) ?>
            </div>
        </div>

        <?php if (!empty($thesis['file_path'])): ?>
            <div style="margin-top: 30px;">
                <a href="<?= htmlspecialchars($thesis['file_path']) ?>" 
                   target="_blank"
                   style="display: inline-block; padding: 12px 24px; background-color: #d32f2f; 
                          color: white; text-decoration: none; border-radius: 4px; 
                          font-weight: 600;">
                    📄 View Full Thesis (PDF)
                </a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="<?= route('thesis/list') ?>" 
               style="color: #d32f2f; text-decoration: underline;">
                ← Back to My Submissions
            </a>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px;">
            <h2 style="color: #dc3545;">⚠️ Thesis not found</h2>
            <p>The thesis you're looking for doesn't exist or you don't have permission to view it.</p>
            <a href="<?= route('home') ?>" 
               style="color: #d32f2f; text-decoration: underline;">
                Go to Home
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>