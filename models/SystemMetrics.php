<?php
require_once __DIR__ . '/Database.php';

class SystemMetrics {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get comprehensive system metrics
     */
    public function getSystemMetrics() {
        return [
            'users' => $this->getUserMetrics(),
            'theses' => $this->getThesisMetrics(),
            'storage' => $this->getStorageMetrics(),
            'activity' => $this->getActivityMetrics(),
            'performance' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserMetrics() {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'student' THEN 1 END) as students,
                    COUNT(CASE WHEN role = 'faculty' THEN 1 END) as faculty,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins,
                    COUNT(CASE WHEN role = 'librarian' THEN 1 END) as librarians,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_week,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_month
                FROM users
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user metrics: " . $e->getMessage());
            return [
                'total_users' => 0,
                'students' => 0,
                'faculty' => 0,
                'admins' => 0,
                'librarians' => 0,
                'new_users_week' => 0,
                'new_users_month' => 0
            ];
        }
    }

    /**
     * Get thesis statistics
     */
    private function getThesisMetrics() {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_theses,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft,
                    COUNT(CASE WHEN status = 'submitted' THEN 1 END) as submitted,
                    COUNT(CASE WHEN status = 'under_review' THEN 1 END) as under_review,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_theses_week,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_theses_month,
                    COALESCE(SUM(view_count), 0) as total_views,
                    COALESCE(SUM(download_count), 0) as total_downloads
                FROM theses
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting thesis metrics: " . $e->getMessage());
            return [
                'total_theses' => 0,
                'draft' => 0,
                'submitted' => 0,
                'under_review' => 0,
                'approved' => 0,
                'rejected' => 0,
                'new_theses_week' => 0,
                'new_theses_month' => 0,
                'total_views' => 0,
                'total_downloads' => 0
            ];
        }
    }

    /**
     * Get storage/file metrics
     */
    private function getStorageMetrics() {
        try {
            // Get file count and sizes
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(CASE WHEN file_path IS NOT NULL AND file_path != '' THEN 1 END) as total_files,
                    COUNT(CASE WHEN status = 'approved' AND file_path IS NOT NULL THEN 1 END) as published_files
                FROM theses
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate actual disk usage
            $uploadPath = __DIR__ . '/../uploads/theses/';
            $totalSize = 0;
            $fileCount = 0;

            if (is_dir($uploadPath)) {
                $files = glob($uploadPath . '*.pdf');
                $fileCount = count($files);
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $totalSize += filesize($file);
                    }
                }
            }

            return [
                'total_files' => $result['total_files'] ?? 0,
                'published_files' => $result['published_files'] ?? 0,
                'actual_file_count' => $fileCount,
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / (1024 * 1024), 2),
                'avg_file_size_mb' => $fileCount > 0 ? round(($totalSize / $fileCount) / (1024 * 1024), 2) : 0
            ];
        } catch (Exception $e) {
            error_log("Error getting storage metrics: " . $e->getMessage());
            return [
                'total_files' => 0,
                'published_files' => 0,
                'actual_file_count' => 0,
                'total_size_bytes' => 0,
                'total_size_mb' => 0,
                'avg_file_size_mb' => 0
            ];
        }
    }

    /**
     * Get recent activity metrics
     */
    private function getActivityMetrics() {
        try {
            // Activity in last 24 hours
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_activity,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as last_hour,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24_hours
                FROM theses
            ");
            $stmt->execute();
            $thesisActivity = $stmt->fetch(PDO::FETCH_ASSOC);

            // Most active strand
            $stmt = $this->db->prepare("
                SELECT
                    u.strand,
                    COUNT(t.id) as submission_count
                FROM theses t
                JOIN users u ON t.user_id = u.id
                WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND u.strand IS NOT NULL
                    AND u.strand != ''
                GROUP BY u.strand
                ORDER BY submission_count DESC
                LIMIT 1
            ");
            $stmt->execute();
            $mostActiveStrand = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'last_hour_submissions' => $thesisActivity['last_hour'] ?? 0,
                'last_24_hours' => $thesisActivity['last_24_hours'] ?? 0,
                'most_active_strand' => $mostActiveStrand['strand'] ?? 'N/A',
                'most_active_strand_count' => $mostActiveStrand['submission_count'] ?? 0
            ];
        } catch (Exception $e) {
            error_log("Error getting activity metrics: " . $e->getMessage());
            return [
                'last_hour_submissions' => 0,
                'last_24_hours' => 0,
                'most_active_strand' => 'N/A',
                'most_active_strand_count' => 0
            ];
        }
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics() {
        try {
            // Average approval time
            $stmt = $this->db->prepare("
                SELECT
                    AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_approval_hours
                FROM theses
                WHERE status IN ('approved', 'rejected')
                    AND created_at != updated_at
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $avgApprovalHours = $result['avg_approval_hours'] ?? 0;

            // Approval rate
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0) as approval_rate
                FROM theses
                WHERE status IN ('approved', 'rejected')
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $approvalRate = $result['approval_rate'] ?? 0;

            // System health indicators
            $dbConnected = $this->testDatabaseConnection();
            $uploadsWritable = is_writable(__DIR__ . '/../uploads/theses/');

            return [
                'avg_approval_hours' => round($avgApprovalHours, 1),
                'avg_approval_days' => round($avgApprovalHours / 24, 1),
                'approval_rate' => round($approvalRate, 1),
                'database_status' => $dbConnected ? 'Connected' : 'Error',
                'uploads_writable' => $uploadsWritable ? 'Yes' : 'No',
                'system_health' => ($dbConnected && $uploadsWritable) ? 'Healthy' : 'Warning'
            ];
        } catch (Exception $e) {
            error_log("Error getting performance metrics: " . $e->getMessage());
            return [
                'avg_approval_hours' => 0,
                'avg_approval_days' => 0,
                'approval_rate' => 0,
                'database_status' => 'Error',
                'uploads_writable' => 'Unknown',
                'system_health' => 'Error'
            ];
        }
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection() {
        try {
            $stmt = $this->db->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get recent activity log
     */
    public function getRecentActivity($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    t.id,
                    t.title,
                    t.status,
                    t.created_at,
                    t.updated_at,
                    u.name as author_name,
                    u.role as author_role
                FROM theses t
                JOIN users u ON t.user_id = u.id
                ORDER BY t.updated_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent activity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system trends (for charts)
     */
    public function getSystemTrends($days = 7) {
        try {
            $trends = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));

                // Get submissions for this day
                $stmt = $this->db->prepare("
                    SELECT
                        COUNT(*) as submissions,
                        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approvals
                    FROM theses
                    WHERE DATE(created_at) = ?
                ");
                $stmt->execute([$date]);
                $dayData = $stmt->fetch(PDO::FETCH_ASSOC);

                $trends[] = [
                    'date' => $date,
                    'label' => date('M j', strtotime($date)),
                    'submissions' => (int)$dayData['submissions'],
                    'approvals' => (int)$dayData['approvals']
                ];
            }

            return $trends;
        } catch (Exception $e) {
            error_log("Error getting system trends: " . $e->getMessage());
            return [];
        }
    }
}
