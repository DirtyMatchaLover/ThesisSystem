<?php
require_once __DIR__ . '/../models/Thesis.php';

class HomeController {
    private $theses;

    public function __construct() {
        $this->theses = new Thesis();
    }

    public function index() {
        // Initialize variables with defaults
        $approvedTheses = [];
        $latestTheses = [];
        $stats = [
            'total_approved' => 0,
            'total_authors' => 0,
            'current_year' => 0,
            'academic_year' => $this->getCurrentAcademicYear()
        ];

        try {
            // Get approved theses for the homepage
            $approvedTheses = $this->theses->approvedPublic();
            
            // Limit to latest 12 for the homepage grid
            $latestTheses = array_slice($approvedTheses, 0, 12);
            
            // Get some statistics for display
            $stats = $this->getHomepageStats();
            
        } catch (Exception $e) {
            error_log("Home page data loading error: " . $e->getMessage());
            // Use default values already set above
        }
        
        // Pass data to the view
        require __DIR__ . '/../views/home.php';
    }

    private function getHomepageStats() {
        try {
            $db = Database::getInstance();
            
            // Get total approved theses
            $stmt = $db->query("SELECT COUNT(*) as total_approved FROM theses WHERE status = 'approved'");
            $result = $stmt->fetch();
            $totalApproved = $result ? (int)$result['total_approved'] : 0;
            
            // Get total unique authors (handle null author_id)
            $stmt = $db->query("SELECT COUNT(DISTINCT author_id) as total_authors FROM theses WHERE status = 'approved' AND author_id IS NOT NULL");
            $result = $stmt->fetch();
            $totalAuthors = $result ? (int)$result['total_authors'] : 0;
            
            // Get current academic year theses (simplified query)
            $currentYear = $this->getCurrentAcademicYear();
            $stmt = $db->query("SELECT COUNT(*) as current_year FROM theses WHERE status = 'approved' AND YEAR(created_at) = YEAR(CURDATE())");
            $result = $stmt->fetch();
            $currentYearCount = $result ? (int)$result['current_year'] : 0;
            
            return [
                'total_approved' => $totalApproved,
                'total_authors' => $totalAuthors,
                'current_year' => $currentYearCount,
                'academic_year' => $currentYear
            ];
            
        } catch (Exception $e) {
            error_log("Homepage stats error: " . $e->getMessage());
            return [
                'total_approved' => 0,
                'total_authors' => 0,
                'current_year' => 0,
                'academic_year' => $this->getCurrentAcademicYear()
            ];
        }
    }

    private function getCurrentAcademicYear() {
        $month = (int)date('n');
        $year = (int)date('Y');
        
        if ($month >= 6) { // June onwards is new academic year
            return $year . '-' . ($year + 1);
        } else {
            return ($year - 1) . '-' . $year;
        }
    }

    public function search() {
        $query = trim($_GET['q'] ?? '');
        
        if (empty($query)) {
            echo json_encode(['results' => []]);
            return;
        }
        
        $results = $this->theses->search($query, 10);
        
        // Format results for JSON response
        $formattedResults = array_map(function($thesis) {
            return [
                'id' => $thesis['id'],
                'title' => $thesis['title'],
                'author' => $thesis['author'] ?? $thesis['author_name'] ?? 'Unknown',
                'year' => date('Y', strtotime($thesis['created_at'] ?? 'now')),
                'url' => route('thesis/show') . '&id=' . $thesis['id']
            ];
        }, $results);
        
        header('Content-Type: application/json');
        echo json_encode(['results' => $formattedResults]);
    }
}