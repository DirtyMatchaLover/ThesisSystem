<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="thesis-view-container">
    <?php if (!empty($thesis)): ?>
        <!-- Thesis Header -->
        <div class="thesis-header">
            <div class="header-content">
                <h1 class="thesis-title">
                    <?= htmlspecialchars($thesis['title'] ?? 'Untitled Thesis') ?>
                </h1>

                <div class="thesis-meta">
                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-text"><strong>Author:</strong> <?= htmlspecialchars($thesis['author'] ?? 'Unknown') ?></span>
                    </div>

                    <?php if (!empty($thesis['adviser'])): ?>
                    <div class="meta-item">
                        <span class="meta-icon">‍</span>
                        <span class="meta-text"><strong>Adviser:</strong> <?= htmlspecialchars($thesis['adviser']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($thesis['strand'])): ?>
                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-text"><strong>Strand:</strong> <?= htmlspecialchars($thesis['strand']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-text"><strong>Year:</strong> <?= htmlspecialchars($thesis['academic_year'] ?? 'N/A') ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-icon">️</span>
                        <span class="meta-text"><strong>Views:</strong> <?= number_format($thesis['view_count'] ?? 0) ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-text"><strong>Downloads:</strong> <?= number_format($thesis['download_count'] ?? 0) ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="status-badge status-<?= strtolower($thesis['status'] ?? 'unknown') ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status'] ?? 'unknown'))) ?>
                        </span>
                    </div>

                    <?php
                    // Show who approved if thesis is approved
                    if ($thesis['status'] === 'approved' && !empty($thesis['approved_by'])):
                        // Fetch approver name
                        require_once __DIR__ . '/../../models/Database.php';
                        $db = Database::getInstance();
                        $stmt = $db->prepare("SELECT name, role FROM users WHERE id = ?");
                        $stmt->execute([$thesis['approved_by']]);
                        $approver = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($approver):
                    ?>
                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-text"><strong>Approved by:</strong> <?= htmlspecialchars($approver['name']) ?> (<?= htmlspecialchars(ucfirst($approver['role'])) ?>)</span>
                    </div>
                    <?php endif; endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <button onclick="toggleAbstract()" class="btn btn-outline">
                <span class="btn-icon"></span>
                <span id="abstractBtnText">Show Abstract</span>
            </button>

            <button onclick="toggleCitation()" class="btn btn-outline">
                <span class="btn-icon"></span>
                Cite This (APA)
            </button>

            <?php if (is_logged_in()): ?>
                <button onclick="toggleBookmark(<?= $thesis['id'] ?>)"
                        class="btn btn-bookmark <?= is_bookmarked($thesis['id']) ? 'bookmarked' : '' ?>"
                        id="bookmarkBtn">
                    <span class="btn-icon" id="bookmarkIcon"><?= is_bookmarked($thesis['id']) ? '️' : '' ?></span>
                    <span id="bookmarkText"><?= is_bookmarked($thesis['id']) ? 'Saved' : 'Save to Favorites' ?></span>
                </button>
            <?php endif; ?>

            <?php if (!empty($thesis['file_path'])): ?>
                <button onclick="togglePDFViewer()" class="btn btn-primary" id="viewPdfBtn">
                    <span class="btn-icon">️</span>
                    View Full PDF
                </button>

                <a href="<?= route('research/download') ?>&id=<?= $thesis['id'] ?>" class="btn btn-success" download>
                    <span class="btn-icon"></span>
                    Download PDF
                </a>
            <?php endif; ?>

            <a href="<?= isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : route('research') ?>" class="btn btn-secondary">
                <span class="btn-icon">←</span>
                Back
            </a>
        </div>

        <!-- Abstract Section (Hidden by default) -->
        <div class="abstract-section" id="abstractSection" style="display: none;">
            <h3 class="section-title"> Abstract</h3>
            <div class="abstract-content">
                <?= nl2br(htmlspecialchars($thesis['abstract'] ?? 'No abstract available.')) ?>
            </div>
        </div>

        <!-- Citation Section (Hidden by default) -->
        <div class="citation-section" id="citationSection" style="display: none;">
            <h3 class="section-title"> APA Citation</h3>
            <div class="citation-box">
                <div class="citation-text" id="citationText">
                    <?= generate_apa_citation($thesis) ?>
                </div>
                <button onclick="copyCitation()" class="btn-copy" id="copyBtn">
                    <span class="btn-icon"></span>
                    Copy Citation
                </button>
            </div>
            <p class="citation-note">Click "Copy Citation" to copy to your clipboard</p>
        </div>

        <!-- PDF Viewer Section -->
        <?php if (!empty($thesis['file_path'])): ?>
            <div class="pdf-viewer-section" id="pdfViewerSection">
                <div class="pdf-viewer-header">
                    <h3 class="section-title"> Full Thesis Document</h3>
                    <div class="pdf-controls">
                        <button onclick="openFullscreen()" class="btn-control" title="Fullscreen">
                            <span></span>
                        </button>
                        <button onclick="closePDFViewer()" class="btn-control" title="Close">
                            <span></span>
                        </button>
                    </div>
                </div>

                <div class="pdf-viewer-wrapper">
                    <iframe
                        id="pdfIframe"
                        src="<?= htmlspecialchars($thesis['file_path']) ?>#toolbar=1&navpanes=1&scrollbar=1"
                        class="pdf-iframe"
                        frameborder="0"
                    ></iframe>
                </div>

                <div class="pdf-fallback">
                    <p>Can't see the PDF? <a href="<?= htmlspecialchars($thesis['file_path']) ?>" target="_blank">Click here to open in new tab</a></p>
                </div>
            </div>
        <?php else: ?>
            <div class="no-file-message">
                <div class="message-icon"></div>
                <h3>No PDF Available</h3>
                <p>The thesis file hasn't been uploaded yet.</p>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Error State -->
        <div class="error-state">
            <div class="error-icon">️</div>
            <h2>Thesis Not Found</h2>
            <p>The thesis you're looking for doesn't exist or you don't have permission to view it.</p>
            <a href="<?= route('research') ?>" class="btn btn-primary">
                <span class="btn-icon"></span>
                Browse Research Papers
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
/* Thesis View Container - Library Theme */
.thesis-view-container {
    max-width: 1800px;
    margin: 40px auto;
    padding: 0 20px;
}

