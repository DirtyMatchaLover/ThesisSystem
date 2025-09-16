<?php
// Initialize variables if not set
$approvedTheses = $approvedTheses ?? [];
$latestTheses = $latestTheses ?? [];
$stats = $stats ?? [
    'total_approved' => 0,
    'total_authors' => 0,
    'current_year' => 0,
    'academic_year' => date('Y') . '-' . (date('Y') + 1)
];
?>

<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/navigation.php'; ?>

<div class="main-container">
    <div class="home-container">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <h1 class="welcome-title">Welcome to</h1>
            <h2 class="welcome-subtitle">Thesis Flow</h2>
            
            <!-- Statistics Section -->
            <?php if (($stats['total_approved'] ?? 0) > 0): ?>
                <div class="stats-summary">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['total_approved'] ?? 0) ?></span>
                        <span class="stat-label">Published Papers</span>
                    </div>
                    <div class="stat-divider">•</div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['total_authors'] ?? 0) ?></span>
                        <span class="stat-label">Authors</span>
                    </div>
                    <div class="stat-divider">•</div>
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($stats['current_year'] ?? 0) ?></span>
                        <span class="stat-label">This Year</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Latest Thesis Papers Section -->
        <section class="latest-section">
            <?php if (!empty($latestTheses)): ?>
                <h3 class="section-title">Latest Thesis Papers</h3>
                
                <div class="thesis-grid">
                    <?php foreach ($latestTheses as $index => $thesis): ?>
                        <div class="thesis-card" onclick="location.href='<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?? 0 ?>'">
                            <!-- Thesis Image/Icon -->
                            <div class="thesis-image">
                                <div class="thesis-type-badge">
                                    <?= file_icon($thesis['file_path'] ?? '') ?>
                                </div>
                            </div>
                            
                            <!-- Thesis Content -->
                            <div class="thesis-content">
                                <h4 class="thesis-title">
                                    <?= htmlspecialchars(str_limit($thesis['title'] ?? 'Untitled', 60)) ?>
                                </h4>
                                
                                <div class="thesis-meta">
                                    <span class="thesis-author">
                                        <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown Author') ?>
                                    </span>
                                    <span class="thesis-date">
                                        <?= format_date($thesis['created_at'] ?? $thesis['publication_date'] ?? null) ?>
                                    </span>
                                </div>

                                <?php if (!empty($thesis['abstract'])): ?>
                                    <div class="thesis-abstract">
                                        <?= htmlspecialchars(str_limit($thesis['abstract'], 100)) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Visual lines for design -->
                                <div class="thesis-lines">
                                    <?php 
                                    // Generate different line patterns for visual variety
                                    $patterns = [
                                        ['long', 'medium', 'long', 'short', 'medium'],
                                        ['medium', 'long', 'short', 'long', 'medium'],
                                        ['long', 'short', 'medium', 'long', 'short'],
                                        ['short', 'long', 'medium', 'long', 'short']
                                    ];
                                    $pattern = $patterns[$index % 4];
                                    ?>
                                    <?php foreach ($pattern as $lineType): ?>
                                        <div class="thesis-line <?= $lineType ?>"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- View Button -->
                            <button class="play-button" aria-label="View thesis">
                                <span class="play-icon">👁</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- View More Button -->
                <?php if (count($approvedTheses) > count($latestTheses)): ?>
                    <div class="view-more-section">
                        <a href="<?= route('research') ?>" class="btn-view-more">
                            View All <?= count($approvedTheses) ?> Papers
                            <span class="view-more-icon">→</span>
                        </a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3 class="empty-title">No Published Papers Yet</h3>
                    <p class="empty-description">
                        Be the first to contribute to the PCC research repository!
                        <br><br>
                        <?php if (is_logged_in()): ?>
                            <a href="<?= route('thesis/create') ?>" class="btn btn-primary">
                                Submit Your Thesis
                            </a>
                        <?php else: ?>
                            <a href="<?= route('auth/select') ?>" class="btn btn-primary">
                                Get Started
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Call to Action Section -->
        <?php if (!empty($latestTheses)): ?>
            <section class="cta-section">
                <div class="cta-content">
                    <h3>Share Your Research</h3>
                    <p>Join our growing community of researchers and contribute to academic knowledge.</p>
                    
                    <div class="cta-buttons">
                        <?php if (is_logged_in()): ?>
                            <a href="<?= route('thesis/create') ?>" class="btn btn-primary">
                                Upload Your Thesis
                            </a>
                            <a href="<?= route('research') ?>" class="btn btn-secondary">
                                Browse Research
                            </a>
                        <?php else: ?>
                            <a href="<?= route('auth/select') ?>" class="btn btn-primary">
                                Get Started
                            </a>
                            <a href="<?= route('research') ?>" class="btn btn-secondary">
                                Explore Papers
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<style>
/* Enhanced Home Page Styles */

