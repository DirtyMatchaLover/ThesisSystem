<?php
// ---------------------------
// Base Configuration (FIXED for Docker)
// ---------------------------
if (!defined('BASE_PATH')) {
    define('BASE_PATH', ''); // ✅ Empty for Docker
}

// ---------------------------
// URL and Route Helpers (FIXED)
// ---------------------------

/**
 * Build a URL to an asset (CSS, JS, images, uploads)
 */
function asset(string $path): string {
    return '/' . ltrim($path, '/'); // ✅ Direct absolute path for Docker
}

/**
 * Build a route URL (ex: route('auth/login'))
 */
function route(string $path = ''): string {
    if (empty($path)) {
        return '/index.php';
    }
    return '/index.php?route=' . ltrim($path, '/'); // ✅ Direct absolute path for Docker
}

// ✅ Redirect helper
function redirect(string $path): void {
    header("Location: " . route($path));
    exit;
}

// Create URL helper (alias for route for compatibility)
function url(string $path = '', array $params = []): string {
    $url = route($path);
    if (!empty($params)) {
        $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($params);
    }
    return $url;
}

// ---------------------------
// Auth Helpers
// ---------------------------

// Require user to be logged in
function require_login(): void {
    if (empty($_SESSION['user'])) {
        redirect('auth/select');
    }
}

// Require user to have one of the given roles
function require_role(array $roles): void {
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles)) {
        http_response_code(403);
        echo "<h2>🚫 Access denied.</h2>";
        exit;
    }
}

// Get current user info or null
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

// Check if user is logged in
function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

// Check if current user has one of the given roles
function has_role(array $roles): bool {
    $user = current_user();
    if (!$user) return false;
    return in_array($user['role'], $roles);
}

// Check if current user has specific role
function is_role(string $role): bool {
    $user = current_user();
    if (!$user) return false;
    return $user['role'] === $role;
}

// ---------------------------
// CSRF Protection Functions
// ---------------------------

// Generate CSRF token for forms
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // ✅ Check session status first
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Output CSRF token as hidden form field
function csrf_field(): void {
    $token = csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

// Verify CSRF token
function verify_csrf_token(?string $token = null): bool {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------
// String Helper Functions (ADDED)
// ---------------------------

// Truncate string with ellipsis
function str_limit(?string $string, int $limit = 100, string $end = '...'): string {
    if (!$string || mb_strlen($string) <= $limit) {
        return $string ?? '';
    }
    
    return mb_substr($string, 0, $limit) . $end;
}

// Truncate by words instead of characters
function str_words(?string $string, int $words = 100, string $end = '...'): string {
    if (!$string) return '';
    
    $wordsArray = explode(' ', $string);
    if (count($wordsArray) <= $words) {
        return $string;
    }
    
    return implode(' ', array_slice($wordsArray, 0, $words)) . $end;
}

// Convert string to slug
function str_slug(string $string): string {
    // Convert to lowercase and replace non-alphanumeric characters with hyphens
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $string));
    
    // Remove leading/trailing hyphens
    return trim($slug, '-');
}

// ---------------------------
// Utility Functions
// ---------------------------

// HTML escape helper
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Format file size
function format_file_size(int $bytes): string {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Format date nicely
function format_date(?string $date): string {
    if (!$date) return '';
    try {
        return date('F j, Y', strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

// Format datetime nicely
function format_datetime(?string $datetime): string {
    if (!$datetime) return '';
    try {
        return date('F j, Y g:i A', strtotime($datetime));
    } catch (Exception $e) {
        return $datetime;
    }
}

// Get file icon based on extension
function file_icon(?string $filename): string {
    if (empty($filename)) return '📄';
    
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    return match($extension) {
        'pdf' => '📄',
        'doc', 'docx' => '📝',
        'xls', 'xlsx' => '📊',
        'ppt', 'pptx' => '📽️',
        'jpg', 'jpeg', 'png', 'gif' => '🖼️',
        'zip', 'rar', '7z' => '📦',
        'mp4', 'avi', 'mkv' => '🎥',
        'mp3', 'wav', 'flac' => '🎵',
        default => '📄'
    };
}

// Check if value is filled (not null, empty string, or just whitespace)
function is_filled(mixed $value): bool {
    if ($value === null) return false;
    if (is_string($value)) return trim($value) !== '';
    if (is_array($value)) return !empty($value);
    return !empty($value);
}

// Get array value with default
function array_get(array $array, string $key, mixed $default = null): mixed {
    return array_key_exists($key, $array) && is_filled($array[$key]) ? $array[$key] : $default;
}

// Check if array has key and value is not empty
function array_filled(array $array, string $key): bool {
    return isset($array[$key]) && is_filled($array[$key]);
}

// ---------------------------
// Request Helpers
// ---------------------------

// Get POST value with default
function post(string $key, mixed $default = null): mixed {
    return $_POST[$key] ?? $default;
}

// Get GET value with default
function get(string $key, mixed $default = null): mixed {
    return $_GET[$key] ?? $default;
}

// Check if request is POST
function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Check if request is GET
function is_get(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// ---------------------------
// Security Helpers
// ---------------------------

// Generate secure random string
function generate_random_string(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

// Check if string contains only allowed characters
function is_safe_filename(string $filename): bool {
    return preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename) === 1;
}

// ---------------------------
// Debugging Helper
// ---------------------------
function dd($var): void {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
    exit;
}