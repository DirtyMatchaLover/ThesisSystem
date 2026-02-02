<?php include __DIR__ . '/../layout/header.php'; ?>
<?php include __DIR__ . '/../layout/navigation.php'; ?>

<div class="container">
    <h2 class="page-title">️ Profile Settings</h2>

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

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header" data-strand="<?= htmlspecialchars($userDetails['strand'] ?? '') ?>">
            <div class="profile-avatar">
                <?php
                $initials = '';
                if (!empty($userDetails['name'])) {
                    $nameParts = explode(' ', trim($userDetails['name']));
                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                    if (count($nameParts) > 1) {
                        $initials .= strtoupper(substr(end($nameParts), 0, 1));
                    }
                }
                echo $initials ?: '';
                ?>
            </div>
            <div class="profile-info">
                <h3><?= htmlspecialchars($userDetails['name']) ?></h3>
                <p class="role"><?= htmlspecialchars(ucwords($userDetails['role'])) ?></p>
                <p class="email"><?= htmlspecialchars($userDetails['email']) ?></p>
            </div>
        </div>

        <!-- Settings Tabs -->
        <div class="settings-tabs">
            <button class="tab-button active" onclick="showTab('profile')">
                 Personal Information
            </button>
            <button class="tab-button" onclick="showTab('password')">
                 Change Password
            </button>
            <button class="tab-button" onclick="showTab('account')">
                 Account Details
            </button>
        </div>

        <!-- Personal Information Tab -->
        <div id="profile-tab" class="tab-content active">
            <div class="settings-section">
                <h4> Personal Information</h4>
                <form method="POST" action="<?= route('profile/update') ?>" class="profile-form">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="update_type" value="profile">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($userDetails['name'] ?? '') ?>" 
                                   required 
                                   placeholder="Enter your full name">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($userDetails['email'] ?? '') ?>" 
                                   required 
                                   placeholder="Enter your email address">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($userDetails['phone'] ?? '') ?>" 
                                   placeholder="Enter your phone number">
                        </div>

                        <!-- Department field - read-only for students -->
                        <div class="form-group">
                            <label for="department">Department</label>
                            <?php if ($userDetails['role'] === 'student'): ?>
                                <!-- Read-only for students -->
                                <input type="text" 
                                       id="department" 
                                       value="<?= htmlspecialchars($userDetails['department'] ?? 'Senior High School') ?>" 
                                       readonly 
                                       class="readonly-field"
                                       title="Department cannot be changed by students">
                                <!-- Hidden field to preserve value -->
                                <input type="hidden" name="department" value="<?= htmlspecialchars($userDetails['department'] ?? 'Senior High School') ?>">
                                <small class="field-note">* Department is managed by administration</small>
                            <?php else: ?>
                                <!-- Editable for faculty/admin -->
                                <select id="department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="Senior High School" <?= ($userDetails['department'] ?? '') === 'Senior High School' ? 'selected' : '' ?>>Senior High School</option>
                                    <option value="IT Department" <?= ($userDetails['department'] ?? '') === 'IT Department' ? 'selected' : '' ?>>IT Department</option>
                                    <option value="Library Services" <?= ($userDetails['department'] ?? '') === 'Library Services' ? 'selected' : '' ?>>Library Services</option>
                                    <option value="Administration" <?= ($userDetails['department'] ?? '') === 'Administration' ? 'selected' : '' ?>>Administration</option>
                                    <option value="Faculty" <?= ($userDetails['department'] ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                                </select>
                            <?php endif; ?>
                        </div>

                        <?php if ($userDetails['role'] === 'student'): ?>
                            <div class="form-group">
                                <label for="strand">Academic Strand</label>
                                <select id="strand" name="strand">
                                    <option value="">Select Strand</option>
                                    <option value="STEM" <?= ($userDetails['strand'] ?? '') === 'STEM' ? 'selected' : '' ?>>STEM - Science, Technology, Engineering, Mathematics</option>
                                    <option value="HUMSS" <?= ($userDetails['strand'] ?? '') === 'HUMSS' ? 'selected' : '' ?>>HUMSS - Humanities and Social Sciences</option>
                                    <option value="ABM" <?= ($userDetails['strand'] ?? '') === 'ABM' ? 'selected' : '' ?>>ABM - Accountancy, Business and Management</option>
                                    <option value="TVL-HE" <?= ($userDetails['strand'] ?? '') === 'TVL-HE' ? 'selected' : '' ?>>TVL-HE - Home Economics</option>
                                    <option value="TVL-ICT" <?= ($userDetails['strand'] ?? '') === 'TVL-ICT' ? 'selected' : '' ?>>TVL-ICT - Information and Communications Technology</option>
                                    <option value="ADT" <?= ($userDetails['strand'] ?? '') === 'ADT' ? 'selected' : '' ?>>ADT - Arts and Design Track</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="year_level">Year Level</label>
                                <select id="year_level" name="year_level">
                                    <option value="">Select Year Level</option>
                                    <option value="Grade 11" <?= ($userDetails['year_level'] ?? '') === 'Grade 11' ? 'selected' : '' ?>>Grade 11</option>
                                    <option value="Grade 12" <?= ($userDetails['year_level'] ?? '') === 'Grade 12' ? 'selected' : '' ?>>Grade 12</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                             Update Profile
                        </button>
                        <a href="<?= route('home') ?>" class="btn btn-secondary">
                             Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Tab -->
        <div id="password-tab" class="tab-content">
            <div class="settings-section">
                <h4> Change Password</h4>
                <form method="POST" action="<?= route('profile/update') ?>" class="profile-form">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="update_type" value="password">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" 
                                   id="current_password" 
                                   name="current_password" 
                                   required 
                                   placeholder="Enter your current password">
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" 
                                   id="new_password" 
                                   name="new_password" 
                                   required 
                                   minlength="6"
                                   placeholder="Enter new password (min. 6 characters)">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   required 
                                   minlength="6"
                                   placeholder="Confirm your new password">
                        </div>
                    </div>

                    <div class="password-requirements">
                        <h5>Password Requirements:</h5>
                        <ul>
                            <li>At least 6 characters long</li>
                            <li>Should contain a mix of letters and numbers for security</li>
                            <li>Avoid using easily guessable information</li>
                        </ul>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                             Change Password
                        </button>
                        <button type="reset" class="btn btn-secondary">
                             Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Details Tab -->
        <div id="account-tab" class="tab-content">
            <div class="settings-section">
                <h4> Account Details</h4>
                <div class="account-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">🆔 User ID:</span>
                            <span class="info-value">#<?= $userDetails['id'] ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label"> Employee ID:</span>
                            <span class="info-value"><?= htmlspecialchars($userDetails['employee_id'] ?? 'Not set') ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label"> Role:</span>
                            <span class="info-value role-badge role-<?= $userDetails['role'] ?>">
                                <?= htmlspecialchars(ucwords($userDetails['role'])) ?>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label"> Member Since:</span>
                            <span class="info-value"><?= date('F j, Y', strtotime($userDetails['created_at'])) ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label"> Last Updated:</span>
                            <span class="info-value"><?= date('F j, Y g:i A', strtotime($userDetails['updated_at'])) ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label"> Status:</span>
                            <span class="info-value status-active">
                                <?= htmlspecialchars(ucwords($userDetails['status'] ?? 'active')) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($userDetails['role'] === 'student'): ?>
                        <div class="student-info">
                            <h5> Academic Information</h5>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label"> Department:</span>
                                    <span class="info-value"><?= htmlspecialchars($userDetails['department'] ?? 'Not set') ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"> Strand:</span>
                                    <span class="info-value"><?= htmlspecialchars($userDetails['strand'] ?? 'Not set') ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label"> Year Level:</span>
                                    <span class="info-value"><?= htmlspecialchars($userDetails['year_level'] ?? 'Not set') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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

.profile-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.profile-header {
    background: linear-gradient(135deg, #d32f2f, #b71c1c);
    color: white;
    padding: 30px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 600;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.profile-info h3 {
    margin: 0 0 5px 0;
    font-size: 1.5rem;
}

.profile-info .role {
    margin: 0 0 3px 0;
    opacity: 0.9;
    font-size: 1rem;
}

.profile-info .email {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.settings-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.tab-button {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    color: #666;
    transition: all 0.3s ease;
}

.tab-button:hover {
    background: #e9ecef;
    color: #333;
}

.tab-button.active {
    background: white;
    color: #d32f2f;
    border-bottom: 3px solid #d32f2f;
}

.tab-content {
    display: none;
    padding: 30px;
}

.tab-content.active {
    display: block;
}

.settings-section h4 {
    color: #333;
    margin: 0 0 25px 0;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    color: #333;
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.form-group input,
.form-group select {
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #d32f2f;
    box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.1);
}

/* Read-only field styling for students */
.readonly-field {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    border-color: #dee2e6 !important;
}

.readonly-field:focus {
    border-color: #dee2e6 !important;
    box-shadow: none !important;
}

.field-note {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 5px;
    font-style: italic;
}

.password-requirements {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.password-requirements h5 {
    margin: 0 0 10px 0;
    color: #333;
}

.password-requirements ul {
    margin: 0;
    padding-left: 20px;
    color: #666;
}

.password-requirements li {
    margin-bottom: 5px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-start;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #d32f2f;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0.9;
}

.account-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.info-label {
    font-weight: 600;
    color: #666;
}

.info-value {
    color: #333;
}

.role-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.role-student { background: #e3f2fd; color: #1976d2; }
.role-faculty { background: #e8f5e8; color: #2e7d32; }
.role-admin { background: #fff3e0; color: #f57c00; }
.role-librarian { background: #f3e5f5; color: #7b1fa2; }

.status-active {
    color: #28a745;
    font-weight: 600;
}

.student-info {
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
}

.student-info h5 {
    margin: 0 0 15px 0;
    color: #333;
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
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .settings-tabs {
        flex-direction: column;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        justify-content: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (newPassword && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>