<?php
/**
 * Activity Tracker Helper
 * Tracks user activities for analysis and reporting
 */

require_once __DIR__ . '/../models/Database.php';

class ActivityTracker {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Log a user activity
     */
    public function logActivity(
        $userId,
        $activityType,
        $description = null,
        $thesisId = null,
        $metadata = null
    ) {
        try {
            $sql = "INSERT INTO user_activities
                    (user_id, activity_type, activity_description, thesis_id,
                     metadata, session_id, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);

            $sessionId = session_id();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $metadataJson = $metadata ? json_encode($metadata) : null;

            $stmt->execute([
                $userId,
                $activityType,
                $description,
                $thesisId,
                $metadataJson,
                $sessionId,
                $ipAddress,
                $userAgent
            ]);

            // Update user statistics
            $this->updateUserStats($userId, $activityType);

            return true;
        } catch (PDOException $e) {
            error_log("Activity tracking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user statistics
     */
    private function updateUserStats($userId, $activityType) {
        try {
            // Create user stats record if doesn't exist
            $sql = "INSERT INTO user_statistics (user_id)
                    VALUES (?)
                    ON DUPLICATE KEY UPDATE user_id = user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);

            // Update specific stat based on activity type
            $statField = null;
            switch ($activityType) {
                case 'login':
                    $statField = 'total_logins';
                    // Also update last_login
                    $sql = "UPDATE user_statistics
                            SET last_login = NOW()
                            WHERE user_id = ?";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$userId]);
                    break;
                case 'thesis_upload':
                    $statField = 'theses_uploaded';
                    break;
                case 'thesis_view':
                    $statField = 'theses_viewed';
                    break;
                case 'thesis_download':
                    $statField = 'theses_downloaded';
                    break;
                case 'search':
                    $statField = 'total_searches';
                    break;
                case 'thesis_review':
                    $statField = 'theses_reviewed';
                    break;
                case 'comment':
                    $statField = 'comments_made';
                    break;
            }

            if ($statField) {
                $sql = "UPDATE user_statistics
                        SET $statField = $statField + 1
                        WHERE user_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Stats update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Start a user session
     */
    public function startSession($userId) {
        try {
            $sessionId = session_id();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $sql = "INSERT INTO user_sessions
                    (user_id, session_id, ip_address, user_agent)
                    VALUES (?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $sessionId, $ipAddress, $userAgent]);

            return true;
        } catch (PDOException $e) {
            error_log("Session start error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * End a user session
     */
    public function endSession($userId) {
        try {
            $sessionId = session_id();

            $sql = "UPDATE user_sessions
                    SET logout_at = NOW(), is_active = FALSE
                    WHERE user_id = ? AND session_id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $sessionId]);

            return true;
        } catch (PDOException $e) {
            error_log("Session end error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user activity report
     */
    public function getUserReport($userId) {
        try {
            $sql = "SELECT * FROM individual_user_report WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Report error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user activities (paginated)
     */
    public function getUserActivities($userId, $limit = 50, $offset = 0) {
        try {
            $sql = "SELECT
                        ua.*,
                        t.title AS thesis_title
                    FROM user_activities ua
                    LEFT JOIN theses t ON ua.thesis_id = t.id
                    WHERE ua.user_id = ?
                    ORDER BY ua.created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Activities fetch error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all users report (for admin/SOP analysis)
     */
    public function getAllUsersReport($role = null, $strand = null) {
        try {
            $sql = "SELECT * FROM individual_user_report WHERE 1=1";
            $params = [];

            if ($role) {
                $sql .= " AND role = ?";
                $params[] = $role;
            }

            if ($strand) {
                $sql .= " AND strand = ?";
                $params[] = $strand;
            }

            $sql .= " ORDER BY engagement_score DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("All users report error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export user data to CSV (for SOP documentation)
     */
    public function exportUserDataToCSV($userId, $outputPath = null) {
        try {
            $report = $this->getUserReport($userId);
            $activities = $this->getUserActivities($userId, 1000);

            if (!$outputPath) {
                $outputPath = "user_{$userId}_data_" . date('Y-m-d') . ".csv";
            }

            $fp = fopen($outputPath, 'w');

            // Write header info
            fputcsv($fp, ['User Data Export']);
            fputcsv($fp, ['Generated', date('Y-m-d H:i:s')]);
            fputcsv($fp, []);

            // Write user summary
            fputcsv($fp, ['User Summary']);
            foreach ($report as $key => $value) {
                fputcsv($fp, [$key, $value]);
            }

            fputcsv($fp, []);
            fputcsv($fp, ['Activity Log']);

            // Write activities header
            if (!empty($activities)) {
                fputcsv($fp, array_keys($activities[0]));

                // Write activity data
                foreach ($activities as $activity) {
                    fputcsv($fp, $activity);
                }
            }

            fclose($fp);
            return $outputPath;
        } catch (Exception $e) {
            error_log("CSV export error: " . $e->getMessage());
            return false;
        }
    }
}

// Helper function for easy access
function trackActivity($userId, $activityType, $description = null, $thesisId = null, $metadata = null) {
    $tracker = new ActivityTracker();
    return $tracker->logActivity($userId, $activityType, $description, $thesisId, $metadata);
}
