<?php
// complete-test.php - Test all our fixes
require_once __DIR__ . '/helpers.php';

echo "<h1>🧪 Complete System Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .good { color: green; font-weight: bold; }
    .bad { color: red; font-weight: bold; }
    .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
</style>";

echo "<div class='test-section'>";
echo "<h2>1. Helper Functions Test</h2>";

// Test str_limit
if (function_exists('str_limit')) {
    echo "<p class='good'>✅ str_limit() function exists</p>";
    $testStr = "This is a very long title that should be truncated properly for display in the thesis grid.";
    $result = str_limit($testStr, 60);
    echo "<p><strong>Test:</strong> str_limit('$testStr', 60)</p>";
    echo "<p><strong>Result:</strong> $result</p>";
} else {
    echo "<p class='bad'>❌ str_limit() function missing</p>";
}

// Test other functions
$functions = ['asset', 'route', 'format_date', 'file_icon'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<p class='good'>✅ $func() exists</p>";
    } else {
        echo "<p class='bad'>❌ $func() missing</p>";
    }
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>2. URL Generation Test</h2>";
echo "<p><strong>asset('assets/css/style.css'):</strong> " . asset('assets/css/style.css') . "</p>";
echo "<p><strong>route('home'):</strong> " . route('home') . "</p>";
echo "<p><strong>route('research'):</strong> " . route('research') . "</p>";
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>3. Database Connection Test</h2>";
try {
    require_once __DIR__ . '/models/Database.php';
    $db = Database::getInstance();
    
    // Test basic query
    $stmt = $db->query("SELECT COUNT(*) as count FROM theses WHERE status = 'approved'");
    $result = $stmt->fetch();
    
    echo "<p class='good'>✅ Database connection successful</p>";
    echo "<p><strong>Approved theses count:</strong> " . ($result['count'] ?? 0) . "</p>";
    
    // Test if sample thesis exists
    $stmt = $db->query("SELECT id, title FROM theses WHERE status = 'approved' LIMIT 1");
    $thesis = $stmt->fetch();
    
    if ($thesis) {
        echo "<p class='good'>✅ Sample thesis found: " . htmlspecialchars($thesis['title']) . "</p>";
    } else {
        echo "<p class='bad'>⚠️ No approved theses found (this is why homepage is empty)</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='bad'>❌ Database error: " . $e->getMessage() . "</p>";
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>4. File System Test</h2>";
$files = [
    'assets/css/style.css',
    'assets/images/pcc-logo.png',
    'views/home.php',
    'views/layout/navigation.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p class='good'>✅ $file exists</p>";
    } else {
        echo "<p class='bad'>❌ $file missing</p>";
    }
}
echo "</div>";

echo "<div class='test-section'>";
echo "<h2>5. Quick Links</h2>";
echo "<p><a href='index.php'>🏠 Test Home Page</a></p>";
echo "<p><a href='index.php?route=research'>🔍 Test Research Page</a></p>";
echo "<p><a href='assets/images/pcc-logo.png' target='_blank'>🖼️ Test Logo Image</a></p>";
echo "</div>";
?>