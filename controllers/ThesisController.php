<?php
require_once __DIR__ . '/../models/Thesis.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers.php';

class ThesisController {
    private $thesisModel;
    
    public function __construct() { 
        $this->thesisModel = new Thesis(); 
    }

    public function create() {
        $success = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                $error = "Invalid request. Please try again.";
                $this->logSecurityEvent('csrf_failure', 'thesis_upload');
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Debug: Check if user is logged in
            if (!is_logged_in()) {
                $error = "You must be logged in to submit a thesis.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Get form data
            $title = trim($_POST['title'] ?? '');
            $abstract = trim($_POST['abstract'] ?? '');
            $author = trim($_POST['author'] ?? ''); // Not used in DB but kept for form
            $adviser = trim($_POST['adviser'] ?? ''); // Research adviser name
            $user_id = current_user()['id'];
            
            // Debug: Check if we have user ID
            if (!$user_id) {
                $error = "User session error. Please log in again.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Validate required fields
            if (!$title || !$abstract) {
                $error = "Title and abstract are required.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Validate field lengths
            if (strlen($title) > 500) {
                $error = "Title must not exceed 500 characters.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            if (strlen($abstract) > 5000) {
                $error = "Abstract must not exceed 5000 characters.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            if ($adviser && strlen($adviser) > 255) {
                $error = "Adviser name must not exceed 255 characters.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }
            
            // Check if file was uploaded
            if (empty($_FILES['file']) || empty($_FILES['file']['name'])) {
                $error = "Please select a PDF file to upload.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }
            
            // Validate file upload
            $file = $_FILES['file'];
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                switch ($file['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = "File is too large. Maximum size is 10MB.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error = "No file was uploaded.";
                        break;
                    default:
                        $error = "File upload failed with error code: " . $file['error'];
                }
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }
            
            // Validate file type by extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $error = "Only PDF files are allowed.";
                $this->logSecurityEvent('invalid_file_type', $file['name']);
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Validate MIME type for actual file content
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = ['application/pdf', 'application/x-pdf'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $error = "Invalid file type. Only PDF files are allowed.";
                $this->logSecurityEvent('mime_type_mismatch', $file['name'] . ' (' . $mimeType . ')');
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Additional PDF verification - check PDF header
            $fileHandle = fopen($file['tmp_name'], 'r');
            $header = fread($fileHandle, 5);
            fclose($fileHandle);

            if ($header !== '%PDF-') {
                $error = "File is not a valid PDF document.";
                $this->logSecurityEvent('invalid_pdf_header', $file['name']);
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }

            // Validate file size (10MB max)
            $maxBytes = 10 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $error = "File too large. Maximum size is 10MB.";
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }
            
            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads/theses';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $error = "Failed to create upload directory. Please contact administrator.";
                    error_log("Failed to create directory: " . $uploadDir);
                    require __DIR__ . '/../views/thesis/create.php';
                    return;
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                $error = "Upload directory is not writable. Please contact administrator.";
                error_log("Directory not writable: " . $uploadDir);
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }
            
            // Generate unique filename
            $safeName = preg_replace('/[^a-z0-9_\-]+/i', '-', strtolower(pathinfo($file['name'], PATHINFO_FILENAME)));
            $newName = $user_id . '_' . time() . '_' . $safeName . '.pdf';
            $destPath = $uploadDir . '/' . $newName;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $error = "Failed to save uploaded file. Please try again.";
                error_log("Failed to move file from " . $file['tmp_name'] . " to " . $destPath);
                require __DIR__ . '/../views/thesis/create.php';
                return;
            }
            
            // Save to database (relative path for web access)
            $dbPath = 'uploads/theses/' . $newName;

            // Create thesis record - with adviser
            $result = $this->thesisModel->create($user_id, $title, $abstract, $dbPath, 'submitted', $adviser);

            if ($result) {
                // Log successful upload
                $this->logSecurityEvent('thesis_uploaded', $title);

                // Success - redirect to list
                $_SESSION['flash_message'] = "Thesis uploaded successfully!";
                header('Location: ' . route('thesis/list'));
                exit;
            } else {
                // Database save failed - delete uploaded file
                unlink($destPath);
                $error = "Failed to save thesis information. Please try again.";
                error_log("Database save failed for thesis: " . $title);
            }
        }
        
        require __DIR__ . '/../views/thesis/create.php';
    }

    public function list() {
        require_login();
        
        $user = current_user();
        $items = $this->thesisModel->byUser($user['id']);
        
        require __DIR__ . '/../views/thesis/list.php';
    }

    public function show() {
        try {
            $id = (int)($_GET['id'] ?? 0);

            if (!$id) {
                http_response_code(404);
                $thesis = null;
                require __DIR__ . '/../views/thesis/show.php';
                return;
            }

            $thesis = $this->thesisModel->find($id);

            if (!$thesis) {
                http_response_code(404);
                $thesis = null;
                require __DIR__ . '/../views/thesis/show.php';
                return;
            }

            // Check access permissions
            if ($thesis['status'] !== 'approved' && $thesis['status'] !== 'published') {
                // If not approved/published, only the author or admin/faculty can view
                if (!is_logged_in()) {
                    http_response_code(403);
                    $thesis = null;
                    require __DIR__ . '/../views/thesis/show.php';
                    return;
                }

                $user = current_user();
                if ($user['id'] != $thesis['user_id'] && !in_array($user['role'], ['admin', 'faculty'])) {
                    http_response_code(403);
                    $thesis = null;
                    require __DIR__ . '/../views/thesis/show.php';
                    return;
                }
            }

            // Get author name from users table if not set
            if (empty($thesis['author']) && empty($thesis['author_name'])) {
                require_once __DIR__ . '/../models/Database.php';
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
                $stmt->execute([$thesis['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $thesis['author'] = $user['name'] ?? 'Unknown';
            } else {
                $thesis['author'] = $thesis['author_name'] ?? $thesis['author'] ?? 'Unknown';
            }

            // Prepare file path for viewing with security validation
            if (!empty($thesis['file_path'])) {
                // Validate file path to prevent directory traversal
                $uploadsDir = __DIR__ . '/../uploads/theses/';
                $fullPath = __DIR__ . '/../' . $thesis['file_path'];
                $realPath = realpath($fullPath);
                $realUploadsDir = realpath($uploadsDir);

                // Ensure the file exists and is within the uploads directory
                if (!$realPath || !$realUploadsDir || strpos($realPath, $realUploadsDir) !== 0) {
                    error_log("Security: Attempted directory traversal - Path: " . $thesis['file_path']);
                    http_response_code(403);
                    $thesis = null;
                    require __DIR__ . '/../views/thesis/show.php';
                    return;
                }

                // Ensure path starts with /
                $filePath = $thesis['file_path'];
                if (substr($filePath, 0, 1) !== '/') {
                    $filePath = '/' . $filePath;
                }
                $thesis['file_path'] = $filePath;
            }

            // Increment view count
            try {
                if (method_exists($this->thesisModel, 'incrementViewCount')) {
                    $this->thesisModel->incrementViewCount($id);
                }
            } catch (Exception $e) {
                error_log("Failed to increment view count: " . $e->getMessage());
            }

            require __DIR__ . '/../views/thesis/show.php';

        } catch (Exception $e) {
            error_log("Error in ThesisController::show: " . $e->getMessage());
            http_response_code(500);
            $thesis = null;
            require __DIR__ . '/../views/thesis/show.php';
        }
    }

    /**
     * Log security events for thesis operations
     */
    private function logSecurityEvent($event, $details) {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);

        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user = is_logged_in() ? current_user()['email'] : 'guest';

        $logEntry = sprintf(
            "[%s] %s | User: %s | IP: %s | Details: %s\n",
            $timestamp,
            strtoupper($event),
            $user,
            $ip,
            $details
        );

        @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}