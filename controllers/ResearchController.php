<?php
require_once __DIR__ . '/../models/Thesis.php';

class ResearchController {
    private $theses;

    public function __construct() {
        $this->theses = new Thesis();
    }

    public function index() {
        // Get all approved theses for browsing
        $approvedTheses = $this->theses->approvedPublic();
        
        // Handle search query
        $searchQuery = trim($_GET['search'] ?? '');
        $filterType = $_GET['filter'] ?? '';
        $category = $_GET['category'] ?? '';
        $year = $_GET['year'] ?? '';
        $author = $_GET['author'] ?? '';
        
        // Filter theses based on parameters
        $filteredTheses = $this->filterTheses($approvedTheses, $searchQuery, $filterType, $category, $year, $author);
        
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
    }

    private function filterTheses($theses, $search, $filter, $category, $year, $author) {
        if (empty($theses)) {
            return [];
        }

        $filtered = $theses;

        // Search filter
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $filtered = array_filter($filtered, function($thesis) use ($searchLower) {
                $title = strtolower($thesis['title'] ?? '');
                $abstract = strtolower($thesis['abstract'] ?? '');
                $authorName = strtolower($thesis['author'] ?? $thesis['author_name'] ?? '');
                
                return strpos($title, $searchLower) !== false ||
                       strpos($abstract, $searchLower) !== false ||
                       strpos($authorName, $searchLower) !== false;
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
                    
                case 'popular':
                    usort($filtered, function($a, $b) {
                        $viewsA = (int)($a['view_count'] ?? 0);
                        $viewsB = (int)($b['view_count'] ?? 0);
                        return $viewsB - $viewsA;
                    });
                    break;
                    
                case 'alphabetical':
                    usort($filtered, function($a, $b) {
                        return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
                    });
                    break;
            }
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
            $authorLower = strtolower($author);
            $filtered = array_filter($filtered, function($thesis) use ($authorLower) {
                $thesisAuthor = strtolower($thesis['author'] ?? $thesis['author_name'] ?? '');
                return strpos($thesisAuthor, $authorLower) !== false;
            });
        }

        // Category filter (if you have categories in the future)
        if (!empty($category)) {
            $filtered = array_filter($filtered, function($thesis) use ($category) {
                $thesisStrand = strtolower($thesis['strand'] ?? '');
                return $thesisStrand === strtolower($category);
            });
        }

        return array_values($filtered); // Re-index array
    }

    private function getFilterOptions($theses) {
        if (empty($theses)) {
            return [
                'years' => [],
                'authors' => [],
                'categories' => [],
                'strands' => []
            ];
        }

        $years = [];
        $authors = [];
        $strands = [];

        foreach ($theses as $thesis) {
            // Extract years
            if (!empty($thesis['created_at'])) {
                $year = date('Y', strtotime($thesis['created_at']));
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
        $this->theses->incrementViewCount($id);

        // Get related theses (same strand or similar keywords)
        $relatedTheses = $this->getRelatedTheses($thesis, 4);

        require __DIR__ . '/../views/research/show.php';
    }

    private function getRelatedTheses($currentThesis, $limit = 4) {
        try {
            $allApproved = $this->theses->approvedPublic();
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

    public function download() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            exit('File not found');
        }

        $thesis = $this->theses->find($id);
        if (!$thesis || $thesis['status'] !== 'approved' || empty($thesis['file_path'])) {
            http_response_code(404);
            exit('File not found');
        }

        $filePath = __DIR__ . '/../' . $thesis['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('File not found');
        }

        // Increment download count (if you have this field)
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE theses SET download_count = COALESCE(download_count, 0) + 1 WHERE id = ?");
            $stmt->execute([$id]);
        } catch (Exception $e) {
            // Don't fail download if logging fails
            error_log("Download count update failed: " . $e->getMessage());
        }

        // Set headers for file download
        $filename = $thesis['original_filename'] ?? basename($thesis['file_path']);
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private');
        header('Pragma: private');

        readfile($filePath);
        exit;
    }
}