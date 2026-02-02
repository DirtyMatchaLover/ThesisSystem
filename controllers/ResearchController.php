<?php
require_once __DIR__ . '/../models/Thesis.php';

class ResearchController {
    private $theses;

    public function __construct() {
        $this->theses = new Thesis();
    }

    public function index() {
        try {
            // Handle search query
            $searchQuery = trim($_GET['search'] ?? '');
            $filterType = $_GET['filter'] ?? '';
            $category = $_GET['category'] ?? '';
            $year = $_GET['year'] ?? '';
            $author = $_GET['author'] ?? '';

            // Use database search if there's a search query (includes PDF content search)
            if (!empty($searchQuery)) {
                $filters = ['status' => 'approved'];
                if (!empty($category)) $filters['strand'] = $category;
                if (!empty($year)) $filters['academic_year'] = $year;

                // Use the Thesis model's search which includes PDF content
                $approvedTheses = $this->theses->search($searchQuery, $filters);
            } else {
                // Get all approved theses for browsing - FIXED VERSION
                $approvedTheses = $this->getApprovedThesesSafely();
            }

            // Filter theses based on remaining parameters (author filter, etc.)
            $filteredTheses = $this->filterTheses($approvedTheses, '', $filterType, $category, $year, $author);

            // Get filter options for dropdowns
            $filterOptions = $this->getFilterOptions($approvedTheses);
            
            // Pagination
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 12;
            $totalTheses = count($filteredTheses);
            $totalPages = ceil($totalTheses / $perPage);
            $offset = ($page - 1) * $perPage;
            
            $paginatedTheses = array_slice($filteredTheses, $offset, $perPage);
            
            // Prepare data for view
            $data = [
                'theses' => $paginatedTheses,
                'totalTheses' => $totalTheses,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'searchQuery' => $searchQuery,
                'filterType' => $filterType,
                'category' => $category,
                'year' => $year,
                'author' => $author,
                'filterOptions' => $filterOptions,
                'hasFilters' => !empty($searchQuery) || !empty($filterType) || !empty($category) || !empty($year) || !empty($author)
            ];
            
            require __DIR__ . '/../views/research.php';
            
        } catch (Exception $e) {
            error_log("ResearchController error: " . $e->getMessage());
            
            // Fallback data for when things fail
            $data = [
                'theses' => [],
                'totalTheses' => 0,
                'currentPage' => 1,
                'totalPages' => 0,
                'searchQuery' => $_GET['search'] ?? '',
                'filterType' => '',
                'category' => '',
                'year' => '',
                'author' => '',
                'filterOptions' => ['years' => [], 'authors' => [], 'categories' => []],
                'hasFilters' => false,
                'error' => $e->getMessage()
            ];
            
            require __DIR__ . '/../views/research.php';
        }
    }

    /**
     * FIXED: Safely get approved theses with multiple fallback methods
     */
    private function getApprovedThesesSafely() {
        try {
            // Try the Thesis model method first
            $theses = $this->theses->approvedPublic();
            if (!empty($theses)) {
                return $theses;
            }
        } catch (Exception $e) {
            error_log("Thesis model failed: " . $e->getMessage());
        }
        
        // Fallback to direct database query
        try {
            require_once __DIR__ . '/../models/Database.php';
            $db = Database::getInstance();
            
            // Check what columns exist first
            $stmt = $db->query("DESCRIBE theses");
            $columns = $stmt->fetchAll();
            $columnNames = array_column($columns, 'Field');
            
            // Build query based on available columns
            $whereClause = "WHERE t.status = 'approved'";
            
            if (in_array('is_public', $columnNames)) {
                $whereClause .= " AND (t.is_public IS NULL OR t.is_public = 1)";
            }
            
            $query = "
                SELECT t.*, u.name as author, u.name as author_name 
                FROM theses t 
                LEFT JOIN users u ON t.user_id = u.id 
                $whereClause
                ORDER BY t.created_at DESC
            ";
            
            $stmt = $db->query($query);
            $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($theses)) {
                return $theses;
            }
            
        } catch (Exception $e) {
            error_log("Direct database query failed: " . $e->getMessage());
        }
        
