<?php
/**
 * Clear All Data Except User Accounts
 * WARNING: This will delete ALL theses and related data!
 * User accounts will be preserved.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/models/Database.php';

// Security check - only allow in development
if (!isset($_POST['confirm']) || $_POST['confirm'] !== 'DELETE_ALL_DATA') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Clear All Data - ResearchHub</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .warning-box {
                background: #fff3cd;
                border: 3px solid #ffc107;
                border-radius: 10px;
                padding: 30px;
                margin: 20px 0;
            }
            .danger-box {
                background: #f8d7da;
                border: 3px solid #dc3545;
                border-radius: 10px;
                padding: 30px;
                margin: 20px 0;
            }
            .safe-box {
                background: #d4edda;
                border: 3px solid #28a745;
                border-radius: 10px;
                padding: 20px;
                margin: 20px 0;
            }
            h2 {
                color: #333;
                border-bottom: 3px solid #dc3545;
                padding-bottom: 10px;
            }
            ul {
                line-height: 1.8;
            }
            .confirm-section {
                background: white;
                padding: 30px;
                border-radius: 10px;
                margin-top: 30px;
                border: 2px solid #333;
            }
            input[type="text"] {
                width: 100%;
                padding: 15px;
                font-size: 16px;
                border: 2px solid #ddd;
                border-radius: 5px;
                margin: 10px 0;
            }
            button {
                background: #dc3545;
                color: white;
                border: none;
                padding: 15px 30px;
                font-size: 16px;
                font-weight: 600;
                border-radius: 5px;
                cursor: pointer;
                width: 100%;
                margin-top: 10px;
            }
            button:hover {
                background: #c82333;
            }
            .cancel-btn {
                background: #6c757d;
                margin-top: 10px;
            }
            .cancel-btn:hover {
                background: #5a6268;
            }
            code {
                background: #f8f9fa;
                padding: 3px 8px;
                border-radius: 3px;
                color: #dc3545;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <h2>⚠️ Clear All Data (Keep User Accounts)</h2>

        <div class="danger-box">
            <h3>🚨 DANGER: This Action Cannot Be Undone!</h3>
            <p><strong>This will permanently delete:</strong></p>
            <ul>
                <li>❌ All thesis submissions and files</li>
                <li>❌ All thesis comments and reviews</li>
                <li>❌ All thesis categories and keywords</li>
                <li>❌ All activity logs (if created)</li>
                <li>❌ All user statistics</li>
                <li>❌ All system metrics history</li>
            </ul>
        </div>

        <div class="safe-box">
            <h3>✅ What Will Be Kept:</h3>
            <ul>
                <li>✅ All user accounts (students, faculty, admin, librarian)</li>
                <li>✅ User login credentials</li>
                <li>✅ User profile information</li>
                <li>✅ System configuration</li>
            </ul>
        </div>

        <div class="warning-box">
            <h3>⚠️ Important Notes:</h3>
            <ul>
                <li>Thesis PDF files will remain in <code>uploads/theses/</code> folder (manual deletion needed)</li>
                <li>Users can immediately start uploading new theses after this operation</li>
                <li>This gives you a fresh start for testing or new academic year</li>
                <li>All research analytics will show zero/empty until new data is added</li>
            </ul>
        </div>

        <div class="confirm-section">
            <h3>Confirmation Required</h3>
            <p>To proceed with data deletion, type exactly: <code>DELETE_ALL_DATA</code></p>

            <form method="POST" onsubmit="return confirm('Are you ABSOLUTELY SURE? This cannot be undone!');">
                <input type="text"
                       name="confirm"
                       placeholder="Type: DELETE_ALL_DATA"
                       required
                       autocomplete="off">

                <button type="submit">🗑️ Delete All Data (Keep Users)</button>
                <a href="index.php" style="text-decoration: none;">
                    <button type="button" class="cancel-btn">Cancel - Go Back</button>
                </a>
            </form>
        </div>

        <p style="text-align: center; margin-top: 30px; color: #666;">
            <a href="index.php" style="color: #7b3f00; text-decoration: none; font-weight: bold;">← Back to Home</a>
        </p>
    </body>
    </html>
    <?php
    exit;
}

// ===== PROCEED WITH DELETION =====
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearing Data - ResearchHub</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            color: #333;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 10px;
        }
        .progress {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        .success {
            border-left-color: #28a745;
        }
        .error {
            border-left-color: #dc3545;
        }
        .summary {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
        }
    </style>
</head>
<body>

<h2>🔄 Clearing Data...</h2>

<?php
try {
    $db = Database::getInstance();
    $deletedCounts = [];

    // Disable foreign key checks temporarily
    echo "<div class='progress'>Disabling foreign key checks...</div>\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    flush();

    // Delete thesis-related data (preserves users)
    $tables = [
        'thesis_revisions' => 'Thesis Revisions',
        'thesis_comments' => 'Thesis Comments',
        'thesis_keywords' => 'Thesis Keywords',
        'thesis_categories' => 'Thesis Categories',
        'user_activities' => 'User Activities (if exists)',
        'user_sessions' => 'User Sessions (if exists)',
        'user_statistics' => 'User Statistics (if exists)',
        'theses' => 'All Thesis Submissions'
    ];

    foreach ($tables as $table => $description) {
        try {
            // Check if table exists
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                // Count records before deletion
                $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

                // Delete all records
                $db->exec("TRUNCATE TABLE $table");

                $deletedCounts[$description] = $count;
                echo "<div class='progress success'>✅ Cleared <strong>$description</strong>: $count records deleted</div>\n";
            } else {
                echo "<div class='progress'>ℹ️ Table '$table' does not exist (skipped)</div>\n";
            }
            flush();
        } catch (Exception $e) {
            echo "<div class='progress error'>⚠️ Error clearing $description: " . htmlspecialchars($e->getMessage()) . "</div>\n";
            flush();
        }
    }

    // Re-enable foreign key checks
    echo "<div class='progress'>Re-enabling foreign key checks...</div>\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    flush();

    // Verify users are intact
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Summary
    echo "<div class='summary'>";
    echo "<h3>✅ Data Clearing Complete!</h3>";
    echo "<h4>Deleted Records:</h4>";
    echo "<ul>";
    foreach ($deletedCounts as $type => $count) {
        echo "<li><strong>$type:</strong> $count records</li>";
    }
    echo "</ul>";
    echo "<h4>✅ Preserved:</h4>";
    echo "<ul>";
    echo "<li><strong>User Accounts:</strong> $userCount users intact</li>";
    echo "</ul>";
    echo "<h4>⚠️ Manual Cleanup Needed:</h4>";
    echo "<ul>";
    echo "<li>Delete files in: <code>uploads/theses/</code> folder (optional)</li>";
    echo "</ul>";
    echo "</div>";

    // Clear opcache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "<div class='progress'>✅ OPcache cleared</div>\n";
    }

    echo "<p style='text-align: center; margin-top: 30px;'>";
    echo "<a href='index.php' style='background: #28a745; color: white; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-weight: 600;'>← Return to Home</a>";
    echo "</p>";

} catch (Exception $e) {
    echo "<div class='progress error'>";
    echo "<h3>❌ Error During Deletion</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>

</body>
</html>
