<?php
// Quick Database Fix Script
// Run this once to add missing columns and make approved theses visible
// Access: http://localhost/thesis-management-system/fix_database_columns.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/models/Database.php';

echo "<h1>🔧 Database Column Fix Script</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
    .error { color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block; }
    .btn-success { background: #28a745; }
    .btn-warning { background: #ffc107; color: #212529; }
</style>";

try {
    $db = Database::getInstance();
    echo "<div class='success'>✅ Database connection successful</div>";

    // Check current table structure
    echo "<h2>Current Table Structure</h2>";
    $stmt = $db->query("DESCRIBE theses");
    $columns = $stmt->fetchAll();
    
    $columnNames = array_column($columns, 'Field');
    echo "<div class='info'>Current columns: " . implode(', ', $columnNames) . "</div>";

    // Check for required columns
    $requiredColumns = [
        'is_public' => 'TINYINT(1) DEFAULT 1',
        'publication_date' => 'TIMESTAMP NULL',
        'approval_date' => 'TIMESTAMP NULL',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];

    $missingColumns = [];
    $addedColumns = [];

    echo "<h2>Adding Missing Columns</h2>";

    foreach ($requiredColumns as $columnName => $columnDefinition) {
        if (!in_array($columnName, $columnNames)) {
            $missingColumns[] = $columnName;
            
            try {
                $sql = "ALTER TABLE theses ADD COLUMN $columnName $columnDefinition";
                $db->exec($sql);
                $addedColumns[] = $columnName;
                echo "<div class='success'>✅ Added column: $columnName</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Failed to add column $columnName: " . $e->getMessage() . "</div>";
            }
        } else {
            echo "<div class='info'>✓ Column already exists: $columnName</div>";
        }
    }

    // Update existing approved theses
    echo "<h2>Updating Existing Data</h2>";

    // Count approved theses before update
    $stmt = $db->query("SELECT COUNT(*) as count FROM theses WHERE status = 'approved'");
    $approvedCount = $stmt->fetch()['count'];
    echo "<div class='info'>📊 Found $approvedCount approved theses</div>";

    if ($approvedCount > 0) {
        // Make approved theses public and set publication dates
        $stmt = $db->prepare("
            UPDATE theses 
            SET is_public = 1, 
                publication_date = COALESCE(publication_date, created_at),
                approval_date = COALESCE(approval_date, created_at)
            WHERE status = 'approved'
        ");
        
        if ($stmt->execute()) {
            echo "<div class='success'>✅ Updated $approvedCount approved theses to be public</div>";
        } else {
            echo "<div class='error'>❌ Failed to update approved theses</div>";
        }
    }

    // Test the fix
    echo "<h2>Testing the Fix</h2>";
    
    // Test public approved theses query
    $stmt = $db->query("SELECT COUNT(*) as count FROM theses WHERE status = 'approved' AND is_public = 1");
    $publicCount = $stmt->fetch()['count'];
    echo "<div class='info'>📊 Public approved theses: $publicCount</div>";

    // Show sample data
    if ($publicCount > 0) {
        echo "<h3>Sample Public Approved Theses</h3>";
        $stmt = $db->query("
            SELECT t.id, t.title, t.status, t.is_public, t.publication_date, u.name as author 
            FROM theses t 
            LEFT JOIN users u ON t.user_id = u.id 
            WHERE t.status = 'approved' AND t.is_public = 1 
            LIMIT 5
        ");
        $samples = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th><th>Public</th><th>Publication Date</th></tr>";
        foreach ($samples as $sample) {
            echo "<tr>";
            echo "<td>{$sample['id']}</td>";
            echo "<td>" . htmlspecialchars(substr($sample['title'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars($sample['author'] ?? 'N/A') . "</td>";
            echo "<td>{$sample['status']}</td>";
            echo "<td>" . ($sample['is_public'] ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>" . ($sample['publication_date'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div class='success'>🎉 Great! Your approved theses should now be visible on the home page and research page!</div>";
    } else {
        echo "<div class='warning'>⚠️ No public approved theses found. You may need to:</div>";
        echo "<ul>";
        echo "<li>Submit some theses first</li>";
        echo "<li>Approve them through the admin dashboard</li>";
        echo "<li>Run this script again after approval</li>";
        echo "</ul>";
    }

    // Verify Thesis model will work
    echo "<h2>Testing Thesis Model</h2>";
    try {
        require_once __DIR__ . '/models/Thesis.php';
        $thesisModel = new Thesis();
        $publicTheses = $thesisModel->approvedPublic();
        echo "<div class='success'>✅ Thesis model working - returned " . count($publicTheses) . " public theses</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Thesis model error: " . $e->getMessage() . "</div>";
    }

    // Show final results
    echo "<h2>Fix Complete!</h2>";
    echo "<div class='success'>";
    echo "<h3>✅ Database Fix Summary:</h3>";
    echo "<ul>";
    if (!empty($addedColumns)) {
        echo "<li>Added columns: " . implode(', ', $addedColumns) . "</li>";
    } else {
        echo "<li>All required columns were already present</li>";
    }
    echo "<li>Updated $approvedCount approved theses to be public</li>";
    echo "<li>Found $publicCount public approved theses ready for display</li>";
    echo "</ul>";
    echo "</div>";

    // Test links
    echo "<h2>Test Your Site</h2>";
    echo "<a href='index.php' class='btn btn-success'>🏠 Test Home Page</a>";
    echo "<a href='index.php?route=research' class='btn btn-success'>📚 Test Research Page</a>";
    echo "<a href='index.php?route=admin/dashboard' class='btn'>⚙️ Admin Dashboard</a>";
    echo "<a href='debug_thesis_visibility.php' class='btn btn-warning'>🔍 Run Full Diagnostic</a>";

    echo "<div class='info'>";
    echo "<strong>Note:</strong> If you still don't see theses on your home page, it might be because:<br>";
    echo "1. You need to refresh your browser cache<br>";
    echo "2. Your theses need to be approved first<br>";
    echo "3. There might be other issues - use the diagnostic tool above<br>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Critical Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>";
    echo "<strong>This error usually means:</strong><br>";
    echo "1. Database connection failed<br>";
    echo "2. Database doesn't exist<br>";
    echo "3. Missing permissions<br>";
    echo "4. Configuration issues<br><br>";
    echo "Please check your database settings and try again.";
    echo "</div>";
}
?>