/* Thesis Header */
.thesis-header {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
}

.thesis-title {
    color: #7b3f00;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.3;
    font-family: 'Georgia', 'Garamond', serif;
}

.thesis-meta {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    font-family: 'Georgia', serif;
}

.meta-icon {
    font-size: 1.2rem;
    line-height: 1;
}

.meta-text {
    color: #2c2416;
    line-height: 1;
}

.meta-text strong {
    color: #8b6f47;
    font-weight: 600;
    margin-right: 4px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    font-family: 'Georgia', serif;
}

.status-approved { background: #e8f5e9; color: #2e7d32; }
.status-submitted { background: rgba(212, 165, 116, 0.3); color: #7b3f00; }
.status-under_review { background: #e3f2fd; color: #1976d2; }
.status-rejected { background: #ffebee; color: #d32f2f; }
.status-draft { background: rgba(139, 111, 71, 0.2); color: #5a2d00; }

/* Action Bar */
.action-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-family: 'Georgia', serif;
}

.btn-primary {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(123, 63, 0, 0.3);
}

.btn-success {
    background: linear-gradient(135deg, #d4a574 0%, #c9955f 100%);
    color: #3d2817;
    border: 2px solid #d4a574;
}

.btn-success:hover {
    background: linear-gradient(135deg, #c9955f 0%, #b88750 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212, 165, 116, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #8b6f47 0%, #6f5635 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #6f5635 0%, #5a4429 100%);
    box-shadow: 0 4px 15px rgba(139, 111, 71, 0.3);
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

.btn-bookmark {
    background: transparent;
    color: #7b3f00;
    border: 2px solid #7b3f00;
}

.btn-bookmark:hover {
    background: rgba(123, 63, 0, 0.1);
    transform: translateY(-2px);
}

.btn-bookmark.bookmarked {
    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
    border-color: #d32f2f;
    color: #d32f2f;
}

.btn-bookmark.bookmarked:hover {
    background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%);
}

.btn-icon {
    font-size: 1.1rem;
}

/* Abstract Section */
.abstract-section {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-title {
    color: #3d2817;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Georgia', serif;
}

.abstract-content {
    color: #2c2416;
    font-size: 1rem;
    line-height: 1.8;
    text-align: justify;
    font-family: 'Georgia', serif;
}

/* Citation Section */
.citation-section {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
    animation: slideDown 0.3s ease;
}

.citation-box {
    background: #fff;
    border: 2px solid #d4a574;
    border-radius: 8px;
    padding: 20px;
    position: relative;
    margin-bottom: 15px;
}

.citation-text {
    color: #2c2416;
    font-size: 1rem;
    line-height: 1.8;
    font-family: 'Georgia', serif;
    padding-right: 140px;
}

.btn-copy {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    border: 2px solid #d4a574;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    font-family: 'Georgia', serif;
}

.btn-copy:hover {
    background: linear-gradient(135deg, #5a2d00 0%, #3d1e00 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(123, 63, 0, 0.3);
}

.btn-copy.copied {
    background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
    border-color: #4caf50;
}

.citation-note {
    color: #8b6f47;
    font-size: 0.85rem;
    font-style: italic;
    margin: 0;
    font-family: 'Georgia', serif;
}

/* PDF Viewer Section */
.pdf-viewer-section {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
    animation: slideDown 0.3s ease;
    /* Full width when showing PDF */
    margin-left: -20px;
    margin-right: -20px;
    width: calc(100% + 40px);
}

.pdf-viewer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    background: linear-gradient(135deg, rgba(212, 165, 116, 0.2) 0%, rgba(139, 111, 71, 0.1) 100%);
    border-bottom: 2px solid #d4a574;
}

.pdf-controls {
    display: flex;
    gap: 10px;
}

.btn-control {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid #d4a574;
    background: #faf8f3;
    color: #7b3f00;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(61, 40, 23, 0.1);
}

.btn-control:hover {
    background: linear-gradient(135deg, #7b3f00 0%, #5a2d00 100%);
    color: #f5e6d3;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(123, 63, 0, 0.3);
}

.pdf-viewer-wrapper {
    position: relative;
    width: 100%;
    height: 1200px;
    background: #525659;
}

.pdf-iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.pdf-fallback {
    padding: 15px 30px;
    background: rgba(212, 165, 116, 0.2);
    border-top: 2px solid #d4a574;
    text-align: center;
    font-size: 0.9rem;
    font-family: 'Georgia', serif;
}

.pdf-fallback a {
    color: #7b3f00;
    font-weight: 600;
    text-decoration: underline;
}

.pdf-fallback a:hover {
    color: #5a2d00;
}

/* No File Message */
.no-file-message {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 60px 30px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
}

.message-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-file-message h3 {
    color: #3d2817;
    font-size: 1.5rem;
    margin-bottom: 10px;
    font-family: 'Georgia', serif;
}

.no-file-message p {
    color: #2c2416;
    font-size: 1rem;
    font-family: 'Georgia', serif;
}

/* Error State */
.error-state {
    background: linear-gradient(135deg, #faf8f3 0%, #f5f1e8 100%);
    border-radius: 12px;
    padding: 60px 30px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(61, 40, 23, 0.15);
    border: 2px solid #d4a574;
}

.error-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.error-state h2 {
    color: #7b3f00;
    font-size: 1.8rem;
    margin-bottom: 15px;
    font-family: 'Georgia', serif;
}

.error-state p {
    color: #2c2416;
    font-size: 1rem;
    margin-bottom: 30px;
    font-family: 'Georgia', serif;
}

/* Responsive Design */
@media (max-width: 768px) {
    .thesis-title {
        font-size: 1.5rem;
    }

    .thesis-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .action-bar {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .pdf-viewer-wrapper {
        height: 500px;
    }

    .pdf-viewer-header {
        flex-direction: column;
        gap: 15px;
    }
}

/* Fullscreen Mode */
.pdf-viewer-section.fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    border-radius: 0;
    margin: 0;
}

.pdf-viewer-section.fullscreen .pdf-viewer-wrapper {
    height: calc(100vh - 80px);
}
</style>

<script>
// Toggle Abstract
function toggleAbstract() {
    const section = document.getElementById('abstractSection');
    const btnText = document.getElementById('abstractBtnText');

    if (section.style.display === 'none') {
        section.style.display = 'block';
        btnText.textContent = 'Hide Abstract';
    } else {
        section.style.display = 'none';
        btnText.textContent = 'Show Abstract';
    }
}

// Toggle Citation
function toggleCitation() {
    const section = document.getElementById('citationSection');

    if (section.style.display === 'none') {
        section.style.display = 'block';
        // Scroll to citation section
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    } else {
        section.style.display = 'none';
    }
}

// Copy Citation to Clipboard
function copyCitation() {
    const citationText = document.getElementById('citationText').innerText;
    const copyBtn = document.getElementById('copyBtn');

    // Create temporary textarea
    const tempTextarea = document.createElement('textarea');
    tempTextarea.value = citationText;
    document.body.appendChild(tempTextarea);
    tempTextarea.select();

    try {
        document.execCommand('copy');

        // Change button text to indicate success
        copyBtn.innerHTML = '<span class="btn-icon"></span> Copied!';
        copyBtn.classList.add('copied');

        // Reset button after 2 seconds
        setTimeout(() => {
            copyBtn.innerHTML = '<span class="btn-icon"></span> Copy Citation';
            copyBtn.classList.remove('copied');
        }, 2000);
    } catch (err) {
        console.error('Failed to copy citation:', err);
        alert('Failed to copy citation. Please select and copy manually.');
    }

    // Remove temporary textarea
    document.body.removeChild(tempTextarea);
}

// Toggle PDF Viewer
function togglePDFViewer() {
    const section = document.getElementById('pdfViewerSection');
    const btn = document.getElementById('viewPdfBtn');

    if (section.style.display === 'none') {
        section.style.display = 'block';
        btn.innerHTML = '<span class="btn-icon">️</span> Hide PDF';

        // Scroll to PDF viewer
        setTimeout(() => {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    } else {
        section.style.display = 'none';
        btn.innerHTML = '<span class="btn-icon">️</span> View Full PDF';
    }
}

// Close PDF Viewer
function closePDFViewer() {
    const section = document.getElementById('pdfViewerSection');
    const btn = document.getElementById('viewPdfBtn');

    section.style.display = 'none';
    btn.innerHTML = '<span class="btn-icon">️</span> View Full PDF';

    // Exit fullscreen if active
    if (section.classList.contains('fullscreen')) {
        section.classList.remove('fullscreen');
    }
}

// Fullscreen Mode
function openFullscreen() {
    const section = document.getElementById('pdfViewerSection');
    const iframe = document.getElementById('pdfIframe');

    section.classList.add('fullscreen');

    // Try native fullscreen API
    if (section.requestFullscreen) {
        section.requestFullscreen();
    } else if (section.webkitRequestFullscreen) {
        section.webkitRequestFullscreen();
    } else if (section.msRequestFullscreen) {
        section.msRequestFullscreen();
    }
}

// Exit fullscreen on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const section = document.getElementById('pdfViewerSection');
        if (section && section.classList.contains('fullscreen')) {
            section.classList.remove('fullscreen');
        }
    }
});

// Auto-show PDF on page load
window.addEventListener('load', function() {
    const section = document.getElementById('pdfViewerSection');
    if (section) {
        // Show PDF by default
        section.style.display = 'block';
    }
});

// Toggle Bookmark
function toggleBookmark(thesisId) {
    const btn = document.getElementById('bookmarkBtn');
    const icon = document.getElementById('bookmarkIcon');
    const text = document.getElementById('bookmarkText');

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
            if (data.is_bookmarked) {
                btn.classList.add('bookmarked');
                icon.textContent = '️';
                text.textContent = 'Saved';
            } else {
                btn.classList.remove('bookmarked');
                icon.textContent = '';
                text.textContent = 'Save to Favorites';
            }

            // Show brief success message
            const originalText = text.textContent;
            text.textContent = data.message;
            setTimeout(() => {
                text.textContent = originalText;
            }, 2000);
        } else {
            alert(data.message || 'Failed to update bookmark');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update bookmark');
    });
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
