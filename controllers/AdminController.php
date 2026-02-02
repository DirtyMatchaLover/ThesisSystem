<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Thesis.php';
require_once __DIR__ . '/../models/SystemMetrics.php';

class AdminController {
    private $userModel;
    private $thesisModel;
    private $metricsModel;

    public function __construct() {
        $this->userModel = new User();
        $this->thesisModel = new Thesis();
        $this->metricsModel = new SystemMetrics();
    }

    // Show admin dashboard
    public function dashboard() {
        $stats = $this->thesisModel->getStats();
        $all = $this->thesisModel->all();

        // Get system metrics for admin
        $systemMetrics = $this->metricsModel->getSystemMetrics();
        $recentActivity = $this->metricsModel->getRecentActivity(10);
        $systemTrends = $this->metricsModel->getSystemTrends(7);

        // Add author information if missing
        foreach ($all as &$thesis) {
            if (empty($thesis['author']) && !empty($thesis['author_id'])) {
                // Fetch author name from users table
                try {
                    $db = Database::getInstance();
                    $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
                    $stmt->execute([$thesis['author_id']]);
                    $user = $stmt->fetch();
                    $thesis['author'] = $user['name'] ?? 'Unknown Author';
                } catch (Exception $e) {
                    $thesis['author'] = 'Unknown Author';
                }
            }
        }

        include __DIR__ . '/../views/admin/dashboard.php';
    }