        // Final fallback - get ANY approved theses regardless of columns
        try {
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT t.*, u.name as author, u.name as author_name 
                FROM theses t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.status = 'approved'
                ORDER BY t.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Final fallback failed: " . $e->getMessage());
            return [];
        }
    }

    private function filterTheses($theses, $search, $filter, $category, $year, $author) {
        if (empty($theses)) {
            return [];
        }

        $filtered = $theses;

        // Advanced search filter - searches EVERYTHING with single words
        if (!empty($search)) {
            $searchTerms = preg_split('/\s+/', trim($search));
            $filtered = array_filter($filtered, function($thesis) use ($searchTerms) {
                $searchableText = strtolower(implode(' ', [
                    $thesis['title'] ?? '',
                    $thesis['abstract'] ?? '',
                    $thesis['author'] ?? '',
                    $thesis['author_name'] ?? '',
                    $thesis['strand'] ?? '',
                    $thesis['department'] ?? '',
                    $thesis['adviser'] ?? '',
                    $thesis['keywords'] ?? '',
                    $thesis['academic_year'] ?? ''
                ]));

                // Match if ALL search terms are found
                foreach ($searchTerms as $term) {
                    $term = strtolower(trim($term));
                    if (!empty($term) && strlen($term) > 1 && strpos($searchableText, $term) === false) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Filter type (recent, popular, etc.)
        if (!empty($filter)) {
            switch ($filter) {
                case 'recent':
                    usort($filtered, function($a, $b) {
                        return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
                    });
                    break;

                case 'oldest':
                    usort($filtered, function($a, $b) {
                        return strtotime($a['created_at'] ?? '0') - strtotime($b['created_at'] ?? '0');
                    });
                    break;

                case 'most_viewed':
                    usort($filtered, function($a, $b) {
                        $aViews = (int)($a['view_count'] ?? 0);
                        $bViews = (int)($b['view_count'] ?? 0);
                        return $bViews - $aViews;
                    });
                    break;

                case 'most_downloaded':
                    usort($filtered, function($a, $b) {
                        $aDownloads = (int)($a['download_count'] ?? 0);
                        $bDownloads = (int)($b['download_count'] ?? 0);
                        return $bDownloads - $aDownloads;
                    });
                    break;

                case 'popular':
                    usort($filtered, function($a, $b) {
                        $aScore = (int)($a['view_count'] ?? 0) + (int)($a['download_count'] ?? 0);
                        $bScore = (int)($b['view_count'] ?? 0) + (int)($b['download_count'] ?? 0);
                        return $bScore - $aScore;
                    });
                    break;

                case 'alphabetical':
                    usort($filtered, function($a, $b) {
                        return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
                    });
                    break;

                case 'alphabetical_desc':
                    usort($filtered, function($a, $b) {
                        return strcasecmp($b['title'] ?? '', $a['title'] ?? '');
                    });
                    break;
            }
        }

        // Category/Strand filter
        if (!empty($category)) {
            $filtered = array_filter($filtered, function($thesis) use ($category) {
                return ($thesis['strand'] ?? '') === $category;
            });
        }

        // Year filter
        if (!empty($year)) {
            $filtered = array_filter($filtered, function($thesis) use ($year) {
                $thesisYear = date('Y', strtotime($thesis['created_at'] ?? '0'));
                return $thesisYear === $year;
            });
        }

        // Author filter
        if (!empty($author)) {
            $filtered = array_filter($filtered, function($thesis) use ($author) {
                $thesisAuthor = $thesis['author'] ?? $thesis['author_name'] ?? '';
                return stripos($thesisAuthor, $author) !== false;
            });
        }

        return $filtered;
    }

    private function getFilterOptions($theses) {
        $years = [];
        $authors = [];
        $strands = [];

        foreach ($theses as $thesis) {
            // Extract years
            $year = date('Y', strtotime($thesis['created_at'] ?? '0'));
            if ($year && $year !== '1970') {
                $years[$year] = $year;
            }

            // Extract authors
            $author = $thesis['author'] ?? $thesis['author_name'] ?? '';
            if (!empty($author)) {
                $authors[$author] = $author;
            }

            // Extract strands (as categories)
            $strand = $thesis['strand'] ?? '';
            if (!empty($strand)) {
                $strands[$strand] = $strand;
            }
        }

        return [
            'years' => array_values($years),
            'authors' => array_values($authors),
            'categories' => array_values($strands),
            'strands' => array_values($strands)
        ];
    }

    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        $thesis = $this->theses->find($id);
        if (!$thesis || $thesis['status'] !== 'approved') {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Increment view count
        try {
            $this->theses->incrementViewCount($id);
        } catch (Exception $e) {
            // Don't fail if view count increment fails
            error_log("Failed to increment view count: " . $e->getMessage());
        }

        // Get related theses (same strand or similar keywords)
        $relatedTheses = $this->getRelatedTheses($thesis, 4);

        require __DIR__ . '/../views/research/show.php';
    }

    /**
     * NEW: Download method for research papers
     */
    public function download() {
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            exit('Invalid thesis ID');
        }

        try {
            // Find the thesis
            $thesis = $this->theses->find($id);

            if (!$thesis) {
                http_response_code(404);
                exit('Thesis not found');
            }

            // Check access permissions - allow approved theses or own submissions
            if ($thesis['status'] !== 'approved' && $thesis['status'] !== 'published') {
                // If not approved, only allow author or admin/faculty to download
                if (!is_logged_in()) {
                    http_response_code(403);
                    exit('This thesis is not available for download');
                }

                $user = current_user();
                if ($user['id'] != $thesis['user_id'] && !in_array($user['role'], ['admin', 'faculty', 'librarian'])) {
                    http_response_code(403);
                    exit('This thesis is not available for download');
                }
            }

            // Check if file exists
            if (empty($thesis['file_path'])) {
                http_response_code(404);
                exit('No file associated with this thesis');
            }

            // Try multiple path variations
            $filePath = $thesis['file_path'];

            // Remove leading slash if present for relative path
            $relativePath = ltrim($filePath, '/');

            // Try different path combinations
            $pathsToTry = [
                __DIR__ . '/../' . $relativePath,
                __DIR__ . '/../' . $filePath,
                __DIR__ . '/' . $relativePath,
                '/var/www/html/' . $relativePath  // Docker path
            ];

            $foundPath = null;
            foreach ($pathsToTry as $path) {
                if (file_exists($path)) {
                    $foundPath = $path;
                    break;
                }
            }

            if (!$foundPath) {
                error_log("Download failed - file not found. Tried paths: " . implode(', ', $pathsToTry));
                http_response_code(404);
                exit('File not found on server. Path: ' . htmlspecialchars($thesis['file_path']));
            }

            // Increment download count
            try {
                $this->incrementDownloadCount($id);
            } catch (Exception $e) {
                // Don't fail if download count increment fails
                error_log("Failed to increment download count: " . $e->getMessage());
            }

            // Prepare filename for download
            $filename = $thesis['original_filename'] ?? $thesis['title'] . '.pdf';
            $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
            if (!preg_match('/\.pdf$/i', $filename)) {
                $filename .= '.pdf';
            }

            // Set headers for file download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($foundPath));
            header('Cache-Control: private');
            header('Pragma: private');
            header('Expires: 0');

            // Clear output buffer and send file
            if (ob_get_level()) {
                ob_end_clean();
            }

            readfile($foundPath);
            exit;

        } catch (Exception $e) {
            error_log("Download error: " . $e->getMessage());
            http_response_code(500);
            exit('Error downloading file: ' . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Increment download count for a thesis
     */
    private function incrementDownloadCount($id) {
        try {
            require_once __DIR__ . '/../models/Database.php';
            $db = Database::getInstance();
            
            // Check if download_count column exists
            $stmt = $db->query("DESCRIBE theses");
            $columns = $stmt->fetchAll();
            $columnNames = array_column($columns, 'Field');
            
            if (in_array('download_count', $columnNames)) {
                $stmt = $db->prepare("UPDATE theses SET download_count = COALESCE(download_count, 0) + 1 WHERE id = ?");
                $stmt->execute([$id]);
            }
        } catch (Exception $e) {
            error_log("Failed to increment download count: " . $e->getMessage());
        }
    }

    private function getRelatedTheses($currentThesis, $limit = 4) {
        try {
            $allApproved = $this->getApprovedThesesSafely();
            $related = [];

            foreach ($allApproved as $thesis) {
                // Skip the current thesis
                if ($thesis['id'] == $currentThesis['id']) {
                    continue;
                }

                $score = 0;

                // Same strand gets higher score
                if (!empty($thesis['strand']) && !empty($currentThesis['strand']) && 
                    $thesis['strand'] === $currentThesis['strand']) {
                    $score += 3;
                }

                // Similar titles get score
                if (!empty($thesis['title']) && !empty($currentThesis['title'])) {
                    $titleWords = explode(' ', strtolower($currentThesis['title']));
                    $thesisTitleLower = strtolower($thesis['title']);
                    
                    foreach ($titleWords as $word) {
                        if (strlen($word) > 3 && strpos($thesisTitleLower, $word) !== false) {
                            $score += 1;
                        }
                    }
                }

                if ($score > 0) {
                    $thesis['relevance_score'] = $score;
                    $related[] = $thesis;
                }
            }

            // Sort by relevance score
            usort($related, function($a, $b) {
                return $b['relevance_score'] - $a['relevance_score'];
            });

            return array_slice($related, 0, $limit);
            
        } catch (Exception $e) {
            error_log("Error getting related theses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * PDF Viewer with Highlighting - Opens thesis PDF with keyword highlighting
     */
    public function pdfViewer() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px; font-family: sans-serif;'>";
            echo "<h2>Thesis Not Found</h2>";
            echo "<p>Invalid thesis ID.</p>";
            echo "<a href='" . route('research') . "' style='color: #d32f2f;'>← Back to Research</a>";
            echo "</div>";
            return;
        }

        $thesis = $this->theses->find($id);
        if (!$thesis || $thesis['status'] !== 'approved') {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px; font-family: sans-serif;'>";
            echo "<h2>Thesis Not Available</h2>";
            echo "<p>This thesis is not available for viewing.</p>";
            echo "<a href='" . route('research') . "' style='color: #d32f2f;'>← Back to Research</a>";
            echo "</div>";
            return;
        }

        // Check if PDF file exists
        if (empty($thesis['file_path'])) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px; font-family: sans-serif;'>";
            echo "<h2>PDF Not Available</h2>";
            echo "<p>This thesis doesn't have a PDF file.</p>";
            echo "<a href='" . route('research/show') . '&id=' . $id . "' style='color: #d32f2f;'>← Back to Thesis</a>";
            echo "</div>";
            return;
        }

        // Get author name from users table if not set
        if (empty($thesis['author']) && empty($thesis['author_name'])) {
            require_once __DIR__ . '/../models/Database.php';
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT name, strand FROM users WHERE id = ?");
            $stmt->execute([$thesis['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $thesis['author'] = $user['name'] ?? 'Unknown';
            if (empty($thesis['strand']) && !empty($user['strand'])) {
                $thesis['strand'] = $user['strand'];
            }
        } else {
            $thesis['author'] = $thesis['author_name'] ?? $thesis['author'] ?? 'Unknown';
        }

        // Increment view count
        try {
            $this->theses->incrementViewCount($id);
        } catch (Exception $e) {
            error_log("Failed to increment view count: " . $e->getMessage());
        }

        // Load the PDF viewer template
        require __DIR__ . '/../views/research/pdf_viewer.php';
    }

    /**
     * Instant Search API - Returns JSON for live search results (with PDF content search)
     */
    public function instantSearch() {
        header('Content-Type: application/json');

        $query = trim($_GET['q'] ?? '');

        if (strlen($query) < 2) {
            echo json_encode(['results' => [], 'count' => 0]);
            return;
        }

        try {
            // Use database search which includes PDF content
            $filteredTheses = $this->theses->search($query, ['status' => 'approved']);

            // Limit to top 10 results for instant search
            $results = array_slice($filteredTheses, 0, 10);

            // Format results
            $formattedResults = array_map(function($thesis) use ($query) {
                // Check if this thesis has PDF content matches
                $hasPdfMatches = !empty($thesis['search_snippets']) && is_array($thesis['search_snippets']) && count($thesis['search_snippets']) > 0;
                $hasPdf = !empty($thesis['file_path']);

                // Get first snippet page if available
                $firstPage = 1;
                if ($hasPdfMatches && isset($thesis['search_snippets'][0])) {
                    $firstSnippet = $thesis['search_snippets'][0];
                    if (is_array($firstSnippet) && isset($firstSnippet['page'])) {
                        $firstPage = $firstSnippet['page'];
                    }
                }

                // Build URL - if has PDF matches, link to PDF viewer with highlighting
                if ($hasPdfMatches && $hasPdf) {
                    $url = '/index.php?route=research/viewer&id=' . $thesis['id'] .
                           '&highlight=' . urlencode($query) .
                           '&page=' . $firstPage;
                } else {
                    $url = '/index.php?route=research/show&id=' . $thesis['id'];
                }

                return [
                    'id' => $thesis['id'],
                    'title' => $thesis['title'] ?? 'Untitled',
                    'author' => $thesis['author'] ?? $thesis['author_name'] ?? 'Unknown Author',
                    'strand' => $thesis['strand'] ?? 'General',
                    'year' => date('Y', strtotime($thesis['created_at'] ?? 'now')),
                    'abstract' => substr($thesis['abstract'] ?? '', 0, 150) . '...',
                    'url' => $url,
                    'has_pdf_matches' => $hasPdfMatches,
                    'has_pdf' => $hasPdf,
                    'match_count' => $thesis['keyword_stats']['total_occurrences'] ?? 0
                ];
            }, $results);

            echo json_encode([
                'results' => $formattedResults,
                'count' => count($filteredTheses),
                'showing' => count($formattedResults)
            ]);

        } catch (Exception $e) {
            error_log("Instant search error: " . $e->getMessage());
            echo json_encode(['results' => [], 'count' => 0, 'error' => 'Search failed']);
        }
    }
}