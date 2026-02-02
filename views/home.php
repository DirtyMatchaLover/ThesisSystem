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
        <!-- Latest Thesis Papers Header -->
        <div class="page-header">
            <h1 class="page-title">Latest Thesis Papers</h1>

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

        <!-- Thesis Papers Section -->
        <section class="latest-section">
            <?php if (!empty($latestTheses)): ?>
                <div class="thesis-grid">
                    <?php foreach ($latestTheses as $index => $thesis): ?>
                        <div class="thesis-card" onclick="location.href='<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?? 0 ?>'">
                            <!-- Thesis Content -->
                            <div class="thesis-content">
                                <h4 class="thesis-title">
                                    <?= htmlspecialchars($thesis['title'] ?? 'Untitled') ?>
                                </h4>

                                <div class="thesis-meta">
                                    <span class="thesis-author">
                                        <strong>Author:</strong> <?= htmlspecialchars($thesis['author'] ?? $thesis['author_name'] ?? 'Unknown Author') ?>
                                    </span>
                                    <span class="thesis-date">
                                        <strong>Date:</strong> <?= format_date($thesis['created_at'] ?? $thesis['publication_date'] ?? null) ?>
                                    </span>
                                    <span class="thesis-views">
                                        <strong>Views:</strong> <?= number_format($thesis['view_count'] ?? 0) ?>
                                    </span>
                                    <span class="thesis-downloads">
                                        <strong>Downloads:</strong> <?= number_format($thesis['download_count'] ?? 0) ?>
                                    </span>
                                </div>

                                <?php if (!empty($thesis['abstract'])): ?>
                                    <div class="thesis-abstract">
                                        <strong>Abstract:</strong><br>
                                        <?= htmlspecialchars($thesis['abstract']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Read Button -->
                            <button class="play-button" aria-label="Read thesis">
                                <span class="play-text">READ</span>
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
                        Be the first to contribute to the ResearchHub repository!
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

<?php include __DIR__ . '/layout/footer.php'; ?>