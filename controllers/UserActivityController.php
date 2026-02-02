<?php
/**
 * User Activity Controller
 * Handles individual and aggregated user activity reports
 * For SOP (Statement of Problem) analysis
 */

require_once __DIR__ . '/../models/Database.php';

class UserActivityController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Individual User Activity Report
     * Shows data for ONE specific user
     */
    public function individualReport() {
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            set_flash('error', 'User ID required');
            redirect('admin/users');
        }

        try {
            // Get user info
            $stmt = $this->db->prepare("
                SELECT id, name, email, role, created_at
                FROM users
                WHERE id = ? AND role IN ('librarian', 'faculty', 'student')
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                set_flash('error', 'User not found or invalid role');
                redirect('admin/users');
            }

            // Get individual user statistics
            $stats = $this->getIndividualUserStats($userId);

            // Get individual user activities
            $activities = $this->getIndividualUserActivities($userId);

            require __DIR__ . '/../views/admin/individual_report.php';
        } catch (Exception $e) {
            error_log("Individual report error: " . $e->getMessage());
            set_flash('error', 'Failed to load user report');
            redirect('admin/users');
        }
    }

    /**
     * Combined/Aggregated Report
     * Shows data for ALL users (librarians, faculty, students)
     * For SOP analysis
     */
    public function combinedReport() {
        try {
            // Get aggregated statistics for all tracked users
            $aggregatedStats = $this->getAggregatedStats();

            // Get breakdown by role
            $roleBreakdown = $this->getRoleBreakdown();

            // Get common activities
            $commonActivities = $this->getCommonActivities();

            // Get time-based analysis
            $timeAnalysis = $this->getTimeAnalysis();

            require __DIR__ . '/../views/admin/combined_report.php';
        } catch (Exception $e) {
            error_log("Combined report error: " . $e->getMessage());
            set_flash('error', 'Failed to load combined report');
            redirect('admin/dashboard');
        }
    }

    /**
     * Get statistics for ONE individual user
     */
    private function getIndividualUserStats($userId) {
        // Total activities
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_activities
            FROM user_activities
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalActivities = $stmt->fetch(PDO::FETCH_ASSOC)['total_activities'];

        // Total logins
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_logins
            FROM user_sessions
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $totalLogins = $stmt->fetch(PDO::FETCH_ASSOC)['total_logins'];

        // Average session duration
        $stmt = $this->db->prepare("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, login_at, logout_at)) as avg_session_minutes
            FROM user_sessions
            WHERE user_id = ? AND logout_at IS NOT NULL
        ");
        $stmt->execute([$userId]);
        $avgSession = $stmt->fetch(PDO::FETCH_ASSOC)['avg_session_minutes'];

        // Thesis interactions
        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN activity_type = 'thesis_upload' THEN 1 END) as uploads,
                COUNT(CASE WHEN activity_type = 'thesis_view' THEN 1 END) as views,
                COUNT(CASE WHEN activity_type = 'thesis_download' THEN 1 END) as downloads,
                COUNT(CASE WHEN activity_type = 'thesis_search' THEN 1 END) as searches
            FROM user_activities
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $thesisStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Most active day
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as activity_date, COUNT(*) as count
            FROM user_activities
            WHERE user_id = ?
            GROUP BY DATE(created_at)
            ORDER BY count DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $mostActiveDay = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_activities' => $totalActivities,
            'total_logins' => $totalLogins,
            'avg_session_minutes' => round($avgSession ?: 0, 1),
            'thesis_uploads' => $thesisStats['uploads'],
            'thesis_views' => $thesisStats['views'],
            'thesis_downloads' => $thesisStats['downloads'],
            'thesis_searches' => $thesisStats['searches'],
            'most_active_day' => $mostActiveDay['activity_date'] ?? 'N/A',
            'most_active_count' => $mostActiveDay['count'] ?? 0
        ];
    }

    /**
     * Get recent activities for ONE individual user
     */
    private function getIndividualUserActivities($userId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT
                ua.activity_type,
                ua.activity_description,
                ua.created_at,
                t.title as thesis_title
            FROM user_activities ua
            LEFT JOIN theses t ON ua.thesis_id = t.id
            WHERE ua.user_id = ?
            ORDER BY ua.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get AGGREGATED statistics for ALL users (librarians, faculty, students)
     */
    private function getAggregatedStats() {
        // Count users by role
        $stmt = $this->db->query("
            SELECT
                COUNT(CASE WHEN role = 'librarian' THEN 1 END) as librarians,
                COUNT(CASE WHEN role = 'faculty' THEN 1 END) as faculty,
                COUNT(CASE WHEN role = 'student' THEN 1 END) as students,
                COUNT(*) as total_users
            FROM users
            WHERE role IN ('librarian', 'faculty', 'student')
            AND status = 'active'
        ");
        $userCounts = $stmt->fetch(PDO::FETCH_ASSOC);

        // Total activities across all tracked users
        $stmt = $this->db->query("
            SELECT COUNT(*) as total_activities
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            WHERE u.role IN ('librarian', 'faculty', 'student')
        ");
        $totalActivities = $stmt->fetch(PDO::FETCH_ASSOC)['total_activities'];

        // Average activities per user
        $avgActivitiesPerUser = $userCounts['total_users'] > 0
            ? round($totalActivities / $userCounts['total_users'], 1)
            : 0;

        // Total logins
        $stmt = $this->db->query("
            SELECT COUNT(*) as total_logins
            FROM user_sessions us
            JOIN users u ON us.user_id = u.id
            WHERE u.role IN ('librarian', 'faculty', 'student')
        ");
        $totalLogins = $stmt->fetch(PDO::FETCH_ASSOC)['total_logins'];

        // Average session duration
        $stmt = $this->db->query("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, login_at, logout_at)) as avg_session_minutes
            FROM user_sessions us
            JOIN users u ON us.user_id = u.id
            WHERE u.role IN ('librarian', 'faculty', 'student')
            AND logout_at IS NOT NULL
        ");
        $avgSession = $stmt->fetch(PDO::FETCH_ASSOC)['avg_session_minutes'];

        // System usage score (0-100)
        $usageScore = min(100, ($avgActivitiesPerUser / 10) * 100);

        return [
            'total_users' => $userCounts['total_users'],
            'librarians' => $userCounts['librarians'],
            'faculty' => $userCounts['faculty'],
            'students' => $userCounts['students'],
            'total_activities' => $totalActivities,
            'avg_activities_per_user' => $avgActivitiesPerUser,
            'total_logins' => $totalLogins,
            'avg_session_minutes' => round($avgSession ?: 0, 1),
            'usage_score' => round($usageScore, 1)
        ];
    }

    /**
     * Get breakdown by role
     */
    private function getRoleBreakdown() {
        $stmt = $this->db->query("
            SELECT
                u.role,
                COUNT(DISTINCT ua.user_id) as active_users,
                COUNT(ua.id) as total_activities,
                AVG(us.total_activities) as avg_activities,
                AVG(us.total_logins) as avg_logins
            FROM users u
            LEFT JOIN user_activities ua ON u.id = ua.user_id
            LEFT JOIN user_statistics us ON u.id = us.user_id
            WHERE u.role IN ('librarian', 'faculty', 'student')
            GROUP BY u.role
            ORDER BY total_activities DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get most common activities
     */
    private function getCommonActivities() {
        $stmt = $this->db->query("
            SELECT
                ua.activity_type,
                COUNT(*) as count,
                COUNT(DISTINCT ua.user_id) as unique_users
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            WHERE u.role IN ('librarian', 'faculty', 'student')
            GROUP BY ua.activity_type
            ORDER BY count DESC
            LIMIT 10
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get time-based analysis
     */
    private function getTimeAnalysis() {
        $stmt = $this->db->query("
            SELECT
                DATE(ua.created_at) as date,
                COUNT(*) as activities,
                COUNT(DISTINCT ua.user_id) as active_users
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            WHERE u.role IN ('librarian', 'faculty', 'student')
            AND ua.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(ua.created_at)
            ORDER BY date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Export individual user data to CSV
     */
    public function exportIndividualCSV() {
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            die('User ID required');
        }

        // Get user info
        $stmt = $this->db->prepare("
            SELECT name, email, role FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            die('User not found');
        }

        // Get activities
        $activities = $this->getIndividualUserActivities($userId, 1000);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_' . $userId . '_activity_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, ['User Name', 'Email', 'Role', 'Activity Type', 'Description', 'Thesis', 'Date/Time']);

        // CSV Data
        foreach ($activities as $activity) {
            fputcsv($output, [
                $user['name'],
                $user['email'],
                $user['role'],
                $activity['activity_type'],
                $activity['activity_description'],
                $activity['thesis_title'] ?? 'N/A',
                $activity['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export combined data to CSV (for SOP analysis)
     */
    public function exportCombinedCSV() {
        // Get all activities for tracked users
        $stmt = $this->db->query("
            SELECT
                u.name,
                u.email,
                u.role,
                ua.activity_type,
                ua.activity_description,
                t.title as thesis_title,
                ua.created_at
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            LEFT JOIN theses t ON ua.thesis_id = t.id
            WHERE u.role IN ('librarian', 'faculty', 'student')
            ORDER BY ua.created_at DESC
        ");
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="all_users_activity_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, ['User Name', 'Email', 'Role', 'Activity Type', 'Description', 'Thesis', 'Date/Time']);

        // CSV Data
        foreach ($activities as $activity) {
            fputcsv($output, [
                $activity['name'],
                $activity['email'],
                $activity['role'],
                $activity['activity_type'],
                $activity['activity_description'],
                $activity['thesis_title'] ?? 'N/A',
                $activity['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
