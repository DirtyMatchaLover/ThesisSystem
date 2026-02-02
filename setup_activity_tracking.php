<?php
/**
 * Setup Activity Tracking System
 * Run this file once to add activity tracking tables to your database
 */

require_once __DIR__ . '/models/Database.php';

try {
    $db = Database::getInstance();

    echo "<h2>Setting up Activity Tracking System...</h2>\n";

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/sql/add_activity_tracking.sql');

    // Split by semicolon to execute each statement separately
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        try {
            $db->exec($statement);
            $successCount++;
            echo ".";
        } catch (PDOException $e) {
            $errorCount++;
            echo "X";
            error_log("SQL Error: " . $e->getMessage() . "\nStatement: " . $statement);
        }
    }

    echo "\n\n<h3>Setup Complete!</h3>\n";
    echo "<p>✅ Successfully executed: $successCount statements</p>\n";
    echo "<p>❌ Errors: $errorCount statements</p>\n";

    // Test the tables
    echo "\n<h3>Testing Tables...</h3>\n";

    $tables = ['user_activities', 'user_sessions', 'user_statistics'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>✅ Table '$table' created successfully (0 rows)</p>\n";
        } catch (PDOException $e) {
            echo "<p>❌ Table '$table' error: " . $e->getMessage() . "</p>\n";
        }
    }

    // Test views
    echo "\n<h3>Testing Views...</h3>\n";

    $views = ['user_activity_summary', 'daily_activity_report', 'individual_user_report'];
    foreach ($views as $view) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $view");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>✅ View '$view' created successfully ({$result['count']} rows)</p>\n";
        } catch (PDOException $e) {
            echo "<p>❌ View '$view' error: " . $e->getMessage() . "</p>\n";
        }
    }

    echo "\n<h3>🎉 Activity Tracking System is Ready!</h3>\n";
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Activities will be automatically tracked when users interact with the system</li>\n";
    echo "<li>View all users data at: <a href='index.php?route=admin/users' style='color:#7b3f00;font-weight:600;'>Manage Users Page</a></li>\n";
    echo "<li>View system reports at: <a href='index.php?route=admin/reports' style='color:#7b3f00;font-weight:600;'>Reports & Analytics</a></li>\n";
    echo "<li>Export data for SOP analysis using the export feature</li>\n";
    echo "</ul>\n";

    echo "\n<p><a href='index.php'>← Back to Home</a></p>\n";

} catch (Exception $e) {
    echo "<h3>❌ Setup Failed</h3>\n";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p>Please check your database connection and try again.</p>\n";
}
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
    p {
        line-height: 1.6;
    }
    ul {
        background: white;
        padding: 20px 40px;
        border-left: 4px solid #7b3f00;
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
