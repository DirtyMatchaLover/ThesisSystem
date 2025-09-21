<?php
// debug-functions.php - Find duplicate function declarations
echo "<h1>🔍 Function Debug</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .good{color:green;} .bad{color:red;}</style>";

echo "<h2>Checking for function conflicts...</h2>";

// Check if functions exist before including helpers
$functions_before = [
    'asset' => function_exists('asset'),
    'route' => function_exists('route'),
    'current_user' => function_exists('current_user')
];

echo "<h3>Before including helpers.php:</h3>";
foreach ($functions_before as $func => $exists) {
    $class = $exists ? 'bad' : 'good';
    $status = $exists ? '❌ ALREADY EXISTS' : '✅ NOT DEFINED';
    echo "<p class='$class'><strong>$func():</strong> $status</p>";
}

// Try to include helpers
echo "<h3>Including helpers.php...</h3>";
try {
    require_once __DIR__ . '/helpers.php';
    echo "<p class='good'>✅ helpers.php loaded successfully</p>";
} catch (Error $e) {
    echo "<p class='bad'>❌ Error loading helpers.php: " . $e->getMessage() . "</p>";
}

// Check functions after
$functions_after = [
    'asset' => function_exists('asset'),
    'route' => function_exists('route'),
    'current_user' => function_exists('current_user')
];

echo "<h3>After including helpers.php:</h3>";
foreach ($functions_after as $func => $exists) {
    $class = $exists ? 'good' : 'bad';
    $status = $exists ? '✅ DEFINED' : '❌ MISSING';
    echo "<p class='$class'><strong>$func():</strong> $status</p>";
}

// Test the functions
echo "<h3>Testing Functions:</h3>";
if (function_exists('asset')) {
    echo "<p class='good'>asset('test.css') = " . asset('test.css') . "</p>";
}
if (function_exists('route')) {
    echo "<p class='good'>route('home') = " . route('home') . "</p>";
}

echo "<p><a href='index.php'>🏠 Go to Home Page</a></p>";
?>