<?php
// css-debug.php - Diagnose CSS loading issues
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSS Debug - PCC Thesis System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test { background: red; color: white; padding: 10px; margin: 10px 0; }
        .good { color: green; font-weight: bold; }
        .bad { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🎨 CSS Loading Test</h1>
    
    <div class="test">
        If you can see this red box with white text, inline CSS works!
    </div>
    
    <h2>1. CSS File Locations</h2>
    <?php
    $cssFiles = [
        'assets/css/style.css',
        'assets/css/bootstrap.min.css', 
        'css/style.css',
        'css/bootstrap.min.css',
        'public/css/style.css'
    ];
    
    foreach ($cssFiles as $file) {
        $exists = file_exists($file);
        $size = $exists ? filesize($file) : 0;
        $class = $exists ? 'good' : 'bad';
        $status = $exists ? "✅ EXISTS ($size bytes)" : '❌ MISSING';
        echo "<p class='$class'><strong>$file:</strong> $status</p>";
        
        if ($exists) {
            echo "<p style='margin-left: 20px;'>";
            echo "<a href='$file' target='_blank'>📄 View CSS File</a> | ";
            echo "<a href='$file' download>💾 Download</a>";
            echo "</p>";
        }
    }
    ?>
    
    <h2>2. Test CSS Links</h2>
    <p>Try clicking these links to see if CSS files load:</p>
    <ul>
        <li><a href="assets/css/style.css" target="_blank">assets/css/style.css</a></li>
        <li><a href="css/style.css" target="_blank">css/style.css</a></li>
    </ul>
    
    <h2>3. Directory Structure</h2>
    <?php
    function listDirectory($dir, $level = 0) {
        if (!is_dir($dir)) return;
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $fullPath = $dir . '/' . $file;
            
            if (is_dir($fullPath)) {
                echo "<p>{$indent}📁 <strong>$file/</strong></p>";
                if ($level < 2) { // Limit recursion
                    listDirectory($fullPath, $level + 1);
                }
            } else {
                $size = filesize($fullPath);
                echo "<p>{$indent}📄 $file <em>(" . number_format($size) . " bytes)</em></p>";
            }
        }
    }
    
    echo "<h3>Assets folder:</h3>";
    if (is_dir('assets')) {
        listDirectory('assets');
    } else {
        echo "<p class='bad'>❌ assets/ folder not found</p>";
    }
    ?>
    
    <h2>4. Quick Fixes</h2>
    <p><strong>If CSS files exist but don't load in your main site:</strong></p>
    <ul>
        <li>Check your HTML head section for correct CSS paths</li>
        <li>Look for any .htaccess files that might block CSS</li>
        <li>Make sure Apache rewrite module is working</li>
        <li>Check browser console (F12) for 404 errors</li>
    </ul>
    
    <p><strong>Common fixes:</strong></p>
    <ul>
        <li>Change CSS paths from relative to absolute: <code>/assets/css/style.css</code></li>
        <li>Check file permissions (should be 644 for files, 755 for folders)</li>
    </ul>
    
    <p><a href="index.php">🏠 Back to Main Site</a></p>
</body>
</html>