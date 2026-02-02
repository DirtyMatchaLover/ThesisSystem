<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="container">
    <h2 class="page-title"> User Management</h2>

    <!-- Flash Messages -->
    <?php if (function_exists('has_flash') && has_flash('success')): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars(get_flash('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (function_exists('has_flash') && has_flash('error')): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars(get_flash('error')) ?>
        </div>
    <?php endif; ?>

    <!-- User Statistics -->
    <div class="stats-container">
        <?php 
        $userStats = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $role = $user['role'] ?? 'unknown';
                $userStats[$role] = ($userStats[$role] ?? 0) + 1;
            }
        }
        ?>
        
        <div class="stat-card">
            <h3><?= count($users ?? []) ?></h3>
            <p>Total Users</p>
            <small>All roles</small>
        </div>
        <div class="stat-card">
            <h3><?= $userStats['student'] ?? 0 ?></h3>
            <p>Students</p>
            <small>Active accounts</small>
        </div>
        <div class="stat-card">
            <h3><?= $userStats['faculty'] ?? 0 ?></h3>
            <p>Faculty</p>
            <small>Teaching staff</small>
        </div>
        <div class="stat-card">
            <h3><?= ($userStats['admin'] ?? 0) + ($userStats['librarian'] ?? 0) ?></h3>
            <p>Administrators</p>
            <small>Admin & Librarians</small>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="<?= route('admin/dashboard') ?>" class="btn btn-secondary">
             Back to Dashboard
        </a>
        <button onclick="window.print()" class="btn btn-info">
            ️ Print User List
        </button>
    </div>

    <!-- Users Table -->
    <div class="users-container">
        <div class="table-header">
            <h3>User Directory</h3>
            <div class="table-info">
                Total: <?= count($users ?? []) ?> users
            </div>
        </div>

        <?php if (!empty($users)): ?>
            <div class="users-grid">
                <?php foreach ($users as $user): ?>
                    <div class="user-card" data-role="<?= $user['role'] ?? 'unknown' ?>">
                        <div class="user-header">
                            <div class="user-avatar">
                                <?php
                                $initials = '';
                                if (!empty($user['name'])) {
                                    $nameParts = explode(' ', trim($user['name']));
                                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                                    if (count($nameParts) > 1) {
                                        $initials .= strtoupper(substr(end($nameParts), 0, 1));
                                    }
                                }
                                echo $initials ?: '';
                                ?>
                            </div>
                            <div class="user-info">
                                <h4><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></h4>
                                <p class="user-email"><?= htmlspecialchars($user['email'] ?? 'No email') ?></p>
                            </div>
                            <div class="user-role">
                                <span class="role-badge role-<?= $user['role'] ?? 'unknown' ?>">
                                    <?= htmlspecialchars(ucwords($user['role'] ?? 'Unknown')) ?>
                                </span>
                            </div>
                        </div>

                        <div class="user-details">
                            <div class="detail-item">
                                <span class="label"> Joined:</span>
                                <span class="value">
                                    <?= date('M j, Y', strtotime($user['created_at'] ?? 'now')) ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($user['strand'])): ?>
                                <div class="detail-item">
                                    <span class="label"> Strand:</span>
                                    <span class="value"><?= htmlspecialchars($user['strand']) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <span class="label">🆔 User ID:</span>
                                <span class="value">#<?= $user['id'] ?></span>
                            </div>
                        </div>

                        <!-- User Activity Summary -->
                        <div class="user-activity">
                            <?php if ($user['role'] === 'student'): ?>
                                <div class="activity-item">
                                    <span class="activity-label"> Submissions:</span>
                                    <span class="activity-value">
                                        <?php
                                        // This would normally come from a join query
                                        // For now, we'll show a placeholder
                                        echo "N/A";
                                        ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="activity-item">
                                    <span class="activity-label"> Role:</span>
                                    <span class="activity-value">
                                        <?= $user['role'] === 'admin' ? 'System Admin' : 'Staff Member' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Actions -->
                        <div class="user-actions">
                            <?php if ($user['role'] === 'student'): ?>
                                <span class="action-btn info"> Student Account</span>
                            <?php elseif ($user['role'] === 'faculty'): ?>
                                <span class="action-btn success">‍ Faculty Member</span>
                            <?php elseif ($user['role'] === 'admin'): ?>
                                <span class="action-btn warning"> Administrator</span>
                            <?php elseif ($user['role'] === 'librarian'): ?>
                                <span class="action-btn primary"> Librarian</span>
                            <?php endif; ?>

                            <?php if (in_array($user['role'], ['student', 'faculty', 'librarian'])): ?>
                                <a href="<?= route('admin/individual-report') ?>&user_id=<?= $user['id'] ?>"
                                   class="action-btn"
                                   style="background: #2196f3; color: white; text-decoration: none;">
                                    📊 View Activity Report
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-users">
                <div class="no-users-icon"></div>
                <h3>No Users Found</h3>
                <p>No users have been registered in the system yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-title {
    color: #d32f2f;
    margin-bottom: 30px;
    font-size: 2.2rem;
    font-weight: 600;
    text-align: center;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    border-left: 5px solid #d32f2f;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-card h3 {
    font-size: 2rem;
    margin: 0 0 10px 0;
    color: #d32f2f;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    justify-content: center;
    flex-wrap: wrap;
}

.users-container {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.table-header h3 {
    color: #333;
    margin: 0;
    font-size: 1.5rem;
}

.table-info {
    color: #666;
    font-size: 0.9rem;
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.user-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
}

.user-card:hover {
    border-color: #d32f2f;
    box-shadow: 0 8px 25px rgba(211, 47, 47, 0.15);
    transform: translateY(-3px);
}

.user-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: #d32f2f;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
}

.user-info {
    flex: 1;
}

.user-info h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 1.1rem;
}

.user-email {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.role-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-student { background: #e3f2fd; color: #1976d2; }
.role-faculty { background: #e8f5e8; color: #2e7d32; }
.role-admin { background: #fff3e0; color: #f57c00; }
.role-librarian { background: #f3e5f5; color: #7b1fa2; }
.role-unknown { background: #f5f5f5; color: #666; }

.user-details {
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.detail-item .label {
    color: #666;
    font-weight: 500;
}

.detail-item .value {
    color: #333;
}

.user-activity {
    padding: 10px;
    background: white;
    border-radius: 6px;
    margin-bottom: 15px;
}

.activity-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
}

.activity-label {
    color: #666;
}

.activity-value {
    color: #333;
    font-weight: 500;
}

.user-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: default;
}

.action-btn.info { background: #e3f2fd; color: #1976d2; }
.action-btn.success { background: #e8f5e8; color: #2e7d32; }
.action-btn.warning { background: #fff3e0; color: #f57c00; }
.action-btn.primary { background: #f3e5f5; color: #7b1fa2; }

.btn {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary { background: #d32f2f; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-info { background: #17a2b8; color: white; }

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0.9;
}

.no-users {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-users-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.no-users h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .users-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .table-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .user-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>