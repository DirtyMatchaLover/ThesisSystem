<?php
// ---------------------------
// Base Configuration
// ---------------------------
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/thesis-management-system'); 
}

// ---------------------------
// URL and Route Helpers
// ---------------------------

/**
 * Build a URL to an asset (CSS, JS, images, uploads)
 */
function asset(string $path): string {
    return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/');
}

/**
 * Build a route URL (ex: route('auth/login'))
 */
function route(string $path = ''): string {
    return rtrim(BASE_PATH, '/') . '/index.php?route=' . ltrim($path, '/');
}

/**
 * Create URL helper with parameters
 */
function url(string $path = '', array $params = []): string {
    $url = route($path);
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

/**
 * Redirect to a route
 */
function redirect(string $path): void {
    header("Location: " . route($path));
    exit;
}

// ---------------------------
// Authentication Helpers
// ---------------------------

/**
 * Check if user is logged in
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user']) || !empty($_SESSION['user_id']);
}

/**
 * Get current user info or null
 */
function current_user(): ?array {
    // Check for new session format first
    if (!empty($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    
    // Check for legacy session format
    if (!empty($_SESSION['user_id'])) {
        // Return cached user or fetch from database
        if (!isset($_SESSION['user_data'])) {
            try {
                require_once __DIR__ . '/models/Database.php';
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $_SESSION['user_data'] = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Failed to fetch user data: " . $e->getMessage());
                return null;
            }
        }
        return $_SESSION['user_data'];
    }
    
    return null;
}

/**
 * Require user to be logged in
 */
function require_login(): void {
    if (!is_logged_in()) {
        redirect('auth/select');
    }
}

/**
 * Require user to have one of the given roles
 */
function require_role($roles): void {
    require_login();
    
    $user = current_user();
    if (!$user) {
        redirect('auth/select');
    }
    
    $userRoles = is_array($roles) ? $roles : [$roles];
    
    if (!in_array($user['role'], $userRoles)) {
        http_response_code(403);
        echo "<div style='text-align: center; padding: 50px;'>";
        echo "<h2>🚫 Access Denied</h2>";
        echo "<p>You don't have permission to access this page.</p>";
        echo "<a href='" . route('home') . "'>Go Home</a>";
        echo "</div>";
        exit;
    }
}

/**
 * Check if current user has one of the given roles
 */
function has_role(array $roles): bool {
    $user = current_user();
    if (!$user) return false;
    return in_array($user['role'], $roles);
}

/**
 * Check if current user has specific role
 */
function is_role(string $role): bool {
    $user = current_user();
    if (!$user) return false;
    return $user['role'] === $role;
}

// ---------------------------
// CSRF Protection Functions
// ---------------------------

/**
 * Generate CSRF token for forms
 */
function csrf_token(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF token as hidden form field
 */
function csrf_field(): void {
    $token = csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf_token(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    }
    
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------
// HTML and Output Helpers
// ---------------------------

/**
 * Safely output HTML (escape special characters)
 */
function e(?string $string): string {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Truncate string with ellipsis
 */
function str_limit(?string $string, int $limit = 100, string $end = '...'): string {
    if (!$string || mb_strlen($string) <= $limit) {
        return $string ?? '';
    }
    
    return mb_substr($string, 0, $limit) . $end;
}

/**
 * Get status badge HTML
 */
function status_badge(?string $status): string {
    if (!$status) return '';
    
    $badges = [
        'draft' => '<span class="status draft">Draft</span>',
        'submitted' => '<span class="status submitted">Submitted</span>',
        'under_review' => '<span class="status under_review">Under Review</span>',
        'approved' => '<span class="status approved">Approved</span>',
        'rejected' => '<span class="status rejected">Rejected</span>',
        'published' => '<span class="status approved">Published</span>'
    ];
    
    return $badges[$status] ?? '<span class="status">' . ucfirst($status) . '</span>';
}

// ---------------------------
// Flash Message Helpers
// ---------------------------

/**
 * Set flash message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash(string $type): ?string {
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}

/**
 * Check if flash message exists
 */
function has_flash(string $type): bool {
    return isset($_SESSION['flash_' . $type]);
}

/**
 * Display flash messages HTML
 */
function flash_messages(): void {
    $types = ['success', 'error', 'warning', 'info'];
    foreach ($types as $type) {
        $message = get_flash($type);
        if ($message) {
            $alertClass = $type === 'error' ? 'danger' : $type;
            echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }
    }
}

// ---------------------------
// Date and Time Helpers
// ---------------------------

/**
 * Format date for display
 */
function format_date(?string $date, string $format = 'M j, Y'): string {
    if (!$date) return '';
    
    try {
        $dt = new DateTime($date);
        return $dt->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Get relative time (e.g., "2 hours ago")
 */
function time_ago(?string $date): string {
    if (!$date) return '';
    
    try {
        $dt = new DateTime($date);
        $now = new DateTime();
        $diff = $now->diff($dt);
        
        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        
        return 'Just now';
    } catch (Exception $e) {
        return $date ?? '';
    }
}

// ---------------------------
// File Helpers
// ---------------------------

/**
 * Format file size in human readable format
 */
function format_file_size(int $size): string {
    if ($size <= 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = 0;
    
    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }
    
    return round($size, 1) . ' ' . $units[$unitIndex];
}

/**
 * Get file icon emoji based on extension
 */
function file_icon(?string $filename): string {
    if (!$filename) return '📄';
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $icons = [
        'pdf' => '📄',
        'doc' => '📝',
        'docx' => '📝',
        'txt' => '📄',
        'rtf' => '📝'
    ];
    
    return $icons[$extension] ?? '📄';
}

/**
 * Check if string is safe for use as filename
 */
function is_safe_filename(string $filename): bool {
    return preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename) === 1;
}

// ---------------------------
// Validation Helpers
// ---------------------------

/**
 * Validate email address
 */
function is_valid_email(?string $email): bool {
    if (!$email) return false;
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Clean and validate input
 */
function clean_input(?string $input): string {
    if (!$input) return '';
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Check if value exists and is not empty
 */
function is_filled($value): bool {
    if (is_string($value)) {
        return !empty(trim($value));
    }
    return !empty($value);
}

/**
 * Validate phone number (Philippine format)
 */
function is_valid_phone(string $phone): bool {
    // Remove all non-numeric characters
    $cleanPhone = preg_replace('/\D/', '', $phone);
    
    // Check if it's a valid Philippine mobile number
    return preg_match('/^(09|639)\d{9}$/', $cleanPhone) === 1;
}

/**
 * Check password strength
 */
function is_strong_password(string $password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// ---------------------------
// Array Helpers
// ---------------------------

/**
 * Get array value with default
 */
function array_get(array $array, string $key, mixed $default = null): mixed {
    return $array[$key] ?? $default;
}

/**
 * Check if array has key and value is not empty
 */
function array_filled(array $array, string $key): bool {
    return isset($array[$key]) && is_filled($array[$key]);
}

// ---------------------------
// Request Helpers
// ---------------------------

/**
 * Get POST value with default
 */
function post(string $key, mixed $default = null): mixed {
    return $_POST[$key] ?? $default;
}

/**
 * Get GET value with default
 */
function get(string $key, mixed $default = null): mixed {
    return $_GET[$key] ?? $default;
}

/**
 * Check if request is POST
 */
function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 */
function is_get(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// ---------------------------
// Security Helpers
// ---------------------------

/**
 * Generate secure random string
 */
function generate_random_string(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Convert string to slug
 */
function str_slug(string $string): string {
    // Convert to lowercase and replace non-alphanumeric characters with hyphens
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $string));
    
    // Remove leading/trailing hyphens
    return trim($slug, '-');
}

// ---------------------------
// Debug Helper
// ---------------------------

/**
 * Debug dump and die
 */
function dd($var): void {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
    exit;
}

// ---------------------------
// Session Management
// ---------------------------

/**
 * Start session if not already started
 */
function ensure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Start session automatically when helpers are loaded
ensure_session();

?>