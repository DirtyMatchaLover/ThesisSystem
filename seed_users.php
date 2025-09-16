<?php
require_once __DIR__ . '/models/Database.php';

try {
    $db = Database::getInstance();

    $users = [
        ['Faculty Librarian', 'librarian@pcc.local', 'password123', 'faculty'],
        ['Test Student', 'student@pcc.local', 'student123', 'student']
    ];

    foreach ($users as $u) {
        [$name, $email, $password, $role] = $u;

        // Generate hash
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert only if email doesn't exist
        $stmt = $db->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "ℹ️ User {$email} already exists, skipped.<br>";
            continue;
        }

        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $role]);
        echo "✅ Created {$role}: {$email} / {$password}<br>";
    }

    echo "<br>Seeding complete!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
