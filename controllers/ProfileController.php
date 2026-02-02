<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Thesis.php';

class ProfileController
{
    public function index()
    {
        $user = current_user();
        if (!$user) {
            redirect('auth/select');
        }

        // Get fresh user data from database
        $userDetails = User::findById($user['id']);
        if (!$userDetails) {
            set_flash('error', 'User not found.');
            redirect('auth/select');
        }

        require __DIR__ . '/../views/profile/settings.php';
    }

    /**
     * Student Statistics Dashboard - Shows thesis metrics and analytics
     */
    public function statistics()
    {
        $user = current_user();
        if (!$user) {
            redirect('auth/select');
        }

        // Get all theses by this user
        $thesisModel = new Thesis();
        $db = Database::getInstance();

        $stmt = $db->prepare("
            SELECT t.*,
                   COALESCE(t.view_count, 0) as view_count,
                   COALESCE(t.download_count, 0) as download_count
            FROM theses t
            WHERE t.user_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$user['id']]);
        $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate total statistics
        $totalViews = 0;
        $totalDownloads = 0;
        $totalTheses = count($theses);
        $approvedCount = 0;
        $pendingCount = 0;
        $rejectedCount = 0;

        foreach ($theses as $thesis) {
            $totalViews += (int)($thesis['view_count'] ?? 0);
            $totalDownloads += (int)($thesis['download_count'] ?? 0);

            switch ($thesis['status']) {
                case 'approved':
                    $approvedCount++;
                    break;
                case 'submitted':
                case 'under_review':
                    $pendingCount++;
                    break;
                case 'rejected':
                    $rejectedCount++;
                    break;
            }
        }

        // Get comments for user's theses
        $comments = [];
        if ($totalTheses > 0) {
            $thesisIds = array_column($theses, 'id');
            $placeholders = implode(',', array_fill(0, count($thesisIds), '?'));

            $stmt = $db->prepare("
                SELECT c.*, t.title as thesis_title, u.name as commenter_name
                FROM thesis_comments c
                LEFT JOIN theses t ON c.thesis_id = t.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.thesis_id IN ($placeholders)
                ORDER BY c.created_at DESC
                LIMIT 10
            ");
            $stmt->execute($thesisIds);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $data = [
            'theses' => $theses,
            'stats' => [
                'total_theses' => $totalTheses,
                'total_views' => $totalViews,
                'total_downloads' => $totalDownloads,
                'approved' => $approvedCount,
                'pending' => $pendingCount,
                'rejected' => $rejectedCount
            ],
            'comments' => $comments
        ];

        require __DIR__ . '/../views/profile/statistics.php';
    }

    public function update()
    {
        if (!is_post()) {
            redirect('profile');
        }

        $user = current_user();
        if (!$user) {
            redirect('auth/select');
        }

        // Handle different update types
        $updateType = post('update_type');

        try {
            switch ($updateType) {
                case 'profile':
                    $this->updateProfile($user['id']);
                    break;
                
                case 'password':
                    $this->updatePassword($user['id']);
                    break;
                
                default:
                    set_flash('error', 'Invalid update type.');
                    redirect('profile');
            }
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            set_flash('error', 'An error occurred while updating your profile.');
            redirect('profile');
        }
    }

    private function updateProfile($userId)
    {
        $data = [
            'name' => trim(post('name', '')),
            'email' => trim(post('email', '')),
            'phone' => trim(post('phone', '')),
            'department' => trim(post('department', '')),
            'strand' => trim(post('strand', '')),
            'year_level' => trim(post('year_level', ''))
        ];

        // Basic validation
        if (empty($data['name'])) {
            set_flash('error', 'Name is required.');
            redirect('profile');
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Valid email is required.');
            redirect('profile');
        }

        // Check if email is already taken by another user
        $existingUser = User::findByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $userId) {
            set_flash('error', 'Email is already taken by another user.');
            redirect('profile');
        }

        // Update profile
        $success = User::updateProfile($userId, $data);
        
        if ($success) {
            // Update session with new data
            $updatedUser = User::findById($userId);
            $_SESSION['user'] = $updatedUser;
            
            set_flash('success', 'Profile updated successfully.');
        } else {
            set_flash('error', 'Failed to update profile.');
        }

        redirect('profile');
    }

    private function updatePassword($userId)
    {
        $currentPassword = post('current_password', '');
        $newPassword = post('new_password', '');
        $confirmPassword = post('confirm_password', '');

        // Validation
        if (empty($currentPassword)) {
            set_flash('error', 'Current password is required.');
            redirect('profile');
        }

        if (empty($newPassword)) {
            set_flash('error', 'New password is required.');
            redirect('profile');
        }

        if (strlen($newPassword) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
            redirect('profile');
        }

        if ($newPassword !== $confirmPassword) {
            set_flash('error', 'Password confirmation does not match.');
            redirect('profile');
        }

        // Verify current password
        $user = User::findById($userId);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            set_flash('error', 'Current password is incorrect.');
            redirect('profile');
        }

        // Update password
        $success = User::updatePassword($userId, $newPassword);
        
        if ($success) {
            set_flash('success', 'Password updated successfully.');
        } else {
            set_flash('error', 'Failed to update password.');
        }

        redirect('profile');
    }
}