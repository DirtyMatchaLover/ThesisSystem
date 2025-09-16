<?php
// Create this file in your root directory to debug why approved theses aren't showing
// Run: http://localhost/thesis-management-system/debug_thesis_visibility.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/helpers.php';

echo "<h1>🔍 Thesis Visibility Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
    .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #ccc; }
    .fix-btn { background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px; }
    .test-btn { background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; margin: 5px; }
</style>";

try {
    // Load required files
    require_once __DIR__ . '/models/Database.php';
    require_once __DIR__ . '/models/Thesis.php';

    $db = Database::getInstance();
    echo "<div class='success'>✅ Database connection successful</div>";

    // Test 1: Check table structure
    echo "<h2>1. Database Table Structure</h2>";
    $stmt = $db->query("DESCRIBE theses");
    $columns = $stmt->fetchAll();
    
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Check if important columns exist
    $columnNames = array_column($columns, 'Field');
    $requiredColumns = ['is_public', 'publication_date', 'status'];
    $missingColumns = [];
    
    foreach ($requiredColumns as $required) {
        if (!in_array($required, $columnNames)) {
            $missingColumns[] = $required;
        }
    }

    if (!empty($missingColumns)) {
        echo "<div class='warning'>⚠️ Missing columns: " . implode(', ', $missingColumns) . "</div>";
        echo "<div class='section'>";
        echo "<strong>Fix Missing Columns:</strong><br>";
        foreach ($missingColumns as $col) {
            if ($col === 'is_public') {
                echo "<code>ALTER TABLE theses ADD COLUMN is_public TINYINT(1) DEFAULT 1;</code><br>";
            } elseif ($col === 'publication_date') {
                echo "<code>ALTER TABLE theses ADD COLUMN publication_date TIMESTAMP NULL;</code><br>";
            }
        }
        echo "</div>";
    }

    // Test 2: Count all theses by status
    echo "<h2>2. Thesis Count by Status</h2>";
    $stmt = $db->query("
        SELECT 
            status, 
            COUNT(*) as count,
            COUNT(CASE WHEN is_public = 1 THEN 1 END) as public_count
        FROM theses 
        GROUP BY status 
        ORDER BY count DESC
    ");
    $statusCounts = $stmt->fetchAll();
    
    if (!empty($statusCounts)) {
        echo "<table>";
        echo "<tr><th>Status</th><th>Total Count</th><th>Public Count</th></tr>";
        foreach ($statusCounts as $status) {
            echo "<tr>";
            echo "<td><strong>{$status['status']}</strong></td>";
            echo "<td>{$status['count']}</td>";
            echo "<td>" . ($status['public_count'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No theses found in database</div>";
    }

    // Test 3: Check approved theses details
    echo "<h2>3. Approved Theses Details</h2>";
    $stmt = $db->query("
        SELECT 
            t.id, 
            t.title, 
            t.status, 
            t.is_public,
            t.publication_date,
            t.created_at,
            u.name as author
        FROM theses t 
        LEFT JOIN users u ON t.user_id = u.id 
        WHERE t.status = 'approved'
        ORDER BY t.created_at DESC
    ");
    $approvedTheses = $stmt->fetchAll();

    if (!empty($approvedTheses)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Status</th><th>Is Public</th><th>Publication Date</th><th>Actions</th></tr>";
        foreach ($approvedTheses as $thesis) {
            $isPublic = $thesis['is_public'] ?? 1;
            $rowClass = $isPublic ? 'success' : 'warning';
            
            echo "<tr class='$rowClass'>";
            echo "<td>{$thesis['id']}</td>";
            echo "<td>" . htmlspecialchars(substr($thesis['title'], 0, 50)) . "...</td>";
            echo "<td>" . htmlspecialchars($thesis['author'] ?? 'N/A') . "</td>";
            echo "<td><strong>{$thesis['status']}</strong></td>";
            echo "<td>" . ($isPublic ? '✅ Public' : '❌ Private') . "</td>";
            echo "<td>" . ($thesis['publication_date'] ?? 'N/A') . "</td>";
            echo "<td>";
            if (!$isPublic) {
                echo "<a href='?action=make_public&id={$thesis['id']}' class='fix-btn'>Make Public</a>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No approved theses found!</div>";
    }

    // Test 4: Test the Thesis model query
    echo "<h2>4. Thesis Model Query Test</h2>";
    try {
        $thesisModel = new Thesis();
        $publicTheses = $thesisModel->approvedPublic();
        
        echo "<div class='info'>📊 Thesis model returned: " . count($publicTheses) . " public approved theses</div>";
        
        if (empty($publicTheses)) {
            echo "<div class='warning'>⚠️ The approvedPublic() method returned no results</div>";
            
            // Let's test what the actual query looks like
            echo "<div class='section'>";
            echo "<strong>Testing different queries:</strong><br><br>";
            
            // Test 1: Just approved
            $stmt = $db->query("SELECT COUNT(*) as count FROM theses WHERE status = 'approved'");
            $justApproved = $stmt->fetch()['count'];
            echo "✓ Theses with status = 'approved': $justApproved<br>";
            
            // Test 2: Approved and public
            if (in_array('is_public', $columnNames)) {
                $stmt = $db->query("SELECT COUNT(*) as count FROM theses WHERE status = 'approved' AND is_public = 1");
                $approvedAndPublic = $stmt->fetch()['count'];
                echo "✓ Theses with status = 'approved' AND is_public = 1: $approvedAndPublic<br>";
            } else {
                echo "⚠️ No 'is_public' column found - this might be the issue!<br>";
            }
            
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Thesis model error: " . $e->getMessage() . "</div>";
    }

    // Test 5: Manual fix actions
    if (isset($_GET['action']) && $_GET['action'] === 'make_public' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        echo "<h2>5. Making Thesis Public</h2>";
        
        if (in_array('is_public', $columnNames)) {
            $stmt = $db->prepare("UPDATE theses SET is_public = 1, publication_date = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo "<div class='success'>✅ Thesis ID $id is now public!</div>";
                echo "<script>setTimeout(function(){ window.location = window.location.pathname; }, 2000);</script>";
            } else {
                echo "<div class='error'>❌ Failed to update thesis</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ Cannot make public - missing is_public column</div>";
        }
    }

    // Add missing columns action
    if (isset($_GET['action']) && $_GET['action'] === 'add_columns') {
        echo "<h2>5. Adding Missing Columns</h2>";
        
        try {
            if (!in_array('is_public', $columnNames)) {
                $db->exec("ALTER TABLE theses ADD COLUMN is_public TINYINT(1) DEFAULT 1");
                echo "<div class='success'>✅ Added is_public column</div>";
            }
            
            if (!in_array('publication_date', $columnNames)) {
                $db->exec("ALTER TABLE theses ADD COLUMN publication_date TIMESTAMP NULL");
                echo "<div class='success'>✅ Added publication_date column</div>";
            }
            
            // Update existing approved theses to be public
            $db->exec("UPDATE theses SET is_public = 1, publication_date = created_at WHERE status = 'approved' AND publication_date IS NULL");
            echo "<div class='success'>✅ Updated existing approved theses to be public</div>";
            
            echo "<script>setTimeout(function(){ window.location = window.location.pathname; }, 3000);</script>";
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Database update error: " . $e->getMessage() . "</div>";
        }
    }

    // Show fix options
    echo "<h2>Quick Fixes</h2>";
    echo "<div class='section'>";
    
    if (!empty($missingColumns)) {
        echo "<a href='?action=add_columns' class='fix-btn'>🔧 Add Missing Database Columns</a><br><br>";
    }
    
    if (!empty($approvedTheses)) {
        $privateCount = count(array_filter($approvedTheses, function($t) { return !($t['is_public'] ?? 1); }));
        if ($privateCount > 0) {
            echo "<strong>$privateCount approved theses are marked as private.</strong><br>";
            echo "Click 'Make Public' buttons above to fix individual theses.<br><br>";
        }
    }
    
    echo "<a href='index.php' class='test-btn'>🏠 Test Home Page</a>";
    echo "<a href='index.php?route=research' class='test-btn'>📚 Test Research Page</a>";
    echo "<a href='index.php?route=admin/dashboard' class='test-btn'>⚙️ Admin Dashboard</a>";
    
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Critical Error: " . $e->getMessage() . "</div>";
    echo "<div class='section'>";
    echo "<strong>Possible solutions:</strong><br>";
    echo "1. Check if Database.php exists and is properly configured<br>";
    echo "2. Verify database connection settings<br>";
    echo "3. Make sure the theses table exists<br>";
    echo "4. Run the SQL initialization script<br>";
    echo "</div>";
}
?>