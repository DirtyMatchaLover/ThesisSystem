<?php
require_once __DIR__ . '/../models/Database.php';

class BookmarkController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Toggle bookmark (add or remove)
     */
    public function toggle()
    {
        if (!is_logged_in()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Please login to bookmark theses']);
                exit;
            }
            redirect('auth/select');
        }

        $user = current_user();
        $thesisId = (int)($_POST['thesis_id'] ?? $_GET['thesis_id'] ?? 0);

        if (!$thesisId) {
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid thesis ID']);
                exit;
            }
            redirect('research');
        }

        try {
            // Check if already bookmarked
            $stmt = $this->db->prepare("
                SELECT id FROM thesis_bookmarks
                WHERE user_id = ? AND thesis_id = ?
            ");
            $stmt->execute([$user['id'], $thesisId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Remove bookmark
                $stmt = $this->db->prepare("
                    DELETE FROM thesis_bookmarks
                    WHERE user_id = ? AND thesis_id = ?
                ");
                $stmt->execute([$user['id'], $thesisId]);
                $isBookmarked = false;
                $message = 'Removed from favorites';
            } else {
                // Add bookmark
                $stmt = $this->db->prepare("
                    INSERT INTO thesis_bookmarks (user_id, thesis_id)
                    VALUES (?, ?)
                ");
                $stmt->execute([$user['id'], $thesisId]);
                $isBookmarked = true;
                $message = 'Added to favorites';
            }

            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'is_bookmarked' => $isBookmarked,
                    'message' => $message
                ]);
                exit;
            }

            set_flash('success', $message);
            $referer = $_SERVER['HTTP_REFERER'] ?? route('research');
            header('Location: ' . $referer);
            exit;

        } catch (Exception $e) {
            error_log("Bookmark error: " . $e->getMessage());

            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update bookmark']);
                exit;
            }

            set_flash('error', 'Failed to update bookmark');
            redirect('research');
        }
    }

    /**
     * Show user's bookmarked theses
     */
    public function index()
    {
        if (!is_logged_in()) {
            redirect('auth/select');
        }

        $user = current_user();

        try {
            // Get all bookmarked theses
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as author, u.email as author_email,
                       b.created_at as bookmarked_at
                FROM thesis_bookmarks b
                JOIN theses t ON b.thesis_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            require __DIR__ . '/../views/bookmarks/index.php';

        } catch (Exception $e) {
            error_log("Bookmarks page error: " . $e->getMessage());
            set_flash('error', 'Failed to load bookmarks');
            redirect('home');
        }
    }

    /**
     * Check if thesis is bookmarked by current user
     */
    public function isBookmarked($thesisId)
    {
        if (!is_logged_in()) {
            return false;
        }

        $user = current_user();

        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM thesis_bookmarks
                WHERE user_id = ? AND thesis_id = ?
            ");
            $stmt->execute([$user['id'], $thesisId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['count'] > 0;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
