<?php
// Simplified thesis view for debugging
if (!isset($thesis)) {
    $thesis = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Thesis</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .error { color: red; background: #ffebee; padding: 20px; border-radius: 5px; }
        .info { background: #f5f5f5; padding: 20px; border-radius: 5px; margin: 20px 0; }
        iframe { width: 100%; height: 800px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thesis View (Debug Mode)</h1>

        <?php if ($thesis === null): ?>
            <div class="error">
                <h2>Error: Thesis Not Found</h2>
                <p>The thesis data could not be loaded.</p>
                <p><a href="index.php?route=research">Back to Research</a></p>
            </div>
        <?php else: ?>
            <div class="info">
                <h2><?= htmlspecialchars($thesis['title'] ?? 'No Title') ?></h2>
                <p><strong>Author:</strong> <?= htmlspecialchars($thesis['author'] ?? 'Unknown') ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($thesis['status'] ?? 'Unknown') ?></p>

                <?php if (!empty($thesis['file_path'])): ?>
                    <p><strong>File Path:</strong> <code><?= htmlspecialchars($thesis['file_path']) ?></code></p>

                    <h3>PDF Viewer:</h3>
                    <iframe src="<?= htmlspecialchars($thesis['file_path']) ?>" id="pdfFrame"></iframe>

                    <script>
                    document.getElementById('pdfFrame').onerror = function() {
                        alert('Error loading PDF. File path: <?= htmlspecialchars($thesis['file_path']) ?>');
                    };
                    </script>

                    <p>
                        <a href="<?= htmlspecialchars($thesis['file_path']) ?>" target="_blank">Open PDF in new tab</a> |
                        <a href="index.php?route=research">Back to Research</a>
                    </p>
                <?php else: ?>
                    <div class="error">
                        <p>No PDF file available for this thesis.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
