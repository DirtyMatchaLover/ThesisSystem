<?php
require_once __DIR__ . '/Database.php';

class Thesis {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance(); //  Fixed method call
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
        
        // Get current academic year (adjust logic as needed)
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
     * Get approved and public theses
     */
    public function approvedPublic() {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as author 
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.status = 'approved' AND t.is_public = 1 
            ORDER BY t.publication_date DESC, t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update thesis status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("
            UPDATE theses 
            SET status = ?, 
                updated_at = CURRENT_TIMESTAMP,
                approval_date = CASE WHEN ? = 'approved' THEN CURRENT_TIMESTAMP ELSE approval_date END,
                publication_date = CASE WHEN ? = 'approved' THEN CURRENT_TIMESTAMP ELSE publication_date END,
                is_public = CASE WHEN ? = 'approved' THEN 1 ELSE is_public END
            WHERE id = ?
        ");
        return $stmt->execute([$status, $status, $status, $status, $id]);
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

        // Only public for non-admin searches
        if (!empty($filters['public_only'])) {
            $sql .= " AND t.is_public = 1 AND t.status = 'approved'";
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update thesis file information
     */
    public function updateFileInfo($id, $file_path, $file_size, $original_filename) {
        $stmt = $this->db->prepare("
            UPDATE theses 
            SET file_path = ?, file_size = ?, original_filename = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        return $stmt->execute([$file_path, $file_size, $original_filename, $id]);
    }

    /**
     * Increment view count
     */
    public function incrementViewCount($id) {
        $stmt = $this->db->prepare("
            UPDATE theses 
            SET view_count = view_count + 1 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount($id) {
        $stmt = $this->db->prepare("
            UPDATE theses 
            SET download_count = download_count + 1 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Get theses by strand
     */
    public function getByStrand($strand) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as author, u.strand
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE u.strand = ? AND t.status = 'approved' AND t.is_public = 1
            ORDER BY t.publication_date DESC
        ");
        $stmt->execute([$strand]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent theses
     */
    public function getRecent($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as author 
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.status = 'approved' AND t.is_public = 1
            ORDER BY t.publication_date DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get thesis count by status
     */
    public function getCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM theses WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get popular theses (most viewed)
     */
    public function getPopular($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.name as author 
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.status = 'approved' AND t.is_public = 1
            ORDER BY t.view_count DESC, t.download_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update thesis metadata
     */
    public function updateMetadata($id, $data) {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $params[] = $data['title'];
        }

        if (isset($data['abstract'])) {
            $fields[] = 'abstract = ?';
            $params[] = $data['abstract'];
        }

        if (isset($data['introduction'])) {
            $fields[] = 'introduction = ?';
            $params[] = $data['introduction'];
        }

        if (isset($data['methodology'])) {
            $fields[] = 'methodology = ?';
            $params[] = $data['methodology'];
        }

        if (isset($data['research_type'])) {
            $fields[] = 'research_type = ?';
            $params[] = $data['research_type'];
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        $params[] = $id;

        $sql = "UPDATE theses SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete thesis and its associated data
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // Get file path before deletion
            $stmt = $this->db->prepare("SELECT file_path FROM theses WHERE id = ?");
            $stmt->execute([$id]);
            $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($thesis) {
                // Delete comments first (foreign key constraint)
                $stmt = $this->db->prepare("DELETE FROM thesis_comments WHERE thesis_id = ?");
                $stmt->execute([$id]);

                // Delete thesis categories
                $stmt = $this->db->prepare("DELETE FROM thesis_categories WHERE thesis_id = ?");
                $stmt->execute([$id]);

                // Delete thesis keywords
                $stmt = $this->db->prepare("DELETE FROM thesis_keywords WHERE thesis_id = ?");
                $stmt->execute([$id]);

                // Delete thesis revisions
                $stmt = $this->db->prepare("DELETE FROM thesis_revisions WHERE thesis_id = ?");
                $stmt->execute([$id]);

                // Delete the main thesis record
                $stmt = $this->db->prepare("DELETE FROM theses WHERE id = ?");
                $stmt->execute([$id]);

                // Delete the physical file
                $file_path = __DIR__ . '/../' . $thesis['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting thesis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get thesis activity/history
     */
    public function getActivity($thesis_id) {
        $stmt = $this->db->prepare("
            SELECT 
                tc.*,
                u.name as user_name,
                u.role as user_role
            FROM thesis_comments tc
            LEFT JOIN users u ON tc.user_id = u.id
            WHERE tc.thesis_id = ?
            ORDER BY tc.created_at ASC
        ");
        $stmt->execute([$thesis_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}