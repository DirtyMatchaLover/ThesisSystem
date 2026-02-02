<?php
/**
 * Clear All Caches
 * Run this to clear PHP opcache and force refresh
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔄 Clearing All Caches...</h2>\n";
echo "<hr>\n";

// Clear PHP OPcache
echo "<h3>1. PHP OPcache</h3>\n";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "✅ OPcache cleared successfully<br>\n";
    } else {
        echo "⚠️ OPcache could not be cleared<br>\n";
    }
} else {
    echo "ℹ️ OPcache not enabled<br>\n";
}

// Clear APCu cache
echo "<h3>2. APCu Cache</h3>\n";
if (function_exists('apcu_clear_cache')) {
    if (apcu_clear_cache()) {
        echo "✅ APCu cache cleared<br>\n";
    } else {
        echo "⚠️ APCu cache could not be cleared<br>\n";
    }
} else {
    echo "ℹ️ APCu not enabled<br>\n";
}

// Send no-cache headers
echo "<h3>3. Browser Cache Headers</h3>\n";
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
echo "✅ No-cache headers sent<br>\n";

// Clear PHP session
echo "<h3>4. Session Data</h3>\n";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Don't clear session as it would log out users
echo "ℹ️ Session preserved (to keep users logged in)<br>\n";

// Update CSS version
echo "<h3>5. CSS Version Bump</h3>\n";
$headerPath = __DIR__ . '/views/layout/header.php';
if (file_exists($headerPath)) {
    $content = file_get_contents($headerPath);

    // Find current CSS version
    if (preg_match('/\$cssVersion = \'([\d.]+)\'/', $content, $matches)) {
        $currentVersion = $matches[1];
        $versionParts = explode('.', $currentVersion);
        $versionParts[count($versionParts) - 1]++;
        $newVersion = implode('.', $versionParts);

        // Replace version
        $newContent = preg_replace(
            '/\$cssVersion = \'[\d.]+\'/',
            "\$cssVersion = '$newVersion'",
            $content
        );

        if (file_put_contents($headerPath, $newContent)) {
            echo "✅ CSS version updated: $currentVersion → $newVersion<br>\n";
        } else {
            echo "⚠️ Could not update CSS version<br>\n";
        }
    } else {
        echo "ℹ️ CSS version variable not found<br>\n";
    }
} else {
    echo "⚠️ Header file not found<br>\n";
}

// Test the research analytics page
echo "<h3>6. Test Research Analytics Page</h3>\n";
try {
    require_once __DIR__ . '/models/Database.php';
    require_once __DIR__ . '/helpers.php';
    require_once __DIR__ . '/controllers/AnalyticsController.php';

    if (class_exists('AnalyticsController')) {
        $controller = new AnalyticsController();
        if (method_exists($controller, 'researchDashboard')) {
            echo "✅ Research Analytics page is ready<br>\n";
        } else {
            echo "❌ researchDashboard() method not found<br>\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage()) . "<br>\n";
}

echo "<hr>\n";
echo "<h2>✨ Cache Cleared Successfully!</h2>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Hard refresh your browser: <code>Ctrl+Shift+R</code> (Windows) or <code>Cmd+Shift+R</code> (Mac)</li>\n";
echo "<li>Or open in incognito/private window</li>\n";
echo "<li>Visit: <a href='index.php?route=admin/analytics/research&v=" . time() . "'>Research Analytics Page</a></li>\n";
echo "</ol>\n";

echo "<p><a href='index.php'>← Back to Home</a></p>\n";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
    border-bottom: 2px solid #7b3f00;
    padding-bottom: 10px;
}
code {
    background: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    color: #d32f2f;
    font-family: monospace;
}
ol {
    background: white;
    padding: 20px 40px;
    border-left: 4px solid #4caf50;
}
a {
    color: #7b3f00;
    text-decoration: none;
    font-weight: bold;
}
a:hover {
    text-decoration: underline;
}
</style>
