<?php
// Simple research page fallback
if (file_exists(__DIR__ . '/layout/header.php')) {
    include __DIR__ . '/layout/header.php';
}
if (file_exists(__DIR__ . '/layout/navigation.php')) {
    include __DIR__ . '/layout/navigation.php';
}
?>

<div style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <h1 style="color: #d32f2f; text-align: center;">Research Papers</h1>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>⚠️ Notice:</strong> Research system is running in simplified mode.
        <br>The full research browsing features are not available right now.
    </div>

    <?php
    // Try to get approved theses directly from database
    try {
        if (file_exists(__DIR__ . '/../models/Database.php')) {
            require_once __DIR__ . '/../models/Database.php';
            
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM theses WHERE status = 'approved' ORDER BY created_at DESC LIMIT 10");
            $stmt->execute();
            $theses = $stmt->fetchAll();
            
            if (!empty($theses)) {
                echo "<h2>Available Research Papers</h2>";
                echo "<div style='display: grid; gap: 20px;'>";
                
                foreach ($theses as $thesis) {
                    echo "<div style='border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: white;'>";
                    echo "<h3 style='margin-top: 0; color: #d32f2f;'>" . htmlspecialchars($thesis['title']) . "</h3>";
                    
                    if (!empty($thesis['author'])) {
                        echo "<p><strong>Author:</strong> " . htmlspecialchars($thesis['author']) . "</p>";
                    }
                    
                    if (!empty($thesis['abstract'])) {
                        echo "<p>" . htmlspecialchars(substr($thesis['abstract'], 0, 200)) . "...</p>";
                    }
                    
                    echo "<p><small>Published: " . date('M j, Y', strtotime($thesis['created_at'])) . "</small></p>";
                    
                    if (!empty($thesis['file_path']) && file_exists(__DIR__ . '/../' . $thesis['file_path'])) {
                        echo "<a href='" . htmlspecialchars($thesis['file_path']) . "' target='_blank' style='background: #d32f2f; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;'>📄 View PDF</a>";
                    }
                    
                    echo "</div>";
                }
                
                echo "</div>";
            } else {
                echo "<div style='text-align: center; padding: 40px;'>";
                echo "<h2>📚 No Research Papers Yet</h2>";
                echo "<p>No approved research papers are available at this time.</p>";
                echo "<p>Be the first to contribute to the research repository!</p>";
                
                if (is_logged_in()) {
                    echo "<a href='?route=thesis/create' style='background: #d32f2f; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px;'>Upload Your Research</a>";
                } else {
                    echo "<a href='?route=auth/select' style='background: #d32f2f; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px;'>Get Started</a>";
                }
                echo "</div>";
            }
            
        } else {
            throw new Exception("Database class not found");
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
        echo "<strong>Error:</strong> Cannot load research papers at this time.<br>";
        echo "Reason: " . htmlspecialchars($e->getMessage()) . "<br>";
        echo "<br><a href='debug_simple.php'>🔧 Run System Diagnostic</a>";
        echo "</div>";
    }
    ?>

    <div style="margin-top: 40px; text-align: center;">
        <a href="?route=home" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">← Back to Home</a>
    </div>
</div>

<?php
if (file_exists(__DIR__ . '/layout/footer.php')) {
    include __DIR__ . '/layout/footer.php';
}
?>