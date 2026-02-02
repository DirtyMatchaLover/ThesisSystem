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
                        <span class="meta-label">Author:</span>
                        <span class="meta-value"><?= htmlspecialchars($thesis['author'] ?? 'Unknown') ?></span>
                    </div>

                    <?php if (!empty($thesis['strand'])): ?>
                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-label">Strand:</span>
                        <span class="meta-value"><?= htmlspecialchars($thesis['strand']) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-label">Year:</span>
                        <span class="meta-value"><?= htmlspecialchars($thesis['academic_year'] ?? 'N/A') ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-label">Views:</span>
                        <span class="meta-value"><?= number_format($thesis['view_count'] ?? 0) ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-icon"></span>
                        <span class="meta-label">Downloads:</span>
                        <span class="meta-value"><?= number_format($thesis['download_count'] ?? 0) ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="status-badge status-<?= strtolower($thesis['status'] ?? 'unknown') ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $thesis['status'] ?? 'unknown'))) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <button onclick="toggleAbstract()" class="btn btn-outline">
                <span class="btn-icon"></span>
                <span id="abstractBtnText">Show Abstract</span>
            </button>

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
/* Thesis View Container */
.thesis-view-container {
    max-width: 1800px;
    margin: 40px auto;
    padding: 0 20px;
}

/* Thesis Header */
.thesis-header {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.thesis-title {
    color: #d32f2f;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.3;
}

.thesis-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.95rem;
}

.meta-icon {
    font-size: 1.2rem;
}

.meta-label {
    color: #666;
    font-weight: 500;
}

.meta-value {
    color: #333;
    font-weight: 600;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-approved { background: #e8f5e9; color: #2e7d32; }
.status-submitted { background: #fff3e0; color: #f57c00; }
.status-under_review { background: #e3f2fd; color: #1976d2; }
.status-rejected { background: #ffebee; color: #d32f2f; }

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
}

.btn-primary {
    background: #d32f2f;
    color: white;
}

.btn-primary:hover {
    background: #b71c1c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

.btn-success {
    background: #4caf50;
    color: white;
}

.btn-success:hover {
    background: #388e3c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-outline {
    background: white;
    color: #d32f2f;
    border: 2px solid #d32f2f;
}

.btn-outline:hover {
    background: #d32f2f;
    color: white;
}

.btn-icon {
    font-size: 1.1rem;
}

/* Abstract Section */
.abstract-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
    color: #333;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.abstract-content {
    color: #555;
    font-size: 1rem;
    line-height: 1.8;
    text-align: justify;
}

/* PDF Viewer Section */
.pdf-viewer-section {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
    background: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
}

.pdf-controls {
    display: flex;
    gap: 10px;
}

.btn-control {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: white;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-control:hover {
    background: #d32f2f;
    color: white;
    transform: scale(1.1);
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
    background: #fff3cd;
    border-top: 1px solid #ffc107;
    text-align: center;
    font-size: 0.9rem;
}

.pdf-fallback a {
    color: #d32f2f;
    font-weight: 600;
    text-decoration: underline;
}

/* No File Message */
.no-file-message {
    background: white;
    border-radius: 12px;
    padding: 60px 30px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.message-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-file-message h3 {
    color: #333;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.no-file-message p {
    color: #666;
    font-size: 1rem;
}

/* Error State */
.error-state {
    background: white;
    border-radius: 12px;
    padding: 60px 30px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.error-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.error-state h2 {
    color: #d32f2f;
    font-size: 1.8rem;
    margin-bottom: 15px;
}

.error-state p {
    color: #666;
    font-size: 1rem;
    margin-bottom: 30px;
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
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
