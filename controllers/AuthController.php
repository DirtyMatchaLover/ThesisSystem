<?php
require_once __DIR__ . '/../helpers/ActivityTracker.php';

class AuthController {
    private $maxLoginAttempts = 5;
    private $lockoutTime = 900; // 15 minutes in seconds
    private $activityTracker;

    public function __construct() {
        $this->activityTracker = new ActivityTracker();
    }

    public function login() {
        $role = $_GET['role'] ?? ($_POST['role'] ?? null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                $error = "Invalid request. Please try again.";
                $this->logSecurityEvent('csrf_failure', $_POST['identifier'] ?? 'unknown');
                require __DIR__ . '/../views/auth/login.php';
                return;
            }

            $identifier = trim($_POST['identifier'] ?? '');
            $password   = $_POST['password'] ?? '';

            // Check rate limiting
            if ($this->isRateLimited($identifier)) {
                $error = "Too many login attempts. Please try again in 15 minutes.";
                $this->logSecurityEvent('rate_limit_exceeded', $identifier);
                require __DIR__ . '/../views/auth/login.php';
                return;
            }

            $user = User::findByEmailOrEmployeeId($identifier);

            if ($user && password_verify($password, $user['password']) && $user['role'] === $role) {
                // Clear failed attempts on successful login
                $this->clearLoginAttempts($identifier);

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user'] = $user;
                $this->logSecurityEvent('login_success', $identifier);

                // Track login activity (only for librarian, faculty, student)
                if (in_array($user['role'], ['librarian', 'faculty', 'student'])) {
                    try {
                        // Log to user_sessions table
                        require_once __DIR__ . '/../models/Database.php';
                        $db = Database::getInstance();
                        $stmt = $db->prepare("
                            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $user['id'],
                            session_id(),
                            $_SERVER['REMOTE_ADDR'] ?? null,
                            $_SERVER['HTTP_USER_AGENT'] ?? null
                        ]);

                        // Log to user_activities table
                        $this->activityTracker->logActivity(
                            $user['id'],
                            'user_login',
                            'User logged in as ' . $user['role'],
                            null,
                            ['ip' => $_SERVER['REMOTE_ADDR'] ?? null]
                        );
                    } catch (Exception $e) {
                        error_log("Activity tracking error on login: " . $e->getMessage());
                    }
                }

                redirect('home');
            } else {
                // Record failed attempt
                $this->recordLoginAttempt($identifier);

                $error = "Invalid credentials. Please check your email/ID and password.";
                $this->logSecurityEvent('login_failure', $identifier);
                require __DIR__ . '/../views/auth/login.php';
            }
        } else {
            require __DIR__ . '/../views/auth/login.php';
        }
    }

    public function register() {
        echo "Register page (not implemented yet)";
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                $error = "Invalid request. Please try again.";
                require __DIR__ . '/../views/auth/forgot_password.php';
                return;
            }

            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $error = "Please enter your email address.";
                require __DIR__ . '/../views/auth/forgot_password.php';
                return;
            }

            // Check rate limiting for password reset
            if ($this->isPasswordResetRateLimited($email)) {
                $error = "Too many password reset attempts. Please try again in 15 minutes.";
                $this->logSecurityEvent('password_reset_rate_limited', $email);
                require __DIR__ . '/../views/auth/forgot_password.php';
                return;
            }

            // Record reset attempt
            $this->recordPasswordResetAttempt($email);

            // Check if user exists
            $user = User::findByEmailOrEmployeeId($email);

            if (!$user) {
                // Don't reveal if email exists or not (security best practice)
                $success = "If that email address is in our system, we've sent password reset instructions.";
                $this->logSecurityEvent('password_reset_attempted', $email . ' (not found)');
                require __DIR__ . '/../views/auth/forgot_password.php';
                return;
            }

            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $resetTokenHash = hash('sha256', $resetToken); // Hash the token before storing
            $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save hashed token to database (you'll need to add these columns to users table)
            try {
                $db = Database::getInstance();
                $stmt = $db->prepare("
                    UPDATE users
                    SET reset_token = ?, reset_token_expires = ?
                    WHERE id = ?
                ");
                $stmt->execute([$resetTokenHash, $resetExpiry, $user['id']]);

                // In a real application, send email here
                // For now, we'll show the reset link (REMOVE IN PRODUCTION)
                $resetLink = url('auth/reset-password', ['token' => $resetToken]);

                $this->logSecurityEvent('password_reset_requested', $email);

                $success = "If that email address is in our system, we've sent password reset instructions.";

                // TODO: Send email with reset link
                // mail($email, "Password Reset", "Click here to reset: $resetLink");

            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "An error occurred. Please try again later.";
            }
        }

        require __DIR__ . '/../views/auth/forgot_password.php';
    }

    /**
     * Handle password reset with token
     */
    public function resetPassword() {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $error = "Invalid reset link.";
            require __DIR__ . '/../views/auth/reset_password.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verify CSRF token
            if (!verify_csrf_token()) {
                $error = "Invalid request. Please try again.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            $newPassword = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate passwords
            if (empty($newPassword) || empty($confirmPassword)) {
                $error = "Please fill in all fields.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $error = "Passwords do not match.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            // Validate password strength
            if (strlen($newPassword) < 8) {
                $error = "Password must be at least 8 characters.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            if (!preg_match('/[A-Z]/', $newPassword)) {
                $error = "Password must contain at least one uppercase letter.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            if (!preg_match('/[a-z]/', $newPassword)) {
                $error = "Password must contain at least one lowercase letter.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            if (!preg_match('/[0-9]/', $newPassword)) {
                $error = "Password must contain at least one number.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;
            }

            // Verify token (hash it before comparing with database)
            try {
                $db = Database::getInstance();
                $tokenHash = hash('sha256', $token);
                $stmt = $db->prepare("
                    SELECT * FROM users
                    WHERE reset_token = ?
                    AND reset_token_expires > NOW()
                ");
                $stmt->execute([$tokenHash]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = "Invalid or expired reset link.";
                    $this->logSecurityEvent('password_reset_invalid_token', $token);
                    require __DIR__ . '/../views/auth/reset_password.php';
                    return;
                }

                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE users
                    SET password = ?, reset_token = NULL, reset_token_expires = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$hashedPassword, $user['id']]);

                $this->logSecurityEvent('password_reset_completed', $user['email']);

                $success = "Your password has been reset successfully. You can now log in.";
                require __DIR__ . '/../views/auth/reset_password.php';
                return;

            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $error = "An error occurred. Please try again.";
            }
        }

        require __DIR__ . '/../views/auth/reset_password.php';
    }

    public function logout() {
        if (is_logged_in()) {
            $user = current_user();
            $this->logSecurityEvent('logout', $user['email'] ?? 'unknown');

            // Track logout activity (only for librarian, faculty, student)
            if (in_array($user['role'], ['librarian', 'faculty', 'student'])) {
                try {
                    // Update user_sessions table with logout time
                    require_once __DIR__ . '/../models/Database.php';
                    $db = Database::getInstance();
                    $stmt = $db->prepare("
                        UPDATE user_sessions
                        SET logout_at = NOW()
                        WHERE user_id = ? AND session_id = ?
                        ORDER BY login_at DESC
                        LIMIT 1
                    ");
                    $stmt->execute([
                        $user['id'],
                        session_id()
                    ]);

                    // Log to user_activities table
                    $this->activityTracker->logActivity(
                        $user['id'],
                        'user_logout',
                        'User logged out',
                        null,
                        ['ip' => $_SERVER['REMOTE_ADDR'] ?? null]
                    );
                } catch (Exception $e) {
                    error_log("Activity tracking error on logout: " . $e->getMessage());
                }
            }
        }
        session_destroy();
        redirect('home');
    }

    /**
     * Check if user is rate limited
     */
    private function isRateLimited($identifier) {
        $key = 'login_attempts_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            return false;
        }

        $attempts = $_SESSION[$key];

        if ($attempts['count'] >= $this->maxLoginAttempts) {
            $timeSinceLastAttempt = time() - $attempts['last_attempt'];

            if ($timeSinceLastAttempt < $this->lockoutTime) {
                return true;
            } else {
                // Lockout period expired, clear attempts
                unset($_SESSION[$key]);
                return false;
            }
        }

        return false;
    }

    /**
     * Record a failed login attempt
     */
    private function recordLoginAttempt($identifier) {
        $key = 'login_attempts_' . md5($identifier);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'last_attempt' => time()
            ];
        }

        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }

    /**
     * Clear login attempts on successful login
     */
    private function clearLoginAttempts($identifier) {
        $key = 'login_attempts_' . md5($identifier);
        unset($_SESSION[$key]);
    }

    /**
     * Check if password reset is rate limited
     */
    private function isPasswordResetRateLimited($email) {
        $key = 'password_reset_attempts_' . md5($email);

        if (!isset($_SESSION[$key])) {
            return false;
        }

        $attempts = $_SESSION[$key];

        if ($attempts['count'] >= 3) { // Limit to 3 attempts
            $timeSinceLastAttempt = time() - $attempts['last_attempt'];

            if ($timeSinceLastAttempt < $this->lockoutTime) {
                return true;
            } else {
                // Lockout period expired, clear attempts
                unset($_SESSION[$key]);
                return false;
            }
        }

        return false;
    }

    /**
     * Record a password reset attempt
     */
    private function recordPasswordResetAttempt($email) {
        $key = 'password_reset_attempts_' . md5($email);

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'last_attempt' => time()
            ];
        }

        $_SESSION[$key]['count']++;
        $_SESSION[$key]['last_attempt'] = time();
    }

    /**
     * Log security events
     */
    private function logSecurityEvent($event, $identifier) {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);

        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $logEntry = sprintf(
            "[%s] %s | IP: %s | Identifier: %s | User-Agent: %s\n",
            $timestamp,
            strtoupper($event),
            $ip,
            $identifier,
            $userAgent
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
