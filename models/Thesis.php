<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../helpers/PdfTextExtractor.php';

class Thesis {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new thesis
     */
    public function create($user_id, $title, $abstract, $file_path, $status = 'submitted', $adviser = null) {
        // Extract text from PDF for full-text search (with page tracking)
        $fullTextContent = '';
        $pageData = null;
        if (!empty($file_path) && file_exists(__DIR__ . '/../' . $file_path)) {
            $pdfPath = __DIR__ . '/../' . $file_path;

            // Try page-by-page extraction first
            $extractedByPage = PdfTextExtractor::extractTextByPage($pdfPath);
            if ($extractedByPage !== false) {
                $fullTextContent = $extractedByPage['full_text'];
                $pageData = $extractedByPage; // Store for later use
            } else {
                // Fallback to simple extraction
                $extractedText = PdfTextExtractor::extractText($pdfPath);
                if ($extractedText !== false) {
                    $fullTextContent = $extractedText;
                }
            }
        }

        // Check if columns exist
        $hasFullTextColumn = $this->columnExists('full_text_content');
        $hasAdviserColumn = $this->columnExists('adviser');

        // Build dynamic query based on available columns
        $columns = ['user_id', 'title', 'abstract', 'file_path', 'status', 'submission_date', 'academic_year', 'created_at'];
        $values = ['?', '?', '?', '?', '?', 'CURRENT_TIMESTAMP', '?', 'CURRENT_TIMESTAMP'];
        $params = [];

        if ($hasFullTextColumn) {
            $columns[] = 'full_text_content';
            $values[] = '?';
        }

        if ($hasAdviserColumn && !empty($adviser)) {
            $columns[] = 'adviser';
            $values[] = '?';
        }

        $sql = "INSERT INTO theses (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $values) . ")";

        $stmt = $this->db->prepare($sql);

        // Get current academic year
        $current_year = date('Y');
        $academic_year = $current_year . '-' . ($current_year + 1);

        // Build parameters array
        $params = [$user_id, $title, $abstract, $file_path, $status, $academic_year];

        if ($hasFullTextColumn) {
            $params[] = $fullTextContent;
        }

        if ($hasAdviserColumn && !empty($adviser)) {
            $params[] = $adviser;
        }

        $result = $stmt->execute($params);

        // If insert was successful and we have page data, store it
        if ($result && $pageData !== null) {
            $thesisId = $this->db->lastInsertId();
            $this->savePageLevelText($thesisId, $pageData);
        }

