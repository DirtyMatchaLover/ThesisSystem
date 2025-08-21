<?php
// test-env.php
echo "<h1>Testing .env Configuration</h1>";

// Check if the .env file exists
if (file_exists('.env')) {
    echo "<p style='color: green;'>✓ .env file found.</p>";

    // Read the file line by line
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Split the line into name and value
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Set the environment variable (for this script's runtime)
        putenv("$name=$value");
        echo "<p><strong>$name</strong>: " . getenv($name) . "</p>";
    }

} else {
    echo "<p style='color: red;'>✗ .env file NOT found.</p>";
}

// Test a specific variable crucial for your app
echo "<h2>Testing Database Connection Variables:</h2>";
$db_host = getenv('DB_HOST');
$db_name = getenv('DB_DATABASE');

echo "DB_HOST: " . ($db_host ? $db_host : "<span style='color: red;'>Not Set</span>") . "<br>";
echo "DB_DATABASE: " . ($db_name ? $db_name : "<span style='color: red;'>Not Set</span>") . "<br>";

?>