    // Approve a thesis
    public function approve($id) {
        if (!$id || !is_numeric($id)) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid thesis ID']);
                exit;
            }
            set_flash('error', 'Invalid thesis ID.');
            redirect('admin/dashboard');
        }

        try {
            // Get thesis details for logging
            $thesis = $this->thesisModel->find($id);
            if (!$thesis) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo json_encode(['success' => false, 'message' => 'Thesis not found']);
                    exit;
                }
                set_flash('error', 'Thesis not found.');
                redirect('admin/dashboard');
            }

            // Record who approved and when
            $approver_id = current_user()['id'];
            $success = $this->thesisModel->updateStatus($id, 'approved', $approver_id);

            if ($success) {
                // Log the approval
                $this->logActivity($approver_id, 'thesis_approved', $id);
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Thesis approved successfully',
                        'new_status' => 'approved'
                    ]);
                    exit;
                }
                
                set_flash('success', 'Thesis "' . $thesis['title'] . '" approved successfully.');
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo json_encode(['success' => false, 'message' => 'Failed to approve thesis']);
                    exit;
                }
                set_flash('error', 'Failed to approve thesis.');
            }
        } catch (Exception $e) {
            error_log("Thesis approval error: " . $e->getMessage());
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo json_encode(['success' => false, 'message' => 'An error occurred while approving']);
                exit;
            }
            set_flash('error', 'An error occurred while approving the thesis.');
        }

        redirect('admin/dashboard');
    }

    // Reject a thesis (mark as rejected or under review based on context)
    public function reject($id) {
        if (!$id || !is_numeric($id)) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid thesis ID']);
                exit;
            }
            set_flash('error', 'Invalid thesis ID.');
            redirect('admin/dashboard');
        }

        try {
            // Get thesis details for logging
            $thesis = $this->thesisModel->find($id);
            if (!$thesis) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo json_encode(['success' => false, 'message' => 'Thesis not found']);
                    exit;
                }
                set_flash('error', 'Thesis not found.');
                redirect('admin/dashboard');
            }

            // Determine new status based on current status
            // If currently approved, move to under_review (for re-review)
            // Otherwise, mark as rejected
            $newStatus = ($thesis['status'] === 'approved') ? 'under_review' : 'rejected';
            $success = $this->thesisModel->updateStatus($id, $newStatus);

            if ($success) {
                // Log the rejection/review request
                $action = ($newStatus === 'rejected') ? 'thesis_rejected' : 'thesis_review_requested';
                $this->logActivity(current_user()['id'], $action, $id);

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $message = ($newStatus === 'rejected') ? 'Thesis rejected' : 'Thesis marked for review';
                    echo json_encode([
                        'success' => true,
                        'message' => $message,
                        'new_status' => $newStatus
                    ]);
                    exit;
                }

                $flashMessage = ($newStatus === 'rejected')
                    ? 'Thesis "' . $thesis['title'] . '" has been rejected.'
                    : 'Thesis "' . $thesis['title'] . '" marked for review.';
                set_flash('success', $flashMessage);
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    echo json_encode(['success' => false, 'message' => 'Failed to update thesis status']);
                    exit;
                }
                set_flash('error', 'Failed to update thesis status.');
            }
        } catch (Exception $e) {
            error_log("Thesis rejection error: " . $e->getMessage());

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo json_encode(['success' => false, 'message' => 'An error occurred while processing']);
                exit;
            }
            set_flash('error', 'An error occurred while updating the thesis status.');
        }

        redirect('admin/dashboard');
    }

    // View thesis details in modal/overlay
    public function viewThesis($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            exit('Thesis not found');
        }

        $thesis = $this->thesisModel->find($id);
        if (!$thesis) {
            http_response_code(404);
            exit('Thesis not found');
        }

        // If it's an AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($thesis);
            exit;
        }

        // Otherwise show the view
        require __DIR__ . '/../views/admin/view_thesis.php';
    }

    /**
     * Display PDF viewer page (shows thesis info + embedded PDF)
     * Route: admin/pdfview
     */
    public function pdfView($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>Thesis Not Found</h2>";
            echo "<p>The thesis you're looking for doesn't exist.</p>";
            echo "<a href='" . route('admin/dashboard') . "' class='btn btn-primary'>← Back to Dashboard</a>";
            echo "</div>";
            exit;
        }

        $thesis = $this->thesisModel->find($id);
        if (!$thesis) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>Thesis Not Found</h2>";
            echo "<p>The thesis you're looking for doesn't exist or you don't have permission to view it.</p>";
            echo "<a href='" . route('admin/dashboard') . "' class='btn btn-primary'>← Back to Dashboard</a>";
            echo "</div>";
            exit;
        }

        // Log view activity (optional)
        try {
            $this->logActivity(current_user()['id'], 'thesis_viewed', $id);
        } catch (Exception $e) {
            // Continue if logging fails
            error_log("Failed to log thesis view activity: " . $e->getMessage());
        }

        // Pass thesis data to the PDF viewer template
        require __DIR__ . '/../views/admin/pdf_viewer.php';
    }

    /**
     * Serve PDF file directly (for iframe embedding)
     * Route: admin/viewpdf
     */
    public function viewPdf($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>File Not Found</h2>";
            echo "<p>The PDF file you're looking for doesn't exist.</p>";
            echo "</div>";
            exit;
        }

        $thesis = $this->thesisModel->find($id);
        if (!$thesis || empty($thesis['file_path'])) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>File Not Found</h2>";
            echo "<p>This thesis doesn't have an associated PDF file.</p>";
            echo "</div>";
            exit;
        }

        // Validate file path to prevent directory traversal
        $uploadsDir = __DIR__ . '/../uploads/theses/';
        $filePath = __DIR__ . '/../' . $thesis['file_path'];
        $realPath = realpath($filePath);
        $realUploadsDir = realpath($uploadsDir);

        // Ensure the file exists and is within the uploads directory
        if (!$realPath || !$realUploadsDir || strpos($realPath, $realUploadsDir) !== 0) {
            error_log("Security: Attempted directory traversal in viewPdf - Path: " . $thesis['file_path'] . " - User: " . current_user()['email']);
            http_response_code(403);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>Access Denied</h2>";
            echo "<p>Invalid file path.</p>";
            echo "</div>";
            exit;
        }

        if (!file_exists($realPath)) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>File Not Found on Server</h2>";
            echo "<p>The PDF file exists in the database but not on the server.</p>";
            echo "</div>";
            exit;
        }

        // Check if file is actually a PDF
        if (function_exists('finfo_open')) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $realPath);
            finfo_close($fileInfo);

            if ($mimeType !== 'application/pdf') {
                http_response_code(415);
                echo "<div style='text-align: center; padding: 50px;'>";
                echo "<h2>Invalid File Type</h2>";
                echo "<p>File is not a valid PDF (detected type: " . htmlspecialchars($mimeType) . ")</p>";
                echo "</div>";
                exit;
            }
        }

        // Set headers for PDF viewing in browser (not download)
        $filename = $thesis['original_filename'] ?? basename($thesis['file_path']);
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"'); // 'inline' for viewing
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: public, max-age=3600');
        header('Pragma: public');

        // Clear any output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Output the PDF file
        readfile($realPath);
        exit;
    }

    /**
     * Comments page (simple HTML page, no JavaScript)
     * Route: admin/comments
     */
    public function comments($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>Thesis Not Found</h2>";
            echo "<p>The thesis you're looking for doesn't exist.</p>";
            echo "<a href='" . route('admin/dashboard') . "' class='btn btn-primary'>← Back to Dashboard</a>";
            echo "</div>";
            exit;
        }

        $thesis = $this->thesisModel->find($id);
        if (!$thesis) {
            http_response_code(404);
            echo "<div style='text-align: center; padding: 50px;'>";
            echo "<h2>Thesis Not Found</h2>";
            echo "<p>The thesis you're looking for doesn't exist or you don't have permission to view it.</p>";
            echo "<a href='" . route('admin/dashboard') . "' class='btn btn-primary'>← Back to Dashboard</a>";
            echo "</div>";
            exit;
        }

        // Handle comment submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment'])) {
            $comment = trim($_POST['comment']);
            $type = $_POST['type'] ?? 'feedback';
            
            if ($comment) {
                $success = $this->saveComment($id, current_user()['id'], $type, $comment);
                if ($success) {
                    set_flash('success', 'Comment added successfully');
                } else {
                    set_flash('error', 'Failed to add comment');
                }
                redirect("admin/comments&id=$id");
            }
        }

        // Get existing comments
        $comments = $this->getComments($id);

        require __DIR__ . '/../views/admin/comments.php';
    }

    // Download thesis file
    public function downloadThesis($id) {
        if (!$id || !is_numeric($id)) {
            http_response_code(404);
            exit('File not found');
        }

        $thesis = $this->thesisModel->find($id);
        if (!$thesis || empty($thesis['file_path'])) {
            http_response_code(404);
            exit('File not found');
        }

        // Validate file path to prevent directory traversal
        $uploadsDir = __DIR__ . '/../uploads/theses/';
        $filePath = __DIR__ . '/../' . $thesis['file_path'];
        $realPath = realpath($filePath);
        $realUploadsDir = realpath($uploadsDir);

        // Ensure the file exists and is within the uploads directory
        if (!$realPath || !$realUploadsDir || strpos($realPath, $realUploadsDir) !== 0) {
            error_log("Security: Attempted directory traversal in downloadThesis - Path: " . $thesis['file_path'] . " - User: " . current_user()['email']);
            http_response_code(403);
            exit('Access denied');
        }

        if (!file_exists($realPath)) {
            http_response_code(404);
            exit('File not found on server');
        }

        // Log download activity
        $this->logActivity(current_user()['id'], 'thesis_downloaded', $id);

        // Set headers for file download
        $filename = $thesis['original_filename'] ?? basename($thesis['file_path']);
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename); // Sanitize filename

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: private');
        header('Pragma: private');
        header('Expires: 0');

        // Clear output buffer
        ob_clean();
        flush();

        readfile($realPath);
        exit;
    }

    // Delete a thesis
    public function delete($id) {
        if (!$id || !is_numeric($id)) {
            set_flash('error', 'Invalid thesis ID.');
            redirect('admin/dashboard');
        }

        try {
            // Get thesis details before deletion for better logging
            $thesis = $this->thesisModel->find($id);
            if (!$thesis) {
                set_flash('error', 'Thesis not found.');
                redirect('admin/dashboard');
            }

            // Check if user has permission to delete
            $currentUser = current_user();
            $canDelete = false;

            // Admin and Faculty can both delete any thesis
            if (in_array($currentUser['role'], ['admin', 'faculty'])) {
                $canDelete = true;
            }

            if (!$canDelete) {
                set_flash('error', 'You do not have permission to delete this thesis.');
                redirect('admin/dashboard');
            }

            // Delete the thesis and associated file
            $success = $this->thesisModel->delete($id);

            if ($success) {
                // Delete the physical PDF file if it exists
                if (!empty($thesis['file_path'])) {
                    $filePath = __DIR__ . '/../' . $thesis['file_path'];
                    if (file_exists($filePath)) {
                        @unlink($filePath); // @ suppresses errors if file doesn't exist
                    }
                }

                $this->logActivity($currentUser['id'], 'thesis_deleted', $id);

                // Log with more detail if it was an approved thesis
                if ($thesis['status'] === 'approved') {
                    error_log("IMPORTANT: Approved thesis deleted - ID: {$id}, Title: {$thesis['title']}, By: {$currentUser['name']} ({$currentUser['email']})");
                }

                set_flash('success', 'Thesis "' . htmlspecialchars($thesis['title']) . '" deleted successfully.');
            } else {
                set_flash('error', 'Failed to delete thesis.');
            }
        } catch (Exception $e) {
            error_log("Thesis deletion error: " . $e->getMessage());
            set_flash('error', 'An error occurred while deleting the thesis.');
        }

        redirect('admin/dashboard');
    }

    // Manage users
    public function users() {
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
            
            require __DIR__ . '/../views/admin/users.php';
        } catch (Exception $e) {
            error_log("Admin users error: " . $e->getMessage());
            set_flash('error', 'Failed to load users.');
            redirect('admin/dashboard');
        }
    }

    // Generate reports
    public function reports() {
        try {
            $stats = $this->thesisModel->getStats();
            
            // Get monthly submission data for charts
            $db = Database::getInstance();
            $stmt = $db->query("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as submissions,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
                FROM theses 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ");
            $monthlyData = $stmt->fetchAll();
            
            // Get top authors
            $stmt = $db->query("
                SELECT 
                    u.name,
                    u.role,
                    COUNT(t.id) as thesis_count,
                    SUM(CASE WHEN t.status = 'approved' THEN 1 ELSE 0 END) as approved_count
                FROM users u
                JOIN theses t ON u.id = t.user_id
                GROUP BY u.id, u.name, u.role
                HAVING thesis_count > 0
                ORDER BY approved_count DESC, thesis_count DESC
                LIMIT 10
            ");
            $topAuthors = $stmt->fetchAll();
            
            require __DIR__ . '/../views/admin/reports.php';
        } catch (Exception $e) {
            error_log("Admin reports error: " . $e->getMessage());
            set_flash('error', 'Failed to generate reports.');
            redirect('admin/dashboard');
        }
    }

    // Bulk actions for theses
    public function bulkAction() {
        if (!is_post() || !verify_csrf_token()) {
            set_flash('error', 'Invalid request.');
            redirect('admin/dashboard');
        }

        $action = post('action');
        $thesisIds = post('thesis_ids', []);

        if (empty($thesisIds) || !is_array($thesisIds)) {
            set_flash('error', 'No theses selected.');
            redirect('admin/dashboard');
        }

        $successCount = 0;
        $totalCount = count($thesisIds);

        foreach ($thesisIds as $id) {
            if (!is_numeric($id)) continue;

            try {
                switch ($action) {
                    case 'approve':
                        if ($this->thesisModel->updateStatus($id, 'approved', current_user()['id'])) {
                            $successCount++;
                            $this->logActivity(current_user()['id'], 'thesis_approved', $id);
                        }
                        break;

                    case 'reject':
                        if ($this->thesisModel->updateStatus($id, 'under_review')) {
                            $successCount++;
                            $this->logActivity(current_user()['id'], 'thesis_review_requested', $id);
                        }
                        break;

                    case 'delete':
                        if ($this->thesisModel->delete($id)) {
                            $successCount++;
                            $this->logActivity(current_user()['id'], 'thesis_deleted', $id);
                        }
                        break;
                }
            } catch (Exception $e) {
                error_log("Bulk action error for thesis $id: " . $e->getMessage());
            }
        }

        if ($successCount > 0) {
            set_flash('success', "Successfully processed $successCount out of $totalCount theses.");
        } else {
            set_flash('error', 'Failed to process any theses.');
        }

        redirect('admin/dashboard');
    }

    /**
     * Export thesis data as CSV
     */
    public function export() {
        try {
            $theses = $this->thesisModel->all();
            
            $filename = 'theses_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, [
                'ID', 'Title', 'Author', 'Status', 'Created Date', 
                'File Path', 'Academic Year', 'Strand'
            ]);
            
            // CSV data
            foreach ($theses as $thesis) {
                fputcsv($output, [
                    $thesis['id'],
                    $thesis['title'],
                    $thesis['author'] ?? $thesis['author_name'] ?? 'Unknown',
                    $thesis['status'],
                    $thesis['created_at'] ?? '',
                    $thesis['file_path'] ?? '',
                    $thesis['academic_year'] ?? '',
                    $thesis['strand'] ?? ''
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Export error: " . $e->getMessage());
            set_flash('error', 'Failed to export data.');
            redirect('admin/dashboard');
        }
    }

    /**
     * Save comment to database
     */
    private function saveComment($thesis_id, $user_id, $type, $comment) {
        try {
            $db = Database::getInstance();
            
            // Check if thesis_comments table exists, create if not
            $stmt = $db->query("SHOW TABLES LIKE 'thesis_comments'");
            if (!$stmt->fetch()) {
                // Create table if it doesn't exist
                $createTable = "
                    CREATE TABLE thesis_comments (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        thesis_id INT NOT NULL,
                        user_id INT NOT NULL,
                        comment_type VARCHAR(50) DEFAULT 'feedback',
                        content TEXT NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (thesis_id) REFERENCES theses(id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )
                ";
                $db->exec($createTable);
            }
            
            $stmt = $db->prepare("
                INSERT INTO thesis_comments (thesis_id, user_id, comment_type, content, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$thesis_id, $user_id, $type, $comment]);
        } catch (Exception $e) {
            error_log("Failed to save comment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get comments for thesis
     */
    private function getComments($thesis_id) {
        try {
            $db = Database::getInstance();
            
            // Check if table exists
            $stmt = $db->query("SHOW TABLES LIKE 'thesis_comments'");
            if (!$stmt->fetch()) {
                return []; // No table, no comments
            }
            
            $stmt = $db->prepare("
                SELECT c.*, u.name as user_name 
                FROM thesis_comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.thesis_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$thesis_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get comments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log administrative activity
     */
    private function logActivity($userId, $action, $thesisId = null) {
        try {
            $db = Database::getInstance();
            
            // Try to use activity_logs table first, fallback to simpler logging
            try {
                $stmt = $db->prepare("
                    INSERT INTO activity_logs (user_id, action, thesis_id, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $userId,
                    $action,
                    $thesisId,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
            } catch (Exception $e) {
                // Fallback to simpler logging
                $stmt = $db->prepare("
                    INSERT INTO user_activity (user_id, action, thesis_id, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$userId, $action, $thesisId]);
            }
        } catch (Exception $e) {
            // Don't fail the main operation if logging fails
            error_log("Activity logging failed: " . $e->getMessage());
        }
    }
}
?>