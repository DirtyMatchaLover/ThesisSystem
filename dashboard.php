<?php
// dashboard.php
require_once 'includes/config.php';

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data from database for extra security
$stmt = $pdo->prepare("SELECT username, full_name, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// If user doesn't exist (shouldn't happen), logout
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Update session with fresh data from database
$_SESSION['username'] = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['user_role'] = $user['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PCC ThesisHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .welcome-header {
            background: #2c5aa0;
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #2c5aa0;
        }
        .card h3 {
            color: #2c5aa0;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            background: #2c5aa0;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #1a4f8b;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c5aa0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="welcome-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</h1>
            <p>Your role: <strong><?php echo ucfirst($_SESSION['user_role']); ?></strong></p>
            <p>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>

        <!-- Quick Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Theses Submitted</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Pending Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">0</div>
                <div class="stat-label">Approved Theses</div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="dashboard-cards">
            <!-- Student Cards -->
            <?php if ($_SESSION['user_role'] === 'student'): ?>
                <div class="card">
                    <h3>📤 Submit Thesis</h3>
                    <p>Submit your research work for review and approval.</p>
                    <a href="submit_thesis.php" class="btn">Submit Now</a>
                </div>
                
                <div class="card">
                    <h3>📋 My Submissions</h3>
                    <p>View the status of your submitted theses and feedback.</p>
                    <a href="my_submissions.php" class="btn">View Submissions</a>
                </div>
                
                <div class="card">
                    <h3>🔍 Browse Theses</h3>
                    <p>Explore approved theses from other students.</p>
                    <a href="browse.php" class="btn">Browse Library</a>
                </div>
            <?php endif; ?>

            <!-- Teacher Cards -->
            <?php if ($_SESSION['user_role'] === 'teacher'): ?>
                <div class="card">
                    <h3>📝 Review Queue</h3>
                    <p>Theses waiting for your review and feedback.</p>
                    <a href="review_queue.php" class="btn">Start Reviewing</a>
                </div>
                
                <div class="card">
                    <h3>✅ Approved Theses</h3>
                    <p>View theses you have already approved.</p>
                    <a href="approved_theses.php" class="btn">View Approved</a>
                </div>
                
                <div class="card">
                    <h3>📊 Review Statistics</h3>
                    <p>See your reviewing activity and metrics.</p>
                    <a href="review_stats.php" class="btn">View Stats</a>
                </div>
            <?php endif; ?>

            <!-- Admin Cards -->
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <div class="card">
                    <h3>👥 Manage Users</h3>
                    <p>Add, edit, or remove user accounts.</p>
                    <a href="manage_users.php" class="btn">User Management</a>
                </div>
                
                <div class="card">
                    <h3>📚 Manage Theses</h3>
                    <p>Oversee all thesis submissions and publications.</p>
                    <a href="manage_theses.php" class="btn">Thesis Management</a>
                </div>
                
                <div class="card">
                    <h3>⚙️ System Settings</h3>
                    <p>Configure system preferences and options.</p>
                    <a href="system_settings.php" class="btn">System Settings</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <h3>🚀 Quick Actions</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="profile.php" class="btn">Edit Profile</a>
                <a href="change_password.php" class="btn">Change Password</a>
                <a href="help.php" class="btn">Help & Support</a>
                <a href="logout.php" class="btn" style="background: #dc3545;">Logout</a>
            </div>
        </div>

        <!-- Debug Section (remove later) -->
        <div class="card" style="margin-top: 2rem; background: #f8f9fa;">
            <h3>🛠️ Debug Info</h3>
            <pre>Session Data: <?php print_r($_SESSION); ?></pre>
            <pre>User Data from DB: <?php print_r($user); ?></pre>
        </div>
    </div>
</body>
</html>