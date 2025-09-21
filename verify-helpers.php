<?php
// verify-helpers.php - Check what's actually in your helpers.php file
echo "<h1>🔍 Helpers.php Content Check</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .code{background:#f5f5f5;padding:10px;margin:10px 0;} .good{color:green;} .bad{color:red;}</style>";

$helpersFile = __DIR__ . '/helpers.php';

if (file_exists($helpersFile)) {
    echo "<p class='good'>✅ helpers.php found</p>";
    
    // Read the first 50 lines to see the BASE_PATH definition
    $lines = file($helpersFile);
    
    echo "<h2>First 20 lines of helpers.php:</h2>";
    echo "<div class='code'>";
    for ($i = 0; $i < min(20, count($lines)); $i++) {
        $lineNum = $i + 1;
        $line = htmlspecialchars($lines[$i]);
        
        // Highlight BASE_PATH line
        if (stripos($line, 'BASE_PATH') !== false) {
            echo "<strong style='background:yellow;'>$lineNum: $line</strong>";
        } else {
            echo "$lineNum: $line";
        }
    }
    echo "</div>";
    
    // Check what BASE_PATH is currently set to
    if (defined('BASE_PATH')) {
        echo "<p class='bad'>❌ BASE_PATH already defined as: <strong>" . BASE_PATH . "</strong></p>";
    } else {
        include $helpersFile;
        echo "<p class='good'>✅ BASE_PATH now defined as: <strong>" . BASE_PATH . "</strong></p>";
    }
    
} else {
    echo "<p class='bad'>❌ helpers.php not found</p>";
}

echo "<p><a href='debug-functions.php'>🔄 Rerun Function Debug</a></p>";
?>