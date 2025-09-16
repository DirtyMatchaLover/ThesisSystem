<?php
require_once __DIR__ . '/Database.php';

class Thesis {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new thesis
     */
    public function create($user_id, $title, $abstract, $file_path, $status = 'submitted') {
        $stmt = $this->db->prepare("
            INSERT INTO theses (
                user_id, 
                title, 
                abstract, 
                file_path, 
                status, 
                submission_date,
                academic_year,
                created_at
            ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, CURRENT_TIMESTAMP)
        ");
        
        // Get current academic year
        $current_year = date('Y');
        $academic_year = $current_year . '-' . ($current_year + 1);
        
        return $stmt->execute([
            $user_id, 
            $title, 
            $abstract, 
            $file_path, 
            $status,
            $academic_year
        ]);
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
            $stmt = $this->db->prepare("SHOW COLUMNS FROM theses LIKE ?");
            $stmt->execute([$columnName]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update thesis status - ENHANCED VERSION
     */
    public function updateStatus($id, $status) {
        // Check if enhanced columns exist
        $hasIsPublicColumn = $this->columnExists('is_public');
        $hasPublicationDateColumn = $this->columnExists('publication_date');
        $hasApprovalDateColumn = $this->columnExists('approval_date');
        $hasUpdatedAtColumn = $this->columnExists('updated_at');

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
     * Search theses by title, author, or abstract
     */
    public function search($query, $filters = []) {
        $sql = "
            SELECT t.*, u.name as author, u.email as author_email, u.strand
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE 1=1
        ";
        $params = [];

        // Search query
        if (!empty($query)) {
            $sql .= " AND (t.title LIKE ? OR t.abstract LIKE ? OR u.name LIKE ?)";
            $searchTerm = '%' . $query . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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