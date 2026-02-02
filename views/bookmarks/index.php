<?php
$user = current_user();
$bookmarks = $bookmarks ?? [];
?>

<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="main-container">
    <div class="bookmarks-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">️ My Favorites</h1>
            <p class="page-subtitle">
                Bookmarked theses you want to read later
                <?php if (count($bookmarks) > 0): ?>
                    <span class="results-summary">(<?= count($bookmarks) ?> saved)</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Bookmarks Grid -->
        <?php if (!empty($bookmarks)): ?>
            <div class="thesis-grid">
                <?php foreach ($bookmarks as $thesis): ?>
                    <div class="thesis-card">
                        <div class="card-header">
                            <span class="strand-badge strand-<?= strtolower($thesis['strand'] ?? 'general') ?>">
                                <?= htmlspecialchars($thesis['strand'] ?? 'General') ?>
                            </span>
                            <button class="bookmark-btn bookmarked"
                                    onclick="toggleBookmark(<?= $thesis['id'] ?>, this)"
                                    title="Remove from favorites">
                                ️
                            </button>
                        </div>

                        <div class="card-content" onclick="location.href='<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?>'">
                            <h3 class="thesis-title">
                                <?= htmlspecialchars($thesis['title'] ?? 'Untitled') ?>
                            </h3>

                            <div class="thesis-meta-small">
                                <span> <?= htmlspecialchars($thesis['author'] ?? 'Unknown') ?></span>
                                <span> <?= date('M Y', strtotime($thesis['created_at'] ?? 'now')) ?></span>
                            </div>

                            <?php if (!empty($thesis['abstract'])): ?>
                                <div class="thesis-abstract">
                                    <?= htmlspecialchars(substr($thesis['abstract'], 0, 150)) ?>
                                    <?= strlen($thesis['abstract']) > 150 ? '...' : '' ?>
                                </div>
                            <?php endif; ?>

                            <div class="bookmark-date">
                                Saved: <?= format_datetime($thesis['bookmarked_at']) ?>
                            </div>
                        </div>

                        <div class="card-footer">
                            <a href="<?= route('thesis/show') ?>&id=<?= $thesis['id'] ?>" class="btn btn-outline btn-small">
                                 Read More
                            </a>
                            <?php if (!empty($thesis['file_path'])): ?>
                                <a href="<?= route('research/download') ?>&id=<?= $thesis['id'] ?>"
                                   class="btn btn-primary btn-small">
                                     PDF
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">️</div>
                <h3>No Favorites Yet</h3>
                <p>
                    Start bookmarking theses you want to read later.<br>
                    Click the heart icon ️ on any thesis to add it to your favorites!
                </p>
                <a href="<?= route('research') ?>" class="btn btn-primary">
                     Browse Research Papers
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.bookmarks-container {
    max-width: 1400px;
    margin: 40px auto;
    padding: 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    color: #3d2817;
    font-weight: 600;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.page-subtitle {
    color: #8b6f47;
    font-size: 1.1rem;
    font-family: 'Georgia', serif;
}

.results-summary {
    font-weight: 600;
    color: #7b3f00;
}

.thesis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.thesis-card {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.thesis-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(123, 63, 0, 0.25);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.strand-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.strand-stem { background: #e3f2fd; color: #1976d2; }
.strand-abm { background: #f3e5f5; color: #7b1fa2; }
.strand-humss { background: #fff3e0; color: #f57c00; }
.strand-general { background: rgba(212, 165, 116, 0.3); color: #7b3f00; }

.bookmark-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 5px;
}

.bookmark-btn:hover {
    transform: scale(1.2);
}

.bookmark-btn.bookmarked {
    color: #d32f2f;
}

.card-content {
    flex: 1;
    cursor: pointer;
}

.thesis-title {
    font-size: 1.2rem;
    color: #3d2817;
    font-weight: 600;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
    line-height: 1.4;
}

.thesis-meta-small {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #8b6f47;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.thesis-abstract {
    color: #2c2416;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.bookmark-date {
    font-size: 0.8rem;
    color: #8b6f47;
    font-style: italic;
    margin-top: 10px;
    font-family: 'Georgia', serif;
}

.card-footer {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #d4a574;
}

.btn-small {
    padding: 8px 16px;
    font-size: 0.85rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    font-family: 'Georgia', serif;
    flex: 1;
    text-align: center;
}

.btn-outline {
    background: transparent;
    color: #7b3f00;
    border: 2px solid #7b3f00;
}

.btn-outline:hover {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
}

.btn-primary {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    transform: translateY(-2px);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    border: 2px solid #d4a574;
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 20px;
    opacity: 0.6;
}

.empty-state h3 {
    font-size: 1.8rem;
    color: #7b3f00;
    margin-bottom: 15px;
    font-family: 'Georgia', serif;
}

.empty-state p {
    font-size: 1.1rem;
    color: #2c2416;
    margin-bottom: 30px;
    line-height: 1.6;
    font-family: 'Georgia', serif;
}

.btn {
    display: inline-block;
    padding: 15px 30px;
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    border: 2px solid #d4a574;
    transition: all 0.3s ease;
    font-family: 'Georgia', serif;
}

.btn:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(123, 63, 0, 0.3);
    color: #f5e6d3;
}

@media (max-width: 768px) {
    .thesis-grid {
        grid-template-columns: 1fr;
    }

    .page-title {
        font-size: 2rem;
    }
}
</style>

<script>
function toggleBookmark(thesisId, button) {
    event.stopPropagation(); // Prevent card click

    fetch('<?= route('bookmark/toggle') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'thesis_id=' + thesisId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the card from the page
            button.closest('.thesis-card').style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                button.closest('.thesis-card').remove();

                // Check if no more bookmarks
                const grid = document.querySelector('.thesis-grid');
                if (grid && grid.children.length === 0) {
                    location.reload(); // Reload to show empty state
                }
            }, 300);
        } else {
            alert(data.message || 'Failed to update bookmark');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update bookmark');
    });
}

// Add fade out animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.9); }
    }
`;
document.head.appendChild(style);
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
