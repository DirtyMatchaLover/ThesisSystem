<?php
// reset-users.php - Remove existing accounts and create new ones
require_once __DIR__ . '/models/Database.php';

echo "<h1>👥 Reset User Accounts</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
    .good { color: green; font-weight: bold; }
    .bad { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .account-box { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #d32f2f; }
    .button { display: inline-block; padding: 12px 24px; background: #d32f2f; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
    .button:hover { background: #b71c1c; }
    .button.danger { background: #dc3545; }
    .button.danger:hover { background: #c82333; }
</style>";

echo "<div class='container'>";

if (isset($_POST['confirm']) && $_POST['confirm'] === 'reset_all_users') {
    try {
        $db = Database::getInstance();
        
        echo "<h2>🗑️ Removing Existing Users...</h2>";
        
        // Remove all existing users (except keep the structure)
        $stmt = $db->prepare("DELETE FROM users WHERE id > 0");
        $result = $stmt->execute();
        $deletedCount = $stmt->rowCount();
        
        echo "<p class='good'>✅ Removed $deletedCount existing user accounts</p>";
        
        // Reset auto-increment
        $db->exec("ALTER TABLE users AUTO_INCREMENT = 1");
        
        echo "<h2>👥 Creating New User Accounts...</h2>";
        
        // Password hash for 'password123' (use the same for all test accounts)
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        
        // Create new users for each role
        $newUsers = [
            // Admin Account
            [
                'employee_id' => 'ADMIN001',
                'name' => 'System Administrator',
                'email' => 'admin@pcc.edu.ph',
                'password' => $passwordHash,
                'role' => 'admin',
                'department' => 'IT Department',
                'strand' => null,
                'year_level' => null
            ],
            
            // Faculty Account
            [
                'employee_id' => 'FAC001',
                'name' => 'Dr. Maria Santos',
                'email' => 'faculty@pcc.edu.ph',
                'password' => $passwordHash,
                'role' => 'faculty',
                'department' => 'Senior High School',
                'strand' => null,
                'year_level' => null
            ],
            
            // Librarian Account
            [
                'employee_id' => 'LIB001',
                'name' => 'Ms. Angela Cruz',
                'email' => 'librarian@pcc.edu.ph',
                'password' => $passwordHash,
                'role' => 'librarian',
                'department' => 'Library Services',
                'strand' => null,
                'year_level' => null
            ],
            
            // Student Account - STEM
            [
                'employee_id' => 'STU001',
                'name' => 'Juan Dela Cruz',
                'email' => 'student@pcc.edu.ph',
                'password' => $passwordHash,
                'role' => 'student',
                'department' => 'Senior High School',
                'strand' => 'STEM',
                'year_level' => 'Grade 12'
            ],
            
            // Student Account - ABM
            [
                'employee_id' => 'STU002',
                'name' => 'Maria Gonzales',
                'email' => 'student.abm@pcc.edu.ph',
                'password' => $passwordHash,
                'role' => 'student',
                'department' => 'Senior High School',
                'strand' => 'ABM',
                'year_level' => 'Grade 12'
            ],
            
            // Student Account - HUMSS
            [
                'employee_id' => 'STU003',
                'name' => 'Jose Reyes',
                'email' => 'student.humss@pcc.edu.ph',
                'password' => $passwordHash,
                'role' => 'student',
                'department' => 'Senior High School',
                'strand' => 'HUMSS',
                'year_level' => 'Grade 12'
            ]
        ];
        
        // Insert new users
        $stmt = $db->prepare("
            INSERT INTO users (employee_id, name, email, password, role, department, strand, year_level, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        foreach ($newUsers as $user) {
            $result = $stmt->execute([
                $user['employee_id'],
                $user['name'],
                $user['email'],
                $user['password'],
                $user['role'],
                $user['department'],
                $user['strand'],
                $user['year_level']
            ]);
            
            if ($result) {
                echo "<p class='good'>✅ Created {$user['role']}: {$user['name']} ({$user['email']})</p>";
            } else {
                echo "<p class='bad'>❌ Failed to create: {$user['name']}</p>";
            }
        }
        
        echo "<h2>🎉 User Reset Complete!</h2>";
        echo "<p class='info'>All accounts use the password: <strong>password123</strong></p>";
        
        echo "<div style='margin-top: 30px;'>";
        echo "<a href='index.php' class='button'>🏠 Go to Homepage</a>";
        echo "<a href='index.php?route=auth/select' class='button'>🔐 Test Login</a>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p class='bad'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
} else {
    // Show confirmation form with new account details
    echo "<h2>🔄 Reset User Accounts</h2>";
    echo "<div class='warning' style='background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Warning</h3>";
    echo "<p>This will <strong>permanently delete ALL existing user accounts</strong> and create new ones.</p>";
    echo "</div>";
    
    echo "<h3>📋 New Accounts to be Created:</h3>";
    
    $accounts = [
        ['role' => 'Admin', 'name' => 'System Administrator', 'email' => 'admin@pcc.edu.ph', 'access' => 'Full system access'],
        ['role' => 'Faculty', 'name' => 'Dr. Maria Santos', 'email' => 'faculty@pcc.edu.ph', 'access' => 'Review & approve theses'],
        ['role' => 'Librarian', 'name' => 'Ms. Angela Cruz', 'email' => 'librarian@pcc.edu.ph', 'access' => 'Manage archives'],
        ['role' => 'Student (STEM)', 'name' => 'Juan Dela Cruz', 'email' => 'student@pcc.edu.ph', 'access' => 'Submit theses'],
        ['role' => 'Student (ABM)', 'name' => 'Maria Gonzales', 'email' => 'student.abm@pcc.edu.ph', 'access' => 'Submit theses'],
        ['role' => 'Student (HUMSS)', 'name' => 'Jose Reyes', 'email' => 'student.humss@pcc.edu.ph', 'access' => 'Submit theses']
    ];
    
    foreach ($accounts as $account) {
        echo "<div class='account-box'>";
        echo "<strong>{$account['role']}:</strong> {$account['name']}<br>";
        echo "<strong>Email:</strong> {$account['email']}<br>";
        echo "<strong>Password:</strong> password123<br>";
        echo "<strong>Access:</strong> {$account['access']}";
        echo "</div>";
    }
    
    echo "<div style='margin-top: 30px; text-align: center;'>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='confirm' value='reset_all_users'>";
    echo "<button type='submit' class='button danger' onclick='return confirm(\"Are you sure you want to delete ALL existing users and create new ones?\")'>🗑️ Yes, Reset All User Accounts</button>";
    echo "</form>";
    echo "</div>";
    
    echo "<div style='margin-top: 20px; text-align: center;'>";
    echo "<a href='index.php' class='button'>🏠 Cancel and Go Back</a>";
    echo "</div>";
}

echo "</div>";
?>