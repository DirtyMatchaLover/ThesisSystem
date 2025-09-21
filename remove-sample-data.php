<?php
// remove-sample-data.php - Remove sample thesis data
require_once __DIR__ . '/models/Database.php';

echo "<h1>🗑️ Remove Sample Data</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .good{color:green;} .bad{color:red;} .warning{color:orange;}</style>";

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    try {
        $db = Database::getInstance();
        
        echo "<h2>Removing sample data...</h2>";
        
        // Remove sample theses
        $stmt = $db->prepare("DELETE FROM theses WHERE title LIKE ? OR title LIKE ? OR title LIKE ?");
        $result1 = $stmt->execute([
            '%Digital Marketing Strategies%',
            '%Machine Learning%', 
            '%Literature in Developing%'
        ]);
        
        $deletedTheses = $stmt->rowCount();
        echo "<p class='good'>✅ Removed $deletedTheses sample theses</p>";
        
        // Optional: Remove sample students
        $stmt = $db->prepare("DELETE FROM users WHERE employee_id IN ('STU001', 'STU002', 'STU003')");
        $result2 = $stmt->execute();
        $deletedUsers = $stmt->rowCount();
        echo "<p class='good'>✅ Removed $deletedUsers sample student accounts</p>";
        
        // Check remaining data
        $stmt = $db->query("SELECT COUNT(*) as count FROM theses");
        $result = $stmt->fetch();
        $remainingTheses = $result['count'];
        
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
        $result = $stmt->fetch();
        $remainingStudents = $result['count'];
        
        echo "<h3>📊 Current Status:</h3>";
        echo "<p><strong>Remaining theses:</strong> $remainingTheses</p>";
        echo "<p><strong>Remaining students:</strong> $remainingStudents</p>";
        
        if ($remainingTheses == 0) {
            echo "<p class='warning'>⚠️ Your homepage will now show 'No Published Papers Yet'</p>";
        }
        
        echo "<p><a href='index.php'>🏠 Go to Homepage</a></p>";
        
    } catch (Exception $e) {
        echo "<p class='bad'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
} else {
    // Show confirmation form
    echo "<div style='background:#fff3cd; padding:20px; border-radius:5px; margin:20px 0;'>";
    echo "<h2>⚠️ Confirm Sample Data Removal</h2>";
    echo "<p>This will permanently delete:</p>";
    echo "<ul>";
    echo "<li>Sample thesis: 'Digital Marketing Strategies for Small Business Growth...'</li>";
    echo "<li>Sample thesis: 'The Impact of Machine Learning on Modern Education...'</li>";
    echo "<li>Sample thesis: 'The Role of Literature in Developing Critical Thinking...'</li>";
    echo "<li>3 sample student accounts (STU001, STU002, STU003)</li>";
    echo "</ul>";
    echo "<p><strong>Your homepage will be empty after this!</strong></p>";
    echo "</div>";
    
    echo "<form method='POST'>";
    echo "<p>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<button type='submit' style='background:#d32f2f; color:white; padding:15px 30px; border:none; border-radius:5px; font-size:16px; cursor:pointer;'>Yes, Remove Sample Data</button>";
    echo "</p>";
    echo "</form>";
    
    echo "<p><a href='index.php'>🏠 Cancel and Go Back</a></p>";
}
?>