/* Statistics Summary */
.stats-summary {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    gap: 20px;
    font-size: 14px;
    opacity: 0.9;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #d32f2f;
    line-height: 1;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-divider {
    color: #ccc;
    font-size: 20px;
}

/* Enhanced Thesis Cards */
.thesis-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(211, 47, 47, 0.1);
    color: #d32f2f;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 500;
}

.thesis-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 8px 0;
    font-size: 12px;
    color: #666;
    gap: 10px;
}

.thesis-author {
    font-weight: 500;
    color: #d32f2f;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.thesis-date {
    white-space: nowrap;
    font-size: 11px;
    color: #999;
}

.thesis-abstract {
    font-size: 12px;
    color: #777;
    line-height: 1.4;
    margin: 10px 0;
    max-height: 40px;
    overflow: hidden;
}

.play-button {
    background: #d32f2f;
    border: 2px solid #d32f2f;
}

.play-button:hover {
    background: #b71c1c;
    border-color: #b71c1c;
}

.play-icon {
    font-size: 14px;
}

/* View More Section */
.view-more-section {
    text-align: center;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e0e0e0;
}

.btn-view-more {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 30px;
    background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(211, 47, 47, 0.2);
}

.btn-view-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(211, 47, 47, 0.3);
    text-decoration: none;
    color: white;
}

.view-more-icon {
    font-size: 18px;
    transition: transform 0.3s ease;
}

.btn-view-more:hover .view-more-icon {
    transform: translateX(5px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    margin-top: 20px;
}

.empty-icon {
    font-size: 72px;
    margin-bottom: 25px;
    opacity: 0.6;
}

.empty-title {
    font-size: 2rem;
    color: #333;
    margin-bottom: 15px;
    font-weight: 600;
}

.empty-description {
    font-size: 16px;
    color: #666;
    line-height: 1.6;
    max-width: 500px;
    margin: 0 auto;
}

/* Call to Action Section */
.cta-section {
    background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
    color: white;
    padding: 50px 30px;
    border-radius: 12px;
    text-align: center;
    margin-top: 50px;
}

.cta-content h3 {
    font-size: 2rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.cta-content p {
    font-size: 18px;
    opacity: 0.9;
    margin-bottom: 30px;
    line-height: 1.6;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.cta-buttons .btn {
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 150px;
}

.cta-buttons .btn-primary {
    background: white;
    color: #d32f2f;
    border: 2px solid white;
}

.cta-buttons .btn-primary:hover {
    background: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.cta-buttons .btn-secondary {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.cta-buttons .btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-summary {
        flex-direction: column;
        gap: 15px;
    }
    
    .stats-summary .stat-divider {
        display: none;
    }
    
    .thesis-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .empty-icon {
        font-size: 56px;
    }
    
    .empty-title {
        font-size: 1.5rem;
    }
    
    .cta-content h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .thesis-abstract {
        display: none; /* Hide abstract on very small screens */
    }
    
    .thesis-title {
        font-size: 1rem;
        line-height: 1.3;
    }
}
</style>

<?php include __DIR__ . '/layout/footer.php'; ?>