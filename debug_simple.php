<?php
// Simple debug script that doesn't depend on any other files
// Save as: debug_simple.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Simple Thesis System Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .good { color: green; font-weight: bold; }
    .bad { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #ccc; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
</style>";

echo "<div class='section'>";
echo "<strong>System Information</strong><br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Date/Time: " . date('Y-m-d H:i:s') . "<br>";
echo "</div>";

// Test 1: Check required files
echo "<h2>1. File System Check</h2>";

$requiredFiles = [
    'helpers.php',
    'models/Database.php',
    'models/Thesis.php',
    'models/User.php',
    'controllers/AuthController.php',
    'controllers/ThesisController.php',
    'views/layout/header.php',
    'views/layout/navigation.php',
    'views/auth/role_select.php',
    'assets/css/style.css'
];

echo "<table>";
echo "<tr><th>File</th><th>Status</th><th>Size</th></tr>";

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        echo "<tr><td>$file</td><td class='good'>✅ EXISTS</td><td>" . number_format($size) . " bytes</td></tr>";
    } else {
        echo "<tr><td>$file</td><td class='bad'>❌ MISSING</td><td>-</td></tr>";
    }
}
echo "</table>";

// Test 2: PHP Configuration
echo "<h2>2. PHP Configuration</h2>";

$phpSettings = [
    'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time') . ' seconds'
];

echo "<table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
foreach ($phpSettings as $setting => $value) {
    $status = ($setting === 'file_uploads' && $value === 'Disabled') ? 'bad' : 'good';
    echo "<tr><td>$setting</td><td class='$status'>$value</td></tr>";
}
echo "</table>";

// Test 3: Extensions
echo "<h2>3. PHP Extensions</h2>";

$requiredExtensions = ['pdo', 'pdo_mysql', 'fileinfo', 'gd', 'mbstring'];

echo "<table>";
echo "<tr><th>Extension</th><th>Status</th></tr>";
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? 'good' : 'bad';
    $text = $loaded ? '✅ LOADED' : '❌ MISSING';
    echo "<tr><td>$ext</td><td class='$status'>$text</td></tr>";
}
echo "</table>";

// Test 4: Database Connection (Basic)
echo "<h2>4. Database Connection Test</h2>";

