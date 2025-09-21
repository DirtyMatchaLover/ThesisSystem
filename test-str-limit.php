<?php
// test-str-limit.php
require_once __DIR__ . '/helpers.php';

echo "<h1>🧪 Testing str_limit() Function</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .good{color:green;} .bad{color:red;}</style>";

if (function_exists('str_limit')) {
    echo "<p class='good'>✅ str_limit() function exists!</p>";
    
    $testString = "This is a very long string that should be truncated to test the str_limit function properly.";
    $result = str_limit($testString, 50);
    
    echo "<p><strong>Original:</strong> $testString</p>";
    echo "<p><strong>Limited to 50 chars:</strong> $result</p>";
    echo "<p><strong>Length:</strong> " . strlen($result) . " characters</p>";
} else {
    echo "<p class='bad'>❌ str_limit() function not found!</p>";
    echo "<p>Available functions in helpers.php:</p>";
    $functions = get_defined_functions()['user'];
    foreach ($functions as $func) {
        if (strpos($func, 'str_') === 0 || strpos($func, 'format_') === 0) {
            echo "<p>• $func</p>";
        }
    }
}

echo "<p><a href='index.php'>🏠 Go to Home Page</a></p>";
?>