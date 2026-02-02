<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($thesis['title'] ?? 'PDF Viewer') ?> - PCC ThesisHub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #2c2c2c;
            overflow: hidden;
        }

        .viewer-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Top Toolbar */
        .top-toolbar {
            background: #323232;
            color: white;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            z-index: 100;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .thesis-info {
            display: flex;
            flex-direction: column;
        }

        .thesis-title-small {
            color: white;
            font-size: 0.95rem;
            font-weight: 600;
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .thesis-meta-small {
            color: #aaa;
            font-size: 0.8rem;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-btn {
            background: #444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease;
        }

        .toolbar-btn:hover {
            background: #555;
        }

        .toolbar-btn.close-btn {
            background: #d32f2f;
        }

        .toolbar-btn.close-btn:hover {
            background: #b71c1c;
        }

        /* Search Highlight Bar */
        .highlight-bar {
            background: #fff3e0;
            padding: 10px 20px;
            border-bottom: 2px solid #ff9800;
            display: none;
            align-items: center;
            gap: 15px;
        }

        .highlight-bar.active {
            display: flex;
        }

        .highlight-info {
            color: #e65100;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .highlight-term {
            background: #ffeb3b;
            padding: 4px 8px;
            border-radius: 4px;
            color: #000;
            font-weight: 700;
        }

        .highlight-controls {
            display: flex;
            gap: 8px;
        }

        .highlight-btn {
            background: #ff9800;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.2s ease;
        }

        .highlight-btn:hover {
            background: #f57c00;
        }

        .highlight-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* PDF Viewer Area */
        .pdf-viewer {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .pdf-container {
            width: 100%;
            height: 100%;
            overflow: auto;
            background: #525659;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .pdf-page {
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            position: relative;
            background: white;
            padding: 0;
        }

        canvas {
            display: block;
            margin: 0;
            padding: 0;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .text-layer {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            opacity: 1;
            line-height: 1;
            z-index: 2;
            pointer-events: auto;
        }

        .text-layer > span,
        .text-layer > div {
            color: transparent;
            position: absolute;
            white-space: pre;
            cursor: text;
            transform-origin: 0 0;
            user-select: text;
        }

        .text-layer .highlight {
            background-color: rgba(255, 255, 0, 0.4) !important;
            color: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .text-layer .highlight.current {
            background-color: rgba(255, 152, 0, 0.6) !important;
        }

        /* Make highlighted text more visible */
        mark {
            background-color: rgba(255, 255, 0, 0.15);
            color: #000;
            padding: 0;
            margin: 0;
        }

        .loading-indicator {
            color: white;
            text-align: center;
            padding: 40px;
            font-size: 1.2rem;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #d32f2f;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .page-controls {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
            z-index: 50;
        }

        .page-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #d32f2f;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .page-btn:hover:not(:disabled) {
            background: #b71c1c;
            transform: scale(1.1);
        }

        .page-btn:disabled {
            background: #888;
            cursor: not-allowed;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .thesis-info {
                display: none;
            }

            .toolbar-left, .toolbar-right {
                flex: 1;
            }

            .highlight-bar {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="viewer-container">
        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="toolbar-left">
                <div class="thesis-info">
                    <div class="thesis-title-small"><?= htmlspecialchars($thesis['title'] ?? 'Untitled') ?></div>
                    <div class="thesis-meta-small">
                        <?= htmlspecialchars($thesis['author'] ?? 'Unknown Author') ?>
                        <?php if (!empty($thesis['strand'])): ?>
                            • <?= htmlspecialchars($thesis['strand']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="toolbar-right">
                <a href="<?= route('research/download') ?>&id=<?= $thesis['id'] ?>" class="toolbar-btn" download>
                     Download
                </a>
                <a href="<?= route('research/show') ?>&id=<?= $thesis['id'] ?>" class="toolbar-btn close-btn">
                     Close
                </a>
            </div>
        </div>

        <!-- Highlight Bar (shown when highlighting keywords) -->
        <div class="highlight-bar" id="highlightBar">
            <div class="highlight-info">
                 Highlighting: <span class="highlight-term" id="highlightTerm"></span>
                <span id="matchCount"></span>
            </div>
            <div class="highlight-controls">
                <button class="highlight-btn" id="prevMatch" onclick="previousMatch()">← Previous</button>
                <button class="highlight-btn" id="nextMatch" onclick="nextMatch()">Next →</button>
                <button class="highlight-btn" onclick="clearHighlights()">Clear</button>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="pdf-viewer">
            <div class="pdf-container" id="pdfContainer">
                <div class="loading-indicator">
                    <div class="loading-spinner"></div>
                    <p>Loading PDF...</p>
                </div>
            </div>
        </div>

        <!-- Page Controls -->
        <div class="page-controls">
            <button class="page-btn" id="prevPage" onclick="previousPage()" title="Previous Page">↑</button>
            <button class="page-btn" id="nextPage" onclick="nextPage()" title="Next Page">↓</button>
        </div>
    </div>

    <!-- PDF.js Library (from CDN) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <script>
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const highlightTerm = urlParams.get('highlight') || '';
        const targetPage = parseInt(urlParams.get('page')) || 1;

        // PDF.js configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        <?php
        // Fix PDF path - ensure it starts with /
        $pdfPath = $thesis['file_path'] ?? '';
        if (!empty($pdfPath) && substr($pdfPath, 0, 1) !== '/') {
            $pdfPath = '/' . $pdfPath;
        }
        ?>
        const pdfUrl = '<?= htmlspecialchars($pdfPath) ?>';
        let pdfDoc = null;
        let currentPage = targetPage;
        let allMatches = [];
        let currentMatchIndex = 0;

        // Debug: Log PDF URL
        console.log('PDF URL:', pdfUrl);

        // Load and render PDF
        const loadingTask = pdfjsLib.getDocument(pdfUrl);
        loadingTask.promise.then(function(pdf) {
            pdfDoc = pdf;
            document.getElementById('pdfContainer').innerHTML = '';

            console.log('PDF loaded, total pages:', pdf.numPages);

            // Render all pages
            const renderPromises = [];
            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                renderPromises.push(renderPage(pageNum));
            }

            // Show highlight bar if we have a search term
            if (highlightTerm) {
                document.getElementById('highlightTerm').textContent = highlightTerm;
                document.getElementById('highlightBar').classList.add('active');

                // Wait for all pages to render, then highlight
                setTimeout(() => {
                    console.log('Attempting to highlight all pages...');
                    highlightAll(highlightTerm);

                    // Scroll to target page
                    if (targetPage > 1) {
                        setTimeout(() => {
                            scrollToPage(targetPage);
                            // If we have matches, try to scroll to first match on target page
                            if (allMatches.length > 0) {
                                const targetMatch = allMatches.find(m => m.page === targetPage);
                                if (targetMatch) {
                                    currentMatchIndex = allMatches.indexOf(targetMatch);
                                    highlightCurrentMatch();
                                }
                            }
                        }, 500);
                    }
                }, 2000); // Increased wait time for text layers to render
            }
        }).catch(function(error) {
            console.error('PDF loading error:', error);
            document.getElementById('pdfContainer').innerHTML = '<div class="loading-indicator"><h2>Error loading PDF</h2><p>' + error.message + '</p></div>';
        });

        function renderPage(pageNum) {
            pdfDoc.getPage(pageNum).then(function(page) {
                const scale = 1.5;
                const viewport = page.getViewport({ scale: scale });

                const pageDiv = document.createElement('div');
                pageDiv.className = 'pdf-page';
                pageDiv.id = 'page-' + pageNum;
                pageDiv.dataset.pageNumber = pageNum;
                pageDiv.style.position = 'relative';
                pageDiv.style.marginBottom = '10px';

                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');

                // Simple viewport-based sizing (no devicePixelRatio scaling)
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };

                pageDiv.appendChild(canvas);

                // Render text layer for highlighting - must match canvas exactly
                const textLayerDiv = document.createElement('div');
                textLayerDiv.className = 'text-layer';
                textLayerDiv.style.height = canvas.style.height || (viewport.height + 'px');
                textLayerDiv.style.width = canvas.style.width || (viewport.width + 'px');
                textLayerDiv.style.position = 'absolute';
                textLayerDiv.style.left = '0px';
                textLayerDiv.style.top = '0px';
                textLayerDiv.style.margin = '0';
                textLayerDiv.style.padding = '0';
                // Set the scale factor CSS variable required by PDF.js
                textLayerDiv.style.setProperty('--scale-factor', scale);
                pageDiv.appendChild(textLayerDiv);

                document.getElementById('pdfContainer').appendChild(pageDiv);

                // Render PDF page
                page.render(renderContext).promise.then(function() {
                    console.log(' Page ' + pageNum + ' canvas rendered');
                    return page.getTextContent();
                }).then(function(textContent) {
                    console.log(' Page ' + pageNum + ' text content retrieved:', textContent.items.length, 'items');

                    // Render text layer with proper transform
                    return pdfjsLib.renderTextLayer({
                        textContentSource: textContent,
                        container: textLayerDiv,
                        viewport: viewport,
                        textDivs: []
                    }).promise;
                }).then(function() {
                    console.log(' Text layer rendered for page ' + pageNum);
                    console.log('  Text layer children:', textLayerDiv.children.length);

                    // If we have a highlight term and this is near the target page, highlight
                    if (highlightTerm && pageNum >= targetPage - 1 && pageNum <= targetPage + 1) {
                        console.log('  Highlighting page ' + pageNum + ' for term:', highlightTerm);
                        setTimeout(function() {
                            highlightPageText(textLayerDiv, highlightTerm, pageNum);
                        }, 500);
                    }
                }).catch(function(error) {
                    console.error(' Error rendering page ' + pageNum + ':', error);
                    textLayerDiv.innerHTML = '<div style="color: red; padding: 10px;">Text layer error: ' + error.message + '</div>';
                });
            });
        }

        function highlightPageText(textLayerDiv, searchTerm, pageNum) {
            const textElements = textLayerDiv.querySelectorAll('span, div');

            console.log('Highlighting page ' + pageNum + ', found ' + textElements.length + ' text elements');

            textElements.forEach(element => {
                const text = element.textContent;
                if (text && text.toLowerCase().includes(searchTerm.toLowerCase())) {
                    element.classList.add('highlight');
                    allMatches.push({
                        element: element,
                        page: pageNum
                    });
                    console.log('Match found on page ' + pageNum + ': ' + text.substring(0, 50));
                }
            });

            if (allMatches.length > 0) {
                document.getElementById('matchCount').textContent = `(${allMatches.length} match${allMatches.length > 1 ? 'es' : ''})`;
                updateNavigationButtons();
            }
        }

        function highlightAll(searchTerm) {
            console.log('Starting highlightAll for: ' + searchTerm);
            allMatches = [];
            const textLayers = document.querySelectorAll('.text-layer');

            console.log('Found ' + textLayers.length + ' text layers');

            textLayers.forEach((layer, pageIndex) => {
                highlightPageText(layer, searchTerm, pageIndex + 1);
            });

            if (allMatches.length > 0) {
                document.getElementById('matchCount').textContent = `(${allMatches.length} match${allMatches.length > 1 ? 'es' : ''})`;
                currentMatchIndex = 0;
                highlightCurrentMatch();
            } else {
                document.getElementById('matchCount').textContent = '(no matches)';
                console.log('No matches found for: ' + searchTerm);
            }

            updateNavigationButtons();
        }

        function highlightCurrentMatch() {
            // Remove current highlighting from all
            allMatches.forEach(match => {
                match.element.classList.remove('current');
            });

            if (allMatches.length > 0 && currentMatchIndex >= 0 && currentMatchIndex < allMatches.length) {
                const currentMatch = allMatches[currentMatchIndex];
                currentMatch.element.classList.add('current');
                currentMatch.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function nextMatch() {
            if (currentMatchIndex < allMatches.length - 1) {
                currentMatchIndex++;
                highlightCurrentMatch();
                updateNavigationButtons();
            }
        }

        function previousMatch() {
            if (currentMatchIndex > 0) {
                currentMatchIndex--;
                highlightCurrentMatch();
                updateNavigationButtons();
            }
        }

        function updateNavigationButtons() {
            document.getElementById('prevMatch').disabled = currentMatchIndex === 0;
            document.getElementById('nextMatch').disabled = currentMatchIndex === allMatches.length - 1;
        }

        function clearHighlights() {
            allMatches.forEach(match => {
                match.element.classList.remove('highlight', 'current');
            });
            allMatches = [];
            document.getElementById('highlightBar').classList.remove('active');
        }

        function scrollToPage(pageNum) {
            const pageElement = document.getElementById('page-' + pageNum);
            if (pageElement) {
                pageElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function nextPage() {
            if (pdfDoc && currentPage < pdfDoc.numPages) {
                currentPage++;
                scrollToPage(currentPage);
            }
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                scrollToPage(currentPage);
            }
        }

        // Update page buttons
        const container = document.getElementById('pdfContainer');
        container.addEventListener('scroll', function() {
            const pages = document.querySelectorAll('.pdf-page');
            const scrollTop = container.scrollTop;
            const containerHeight = container.clientHeight;

            pages.forEach(page => {
                const pageTop = page.offsetTop;
                const pageHeight = page.offsetHeight;

                if (scrollTop >= pageTop - containerHeight / 2 && scrollTop < pageTop + pageHeight - containerHeight / 2) {
                    currentPage = parseInt(page.dataset.pageNumber);
                }
            });

            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = pdfDoc && currentPage === pdfDoc.numPages;
        });
    </script>
</body>
</html>