        return $result;
    }

    /**
     * Find thesis by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as author, u.email as author_email 
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all theses
     */
    public function all() {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as author, u.email as author_email 
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get theses by user
     */
    public function byUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM theses 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get approved and public theses - FIXED VERSION
     */
    public function approvedPublic() {
        // First check if is_public column exists
        $hasIsPublicColumn = $this->columnExists('is_public');
        
        if ($hasIsPublicColumn) {
            // Use the is_public column if it exists
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as author, u.name as author_name 
                FROM theses t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.status = 'approved' AND (t.is_public = 1 OR t.is_public IS NULL)
                ORDER BY t.created_at DESC
            ");
        } else {
            // Fallback: show all approved theses if is_public column doesn't exist
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as author, u.name as author_name 
                FROM theses t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.status = 'approved'
                ORDER BY t.created_at DESC
            ");
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a column exists in the theses table
     */
    private function columnExists($columnName) {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM theses");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $column) {
                if ($column['Field'] === $columnName) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update thesis status - ENHANCED VERSION
     */
    public function updateStatus($id, $status, $approved_by = null) {
        // Check if enhanced columns exist
        $hasIsPublicColumn = $this->columnExists('is_public');
        $hasPublicationDateColumn = $this->columnExists('publication_date');
        $hasApprovalDateColumn = $this->columnExists('approval_date');
        $hasUpdatedAtColumn = $this->columnExists('updated_at');
        $hasApprovedByColumn = $this->columnExists('approved_by');
        $hasApprovedAtColumn = $this->columnExists('approved_at');

        // Build the update query based on available columns
        $updateFields = ["status = ?"];
        $params = [$status];

        if ($hasUpdatedAtColumn) {
            $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
        }

        if ($hasApprovalDateColumn && $status === 'approved') {
            $updateFields[] = "approval_date = CURRENT_TIMESTAMP";
        }

        if ($hasPublicationDateColumn && $status === 'approved') {
            $updateFields[] = "publication_date = CURRENT_TIMESTAMP";
        }

        if ($hasIsPublicColumn && $status === 'approved') {
            $updateFields[] = "is_public = 1";
        }

        // NEW: Record who approved and when
        if ($hasApprovedByColumn && $status === 'approved' && $approved_by !== null) {
            $updateFields[] = "approved_by = ?";
            $params[] = $approved_by;
        }

        if ($hasApprovedAtColumn && $status === 'approved') {
            $updateFields[] = "approved_at = CURRENT_TIMESTAMP";
        }

        $sql = "UPDATE theses SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get thesis statistics
     */
    public function getStats() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(CASE WHEN status = 'submitted' THEN 1 END) as submitted,
                COUNT(CASE WHEN status = 'under_review' THEN 1 END) as under_review,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COUNT(CASE WHEN status = 'revision_required' THEN 1 END) as revision_required,
                COUNT(*) as total
            FROM theses
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'submitted' => (int)$result['submitted'],
            'under_review' => (int)$result['under_review'],
            'approved' => (int)$result['approved'],
            'rejected' => (int)$result['rejected'],
            'revision_required' => (int)$result['revision_required'],
            'total' => (int)$result['total']
        ];
    }

    /**
     * Search theses by title, author, abstract, or PDF content
     * Now includes snippet extraction with keyword highlighting
     */
    public function search($query, $filters = []) {
        // Check if full_text_content column exists
        $hasFullTextColumn = $this->columnExists('full_text_content');

        $sql = "
            SELECT t.*, u.name as author, u.email as author_email, u.strand
            FROM theses t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        // Search query - now includes PDF content if available
        if (!empty($query)) {
            if ($hasFullTextColumn) {
                // Try FULLTEXT search first (faster), fallback to LIKE if FULLTEXT index doesn't exist
                try {
                    // Test if FULLTEXT index exists
                    $testStmt = $this->db->prepare("
                        SELECT COUNT(*) as test_count
                        FROM theses t
                        WHERE MATCH(t.title, t.abstract, t.full_text_content) AGAINST(? IN NATURAL LANGUAGE MODE)
                        LIMIT 1
                    ");
                    $testStmt->execute(['test']);

                    // If we get here, FULLTEXT works
                    $sql .= " AND (
                        MATCH(t.title, t.abstract, t.full_text_content) AGAINST(? IN NATURAL LANGUAGE MODE)
                        OR u.name LIKE ?
                    )";
                    $params[] = $query;
                    $params[] = '%' . $query . '%';
                } catch (Exception $e) {
                    // FULLTEXT index doesn't exist, use LIKE with PDF content search
                    $sql .= " AND (
                        t.title LIKE ?
                        OR t.abstract LIKE ?
                        OR t.full_text_content LIKE ?
                        OR u.name LIKE ?
                    )";
                    $searchTerm = '%' . $query . '%';
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
            } else {
                // Original search (title, abstract, author only)
                $sql .= " AND (t.title LIKE ? OR t.abstract LIKE ? OR u.name LIKE ?)";
                $searchTerm = '%' . $query . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
        }

        // Status filter
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        // Strand filter
        if (!empty($filters['strand'])) {
            $sql .= " AND u.strand = ?";
            $params[] = $filters['strand'];
        }

        // Academic year filter
        if (!empty($filters['academic_year'])) {
            $sql .= " AND t.academic_year = ?";
            $params[] = $filters['academic_year'];
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $sql .= " AND t.submission_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND t.submission_date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enhance results with keyword snippets if we have a search query
        if (!empty($query) && !empty($results)) {
            require_once __DIR__ . '/../helpers/SearchHelper.php';

            foreach ($results as &$result) {
                // Try to get page-level text first for accurate page numbers
                $pageTexts = $this->getPageLevelText($result['id']);

                if ($pageTexts !== null) {
                    // Use accurate page-by-page search
                    $snippets = SearchHelper::searchByPage(
                        $pageTexts,
                        $query,
                        3,  // Max 3 snippets
                        100 // 100 chars context on each side
                    );
                    $result['search_snippets'] = $snippets;

                    // Count total occurrences across all pages
                    $totalOccurrences = 0;
                    foreach ($pageTexts as $pageText) {
                        $totalOccurrences += SearchHelper::countKeywordOccurrences($pageText, $query);
                    }

                    $result['keyword_stats'] = [
                        'total_occurrences' => $totalOccurrences,
                        'unique_terms' => count(array_unique(array_column($snippets, 'search_term'))),
                        'sections' => []
                    ];
                } elseif (!empty($result['full_text_content'])) {
                    // Fallback to full-text search with estimated pages
                    $snippets = SearchHelper::extractHighlightedSnippets(
                        $result['full_text_content'],
                        $query,
                        3,  // Max 3 snippets
                        100 // 100 chars context on each side
                    );
                    $result['search_snippets'] = $snippets;

                    // Get keyword statistics
                    $stats = SearchHelper::getKeywordStats($result['full_text_content'], $query);
                    $result['keyword_stats'] = $stats;
                } else {
                    $result['search_snippets'] = [];
                    $result['keyword_stats'] = ['total_occurrences' => 0, 'unique_terms' => 0, 'sections' => []];
                }

                // Add highlighted title and abstract
                $result['highlighted_title'] = SearchHelper::highlightTitle($result['title'] ?? '', $query);
                $result['highlighted_abstract'] = SearchHelper::highlightAbstract($result['abstract'] ?? '', $query, 250);
            }
        }

        // Debug logging
        if (isset($_GET['debug_search'])) {
            error_log("Thesis Search Debug:");
            error_log("Query: $query");
            error_log("SQL: $sql");
            error_log("Params: " . json_encode($params));
            error_log("Results count: " . count($results));
        }

        return $results;
    }

    /**
     * Delete a thesis
     */
    public function delete($id) {
        // First get the file path to delete the file
        $thesis = $this->find($id);
        if ($thesis && !empty($thesis['file_path'])) {
            $filePath = __DIR__ . '/../' . $thesis['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete from database
        $stmt = $this->db->prepare("DELETE FROM theses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Save page-level text extraction to database
     */
    private function savePageLevelText($thesisId, $pageData) {
        if (empty($pageData['pages'])) {
            return;
        }

        try {
            // Check if thesis_pages table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'thesis_pages'")->fetch();
            if (!$tableCheck) {
                return; // Table doesn't exist yet
            }

            // Delete existing pages for this thesis (in case of re-upload)
            $deleteStmt = $this->db->prepare("DELETE FROM thesis_pages WHERE thesis_id = ?");
            $deleteStmt->execute([$thesisId]);

            // Insert each page
            $insertStmt = $this->db->prepare("
                INSERT INTO thesis_pages (thesis_id, page_number, page_text)
                VALUES (?, ?, ?)
            ");

            foreach ($pageData['pages'] as $pageNum => $pageText) {
                $insertStmt->execute([$thesisId, $pageNum, $pageText]);
            }
        } catch (Exception $e) {
            error_log("Error saving page-level text: " . $e->getMessage());
        }
    }

    /**
     * Get page-level text for a thesis
     * Returns array with page numbers as keys
     */
    public function getPageLevelText($thesisId) {
        try {
            // Check if thesis_pages table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'thesis_pages'")->fetch();
            if (!$tableCheck) {
                return null; // Table doesn't exist
            }

            $stmt = $this->db->prepare("
                SELECT page_number, page_text
                FROM thesis_pages
                WHERE thesis_id = ?
                ORDER BY page_number ASC
            ");
            $stmt->execute([$thesisId]);
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($pages)) {
                return null;
            }

            // Convert to array with page numbers as keys
            $pageTexts = [];
            foreach ($pages as $page) {
                $pageTexts[$page['page_number']] = $page['page_text'];
            }

            return $pageTexts;
        } catch (Exception $e) {
            error_log("Error retrieving page-level text: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add required database columns if they don't exist
     */
    public function ensureRequiredColumns() {
        try {
            // Add is_public column if it doesn't exist
            if (!$this->columnExists('is_public')) {
                $this->db->exec("ALTER TABLE theses ADD COLUMN is_public TINYINT(1) DEFAULT 1");
            }

            // Add publication_date column if it doesn't exist
            if (!$this->columnExists('publication_date')) {
                $this->db->exec("ALTER TABLE theses ADD COLUMN publication_date TIMESTAMP NULL");
            }

            // Add approval_date column if it doesn't exist
            if (!$this->columnExists('approval_date')) {
                $this->db->exec("ALTER TABLE theses ADD COLUMN approval_date TIMESTAMP NULL");
            }

            // Add updated_at column if it doesn't exist
            if (!$this->columnExists('updated_at')) {
                $this->db->exec("ALTER TABLE theses ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }

            // Add full_text_content column for PDF text search if it doesn't exist
            if (!$this->columnExists('full_text_content')) {
                $this->db->exec("ALTER TABLE theses ADD COLUMN full_text_content LONGTEXT NULL");
                $this->db->exec("ALTER TABLE theses ADD FULLTEXT INDEX idx_full_text_search (title, abstract, full_text_content)");
            }

            // Update existing approved theses to be public
            $this->db->exec("UPDATE theses SET is_public = 1, publication_date = COALESCE(publication_date, created_at) WHERE status = 'approved' AND is_public IS NULL");

            return true;
        } catch (Exception $e) {
            error_log("Error adding database columns: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment view count for a thesis (if column exists)
     */
    public function incrementViewCount($id) {
        if ($this->columnExists('view_count')) {
            $stmt = $this->db->prepare("UPDATE theses SET view_count = COALESCE(view_count, 0) + 1 WHERE id = ?");
            return $stmt->execute([$id]);
        }
        return true; // Don't fail if column doesn't exist
    }
}
?>