<?php
// Test functions - Save as test_functions.php
require_once __DIR__ . '/helpers.php';

echo "<h2>🧪 Function Tests</h2>";
echo "<style>body{font-family:Arial;margin:20px;} .good{color:green;} .bad{color:red;} .info{color:blue;}</style>";

// Test 1: file_icon function
echo "<h3>1. file_icon() Test</h3>";
$testFiles = [
    'document.pdf',
    'thesis.docx', 
    'readme.txt',
    'file.unknown',
    '',
    null
];

foreach ($testFiles as $file) {
    if (function_exists('file_icon')) {
        $icon = file_icon($file);
        $fileName = $file ?? 'null';
        echo "<div class='info'>file_icon('$fileName') = $icon</div>";
    } else {
        echo "<div class='bad'>❌ file_icon() function not found!</div>";
        break;
    }
}

// Test 2: Other helper functions
echo "<h3>2. Other Helper Functions</h3>";
$functions = [
    'route', 'asset', 'current_user', 'is_logged_in',
    'csrf_token', 'e', 'format_file_size', 'format_date'
];

foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<div class='good'>✅ $func() exists</div>";
    } else {
        echo "<div class='bad'>❌ $func() missing</div>";
    }
}

// Test 3: Constants
echo "<h3>3. Constants Test</h3>";
if (defined('BASE_PATH')) {
    echo "<div class='good'>✅ BASE_PATH = " . BASE_PATH . "</div>";
} else {
    echo "<div class='bad'>❌ BASE_PATH not defined</div>";
}

echo "<br><hr>";
echo "<a href='index.php'>🏠 Go to Home Page</a> | ";
echo "<a href='debug_simple.php'>🔧 Run Diagnostics</a>";
?>