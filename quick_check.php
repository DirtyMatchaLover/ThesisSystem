<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/models/Database.php';

$db = Database::getInstance();

echo "<h2>Quick Database Check</h2>";
echo "<style>body{font-family:monospace;padding:20px;} .box{background:#f0f0f0;padding:10px;margin:10px 0;border-left:4px solid #7b3f00;}</style>";

// Count theses
$stmt = $db->query("SELECT COUNT(*) as count FROM theses");
$thesisCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "<div class='box'><strong>Total Theses:</strong> $thesisCount</div>";

// Count users by role
$stmt = $db->query("
    SELECT
        COUNT(CASE WHEN role = 'faculty' THEN 1 END) as teachers,
        COUNT(CASE WHEN role = 'librarian' THEN 1 END) as librarian,
        COUNT(CASE WHEN role = 'student' THEN 1 END) as students
    FROM users
    WHERE status = 'active'
");
$users = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<div class='box'>";
echo "<strong>Participants (Users):</strong><br>";
echo "Teachers: {$users['teachers']}<br>";
echo "Librarian: {$users['librarian']}<br>";
echo "Students: {$users['students']}<br>";
echo "Total: " . ($users['teachers'] + $users['librarian'] + $users['students']);
echo "</div>";

// Check if metrics should be 0
if ($thesisCount == 0) {
    echo "<div class='box' style='border-left-color:green;background:#d4edda;'>";
    echo "<strong>✅ Expected Result:</strong> All 8 metrics should show 0<br>";
    echo "<strong>✅ Participants:</strong> Should show real user counts (Teachers: {$users['teachers']}, Librarian: {$users['librarian']}, Students: {$users['students']})";
    echo "</div>";
} else {
    echo "<div class='box' style='border-left-color:orange;'>";
    echo "<strong>⚠️ Warning:</strong> Theses exist in database, metrics will calculate from real data";
    echo "</div>";
}

echo "<p><a href='index.php?route=admin/analytics/research'>→ View Research Analytics Page</a></p>";
?>
