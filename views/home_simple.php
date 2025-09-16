<?php
// Simple home page fallback when HomeController doesn't work
if (file_exists(__DIR__ . '/layout/header.php')) {
    include __DIR__ . '/layout/header.php';
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PCC Thesis Hub</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
            .header { text-align: center; margin-bottom: 30px; }
            .title { color: #d32f2f; font-size: 2.5rem; margin-bottom: 10px; }
            .subtitle { color: #666; font-size: 1.2rem; }
            .status { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .nav { text-align: center; margin: 20px 0; }
            .nav a { display: inline-block; margin: 10px; padding: 10px 20px; background: #d32f2f; color: white; text-decoration: none; border-radius: 5px; }
            .nav a:hover { background: #b71c1c; }
        </style>
    </head>
    <body>
    <?php
}
?>

<div class="container">
    <div class="header">
        <h1 class="title">PCC Thesis Hub</h1>
        <p class="subtitle">Pasig Catholic College Research Repository</p>
    </div>

    <div class="status">
        <strong>⚠️ System Status:</strong> Running in simplified mode. Some features may be limited.
        <br><br>
        <strong>Issues detected:</strong>
        <ul>
            <li>HomeController not found or not working properly</li>
            <li>Some system components may need setup</li>
        </ul>
        <br>
        <strong>📋 Troubleshooting:</strong> 
        <a href="debug_simple.php" style="color: #d32f2f;">Run System Diagnostic</a>
    </div>

    <div class="nav">
        <h3>Available Actions:</h3>
        
        <?php if (is_logged_in()): ?>
            <p>Welcome back, <?= htmlspecialchars(current_user()['name']) ?>!</p>
            
            <a href="?route=thesis/create">📝 Upload Thesis</a>
            <a href="?route=thesis/list">📄 My Submissions</a>
            
            <?php if (has_role(['admin', 'faculty', 'librarian'])): ?>
                <a href="?route=admin/dashboard">📊 Dashboard</a>
            <?php endif; ?>
            
            <a href="?route=auth/logout">🚪 Logout</a>
            
        <?php else: ?>
            <p>Please log in to access thesis management features.</p>
            
            <a href="?route=auth/select">🔐 Login</a>
            <a href="?route=research">🔍 Browse Research</a>
        <?php endif; ?>
        
        <br><br>
        <a href="debug_simple.php">🔧 System Diagnostic</a>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666; font-size: 14px;">
        <p>If you're seeing this page, the system is running but some components need attention.</p>
        <p>Run the <strong>System Diagnostic</strong> to identify and fix issues.</p>
    </div>
</div>

<?php
if (file_exists(__DIR__ . '/layout/footer.php')) {
    include __DIR__ . '/layout/footer.php';
} else {
    echo "</body></html>";
}
?>