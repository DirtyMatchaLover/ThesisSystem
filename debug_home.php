<?php
// Create this file in your root directory to debug the home page issues
// Run: http://localhost/thesis-management-system/debug_home.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/Thesis.php';

echo "<h2>🔍 Home Page Debug</h2>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = Database::getInstance();
    echo "<span class='success'>✅ Database connection successful</span><br>";
    
    // Test basic query
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    if ($result['test'] == 1) {
        echo "<span class='success'>✅ Database queries working</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Database connection failed: " . $e->getMessage() . "</span><br>";
}

// Test 2: Check if theses table exists
echo "<h3>2. Theses Table Check</h3>";
try {
    $stmt = $db->query("DESCRIBE theses");
    $columns = $stmt->fetchAll();
    echo "<span class='success'>✅ Theses table exists with " . count($columns) . " columns:</span><br>";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Theses table error: " . $e->getMessage() . "</span><br>";
}

// Test 3: Count theses
echo "<h3>3. Thesis Data Check</h3>";
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM theses");
    $total = $stmt->fetch()['total'];
    echo "<span class='info'>📊 Total theses in database: $total</span><br>";
    
    $stmt = $db->query("SELECT COUNT(*) as approved FROM theses WHERE status = 'approved'");
    $approved = $stmt->fetch()['approved'];
    echo "<span class='info'>✅ Approved theses: $approved</span><br>";
    
    // Show some sample data
    $stmt = $db->query("SELECT id, title, status, created_at FROM theses LIMIT 5");
    $samples = $stmt->fetchAll();
    if ($samples) {
        echo "<br><strong>Sample theses:</strong><br>";
        foreach ($samples as $sample) {
            echo "ID: {$sample['id']} | {$sample['title']} | {$sample['status']} | {$sample['created_at']}<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Data query error: " . $e->getMessage() . "</span><br>";
}

// Test 4: Test Thesis Model
echo "<h3>4. Thesis Model Test</h3>";
try {
    $theses = new Thesis();
    $approved = $theses->approvedPublic();
    echo "<span class='success'>✅ Thesis model working</span><br>";
    echo "<span class='info'>📄 Model returned " . count($approved) . " approved theses</span><br>";
    
    if (!empty($approved)) {
        echo "<br><strong>First approved thesis:</strong><br>";
        $first = $approved[0];
        echo "Title: " . ($first['title'] ?? 'N/A') . "<br>";
        echo "Author: " . ($first['author'] ?? $first['author_name'] ?? 'N/A') . "<br>";
        echo "Status: " . ($first['status'] ?? 'N/A') . "<br>";
    }
    
} catch (Exception $e) {
    echo "<span class='error'>❌ Thesis model error: " . $e->getMessage() . "</span><br>";
}

// Test 5: Test HomeController
echo "<h3>5. HomeController Test</h3>";
try {
    require_once __DIR__ . '/controllers/HomeController.php';
    $homeController = new HomeController();
    echo "<span class='success'>✅ HomeController created successfully</span><br>";
    
    // Test if we can call methods without errors
    $reflection = new ReflectionClass($homeController);
    $method = $reflection->getMethod('getCurrentAcademicYear');
    $method->setAccessible(true);
    $academicYear = $method->invoke($homeController);
    echo "<span class='info'>📅 Current academic year: $academicYear</span><br>";
    
} catch (Exception $e) {
    echo "<span class='error'>❌ HomeController error: " . $e->getMessage() . "</span><br>";
}

// Test 6: Routing Check
echo "<h3>6. Routing Test</h3>";
echo "<span class='info'>🔗 Current route: " . ($_GET['route'] ?? 'home') . "</span><br>";
echo "<span class='info'>📂 BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'NOT DEFINED') . "</span><br>";
echo "<span class='info'>🌐 Current URL: " . $_SERVER['REQUEST_URI'] . "</span><br>";

echo "<h3>7. Recommendations</h3>";
if ($approved == 0) {
    echo "<span class='info'>💡 <strong>No approved theses found.</strong> To test the home page:<br>";
    echo "1. Go to your admin dashboard<br>";
    echo "2. Submit a test thesis<br>";
    echo "3. Approve it<br>";
    echo "4. Then check the home page again</span><br>";
}

echo "<br><hr>";
echo "<p><strong>🏠 <a href='index.php'>Go to Home Page</a></strong> | ";
echo "<strong>📊 <a href='index.php?route=admin/dashboard'>Admin Dashboard</a></strong></p>";
?>