try {
    // Try to load database settings from multiple locations
    $dbSettings = null;
    
    // Method 1: Try config file
    if (file_exists(__DIR__ . '/config/database.php')) {
        $dbSettings = include __DIR__ . '/config/database.php';
        echo "<div class='info'>📁 Found config/database.php</div>";
    }
    
    // Method 2: Try hardcoded settings (for XAMPP)
    if (!$dbSettings) {
        $dbSettings = [
            'host' => 'localhost',
            'dbname' => 'thesis_db',
            'username' => 'root',
            'password' => ''
        ];
        echo "<div class='info'>📁 Using default XAMPP settings</div>";
    }
    
    // Try connection
    $dsn = "mysql:host={$dbSettings['host']};dbname={$dbSettings['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbSettings['username'], $dbSettings['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<div class='good'>✅ Database connection successful</div>";
    
    // Get database info
    $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $info = $stmt->fetch();
    echo "<div class='info'>📊 Database: {$info['db']}</div>";
    echo "<div class='info'>📊 Version: {$info['version']}</div>";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Database Tables:</h3>";
    if (empty($tables)) {
        echo "<div class='warning'>⚠️ No tables found</div>";
        echo "<div class='section'>";
        echo "<strong>Create tables by running:</strong><br>";
        echo "Import sql/init.sql or run the setup script";
        echo "</div>";
    } else {
        echo "<div class='info'>Found " . count($tables) . " tables: " . implode(', ', $tables) . "</div>";
        
        // Check specific tables
        $requiredTables = ['users', 'theses'];
        foreach ($requiredTables as $table) {
            if (in_array($table, $tables)) {
                // Get row count
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch()['count'];
                echo "<div class='good'>✅ Table '$table' exists ($count rows)</div>";
            } else {
                echo "<div class='bad'>❌ Table '$table' missing</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<div class='bad'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    echo "<div class='section'>";
    echo "<strong>Database troubleshooting:</strong><br>";
    echo "1. Make sure XAMPP MySQL is running<br>";
    echo "2. Check if database 'thesis_db' exists<br>";
    echo "3. Verify username/password in Database.php<br>";
    echo "4. Import the sql/init.sql file<br>";
    echo "</div>";
}

// Test 5: Upload Directory
echo "<h2>5. Upload Directory Test</h2>";

$uploadDirs = [
    'uploads/',
    'uploads/theses/',
    'assets/',
    'assets/images/'
];

echo "<table>";
echo "<tr><th>Directory</th><th>Exists</th><th>Writable</th></tr>";

foreach ($uploadDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    $exists = is_dir($fullPath);
    $writable = $exists && is_writable($fullPath);
    
    $existsText = $exists ? '✅ YES' : '❌ NO';
    $writableText = $writable ? '✅ YES' : ($exists ? '❌ NO' : 'N/A');
    
    $existsClass = $exists ? 'good' : 'bad';
    $writableClass = $writable ? 'good' : ($exists ? 'bad' : 'warning');
    
    echo "<tr>";
    echo "<td>$dir</td>";
    echo "<td class='$existsClass'>$existsText</td>";
    echo "<td class='$writableClass'>$writableText</td>";
    echo "</tr>";
    
    // Try to create missing directories
    if (!$exists) {
        if (mkdir($fullPath, 0755, true)) {
            echo "<tr><td colspan='3' class='good'>✅ Created directory: $dir</td></tr>";
        } else {
            echo "<tr><td colspan='3' class='bad'>❌ Failed to create: $dir</td></tr>";
        }
    }
}
echo "</table>";

// Test 6: Session Test
echo "<h2>6. Session Test</h2>";

session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='good'>✅ Sessions working</div>";
    echo "<div class='info'>📋 Session ID: " . session_id() . "</div>";
    
    if (!empty($_SESSION)) {
        echo "<div class='info'>📦 Session data exists</div>";
        if (isset($_SESSION['user'])) {
            echo "<div class='good'>✅ User logged in: " . $_SESSION['user']['name'] . "</div>";
        } else {
            echo "<div class='warning'>⚠️ No user in session</div>";
        }
    } else {
        echo "<div class='info'>📦 Session empty (normal for fresh visit)</div>";
    }
} else {
    echo "<div class='bad'>❌ Sessions not working</div>";
}

// Test 7: Basic includes test
echo "<h2>7. Include Files Test</h2>";

if (file_exists(__DIR__ . '/helpers.php')) {
    try {
        include_once __DIR__ . '/helpers.php';
        echo "<div class='good'>✅ helpers.php loaded successfully</div>";
        
        // Test some helper functions
        if (function_exists('route')) {
            $testRoute = route('home');
            echo "<div class='info'>📍 route('home') = $testRoute</div>";
        }
        
        if (function_exists('asset')) {
            $testAsset = asset('css/style.css');
            echo "<div class='info'>🎨 asset('css/style.css') = $testAsset</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='bad'>❌ Error loading helpers.php: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='bad'>❌ helpers.php not found</div>";
}

// Recommendations
echo "<h2>8. Recommendations</h2>";
echo "<div class='section'>";
echo "<strong>Next steps to fix issues:</strong><br><br>";

echo "1. <strong>Missing Files:</strong> Make sure all files from the artifacts are created<br>";
echo "2. <strong>Database:</strong> Import sql/init.sql to create tables<br>";
echo "3. <strong>Permissions:</strong> Set uploads/ directory to 755 permissions<br>";
echo "4. <strong>Extensions:</strong> Enable missing PHP extensions in php.ini<br>";
echo "5. <strong>Test Login:</strong> Create a test user to try uploads<br>";

echo "</div>";

echo "<hr>";
echo "<strong>Quick Links:</strong><br>";
echo "<a href='index.php'>🏠 Home Page</a> | ";
echo "<a href='?'>🔄 Refresh Debug</a>";

if (file_exists(__DIR__ . '/debug_thesis_upload.php')) {
    echo " | <a href='debug_thesis_upload.php'>🔧 Full Debug</a>";
}
?>