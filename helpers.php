<?php
/**
 * ========================================
 * RESEARCHHUB HELPER FUNCTIONS
 * ========================================
 *
 * This file contains reusable functions that help throughout the application.
 * Think of these as shortcuts - instead of writing the same code over and over,
 * we write it once here and use it everywhere!
 *
 * Categories:
 * 1. URL & Routing - For creating links
 * 2. Authentication - For checking who's logged in
 * 3. Security - For keeping the site safe
 * 4. Formatting - For making data look nice
 * 5. Flash Messages - For showing temporary alerts
 */

// ========================================
// 1. CONFIGURATION
// ========================================

// BASE_PATH is empty for Docker setup (default for most modern setups)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '');
}

// ========================================
// 2. URL & ROUTING HELPERS
// ========================================

/**
 * Create a URL to a file (CSS, JavaScript, images, PDFs, etc.)
 *
 * Example: asset('assets/css/style.css') returns '/assets/css/style.css'
 *
 * @param string $path The path to the file
 * @return string The full URL to the file
 */
function asset(string $path): string {
    return '/' . ltrim($path, '/');
}

/**
 * Create a link to a page in the application
 *
 * Example: route('auth/login') returns '/index.php?route=auth/login'
 *
 * @param string $path The route path (leave empty for homepage)
 * @return string The full URL
 */
function route(string $path = ''): string {
    // If no path given, return homepage
    if (empty($path)) {
        return '/index.php';
    }

    // Build the full route URL
    return '/index.php?route=' . ltrim($path, '/');
}

/**
 * Redirect the user to a different page
 *
 * Example: redirect('home') will send user to homepage
 * Note: This stops all code execution after redirecting
 *
 * @param string $path The route to redirect to
 * @return void
 */
function redirect(string $path): void {
    header("Location: " . route($path));
    exit; // Stop running any more code
}

/**
 * Create a URL with query parameters
 *
 * Example: url('search', ['q' => 'research'])
 * Returns: '/index.php?route=search&q=research'
 *
 * @param string $path The route path
 * @param array $params Additional parameters to add
 * @return string The complete URL
 */
function url(string $path = '', array $params = []): string {
    $url = route($path);

    // Add parameters if provided
    if (!empty($params)) {
        $separator = (strpos($url, '?') !== false) ? '&' : '?';
        $url .= $separator . http_build_query($params);
    }

    return $url;
}

// ========================================
// 3. AUTHENTICATION HELPERS
// ========================================

/**
 * Force user to be logged in (redirect to login if not)
 *
 * Use at the start of pages that require login
 *
 * @return void
 */
function require_login(): void {
    if (empty($_SESSION['user'])) {
        redirect('auth/select');
    }
}

/**
 * Force user to have a specific role (admin, faculty, student)
 *
 * Example: require_role(['admin', 'faculty']) - only admins and faculty can access
 *
 * @param array $roles List of allowed roles
 * @return void
 */
function require_role(array $roles): void {
    // Check if user is logged in and has the right role
    if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], $roles)) {
        http_response_code(403); // Forbidden
        echo "<h2>Access denied. You don't have permission to view this page.</h2>";
        exit;
    }
}

/**
 * Get information about the currently logged-in user
 *
 * Returns: array with user data (id, name, email, role) or null if not logged in
 *
 * @return array|null User data or null
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if someone is logged in
 *
 * Returns: true if logged in, false if not
 *
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

/**
 * Check if current user has one of the given roles
 *
 * Example: has_role(['admin', 'faculty'])
 *
 * @param array $roles Roles to check for
 * @return bool True if user has one of these roles
 */
function has_role(array $roles): bool {
    $user = current_user();
    if (!$user) return false;

    return in_array($user['role'], $roles);
}

/**
 * Check if current user has a specific role
 *
 * Example: is_role('admin')
 *
 * @param string $role The role to check
 * @return bool True if user has this role
 */
function is_role(string $role): bool {
    $user = current_user();
    if (!$user) return false;

    return $user['role'] === $role;
}

// ========================================
// 4. SECURITY - CSRF PROTECTION
// ========================================

/**
 * Generate a CSRF token for form security
 *
 * CSRF = Cross-Site Request Forgery
 * This prevents hackers from submitting forms on behalf of users
 *
 * @return string The security token
 */
function csrf_token(): string {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Create new token if one doesn't exist
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden form field with CSRF token
 *
 * Use this inside every form:
 * <form method="POST">
 *     <?php csrf_field(); ?>
 *     ...
 * </form>
 *
 * @return void
 */
function csrf_field(): void {
    $token = csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Check if the submitted CSRF token is valid
 *
 * Call this when processing forms to make sure they're legitimate
 *
 * @param string|null $token The token to verify (auto-detects from POST/GET if not provided)
 * @return bool True if token is valid
 */
function verify_csrf_token(?string $token = null): bool {
    // Auto-detect token if not provided
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    }

    // Check if both tokens exist and match
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Use hash_equals for timing attack prevention
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ========================================
// 5. STRING FORMATTING HELPERS
// ========================================

/**
 * Shorten text and add "..." if it's too long
 *
 * Example: str_limit("This is a long text", 10) returns "This is a..."
 *
 * @param string|null $string The text to limit
 * @param int $limit Maximum characters
 * @param string $end What to add at the end
 * @return string The shortened text
 */
function str_limit(?string $string, int $limit = 100, string $end = '...'): string {
    // Handle null or empty strings
    if (!$string || mb_strlen($string) <= $limit) {
        return $string ?? '';
    }

    // Cut and add ellipsis
    return mb_substr($string, 0, $limit) . $end;
}

/**
 * Limit text by number of words instead of characters
 *
 * Example: str_words("one two three four", 2) returns "one two..."
 *
 * @param string|null $string The text to limit
 * @param int $words Maximum number of words
 * @param string $end What to add at the end
 * @return string The limited text
 */
function str_words(?string $string, int $words = 100, string $end = '...'): string {
    if (!$string) return '';

    // Split into words
    $wordsArray = explode(' ', $string);

    // If already short enough, return as-is
    if (count($wordsArray) <= $words) {
        return $string;
    }

    // Take only the first X words and add ending
    return implode(' ', array_slice($wordsArray, 0, $words)) . $end;
}

/**
 * Convert text to URL-friendly format (slug)
 *
 * Example: str_slug("Hello World!") returns "hello-world"
 *
 * @param string $string The text to convert
 * @return string The URL-friendly version
 */
function str_slug(string $string): string {
    // Convert to lowercase and replace special characters with dashes
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $string));

    // Remove dashes from start and end
    return trim($slug, '-');
}

// ========================================
// 6. HTML & OUTPUT HELPERS
// ========================================

/**
 * Escape HTML to prevent XSS attacks
 *
 * ALWAYS use this when displaying user input!
 * Example: echo e($userInput);
 *
 * @param string|null $string The text to escape
 * @return string Safe HTML
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Convert file size in bytes to human-readable format
 *
 * Example: format_file_size(1536) returns "1.50 KB"
 *
 * @param int $bytes File size in bytes
 * @return string Formatted size
 */
function format_file_size(int $bytes): string {
    if ($bytes >= 1073741824) {
        // Gigabytes
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        // Megabytes
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        // Kilobytes
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        // Bytes
        return $bytes . ' bytes';
    }
}

/**
 * Format a date nicely
 *
 * Example: format_date("2024-01-15") returns "January 15, 2024"
 *
 * @param string|null $date The date to format
 * @return string Formatted date
 */
function format_date(?string $date): string {
    if (!$date) return '';

    try {
        return date('F j, Y', strtotime($date));
    } catch (Exception $e) {
        return $date; // Return original if formatting fails
    }
}

/**
 * Format a date with time
 *
 * Example: format_datetime("2024-01-15 14:30:00") returns "January 15, 2024 2:30 PM"
 *
 * @param string|null $datetime The datetime to format
 * @return string Formatted datetime
 */
function format_datetime(?string $datetime): string {
    if (!$datetime) return '';

    try {
        return date('F j, Y g:i A', strtotime($datetime));
    } catch (Exception $e) {
        return $datetime; // Return original if formatting fails
    }
}

/**
 * Get an icon emoji for a file type
 *
 * Example: file_icon("document.pdf") returns "📄"
 *
 * @param string|null $filename The filename
 * @return string Icon emoji
 */
function file_icon(?string $filename): string {
    if (empty($filename)) return '📄';

    // Get file extension
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Return appropriate icon
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

// ========================================
// 7. DATA VALIDATION & CHECKING
// ========================================

/**
 * Check if a value is filled (not empty)
 *
 * More thorough than empty() - also checks for whitespace-only strings
 *
 * @param mixed $value The value to check
 * @return bool True if filled
 */
function is_filled(mixed $value): bool {
    if ($value === null) return false;
    if (is_string($value)) return trim($value) !== '';
    if (is_array($value)) return !empty($value);
    return !empty($value);
}

/**
 * Get a value from an array with a default fallback
 *
 * Example: array_get($data, 'name', 'Unknown')
 * Returns $data['name'] if it exists, otherwise 'Unknown'
 *
 * @param array $array The array to check
 * @param string $key The key to look for
 * @param mixed $default Default value if key doesn't exist
 * @return mixed The value or default
 */
function array_get(array $array, string $key, mixed $default = null): mixed {
    return array_key_exists($key, $array) && is_filled($array[$key]) ? $array[$key] : $default;
}

/**
 * Check if array has a key with a filled value
 *
 * @param array $array The array to check
 * @param string $key The key to look for
 * @return bool True if key exists and has value
 */
function array_filled(array $array, string $key): bool {
    return isset($array[$key]) && is_filled($array[$key]);
}

// ========================================
// 8. REQUEST HELPERS
// ========================================

/**
 * Get a POST value with optional default
 *
 * Example: post('username', 'guest')
 *
 * @param string $key The POST field name
 * @param mixed $default Default value if not set
 * @return mixed The POST value or default
 */
function post(string $key, mixed $default = null): mixed {
    return $_POST[$key] ?? $default;
}

/**
 * Get a GET parameter with optional default
 *
 * Example: get('page', 1)
 *
 * @param string $key The GET parameter name
 * @param mixed $default Default value if not set
 * @return mixed The GET value or default
 */
function get(string $key, mixed $default = null): mixed {
    return $_GET[$key] ?? $default;
}

/**
 * Check if current request is POST
 *
 * @return bool True if POST request
 */
function is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if current request is GET
 *
 * @return bool True if GET request
 */
function is_get(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// ========================================
// 9. SECURITY UTILITIES
// ========================================

/**
 * Generate a secure random string
 *
 * Useful for tokens, temporary passwords, etc.
 *
 * @param int $length Length of string (must be even)
 * @return string Random string
 */
function generate_random_string(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Check if filename contains only safe characters
 *
 * Prevents directory traversal attacks (../../../etc/passwd)
 *
 * @param string $filename The filename to check
 * @return bool True if safe
 */
function is_safe_filename(string $filename): bool {
    return preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename) === 1;
}

// ========================================
// 10. DEBUGGING HELPER
// ========================================

/**
 * Dump and Die - Display variable contents and stop execution
 *
 * Useful for debugging! Example: dd($user);
 *
 * @param mixed $var The variable to display
 * @return void
 */
function dd($var): void {
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
    exit;
}

// ========================================
// 11. FLASH MESSAGES
// ========================================

/**
 * Set a flash message (temporary message shown once)
 *
 * Example: set_flash('success', 'Profile updated!')
 *
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message The message text
 * @return void
 */
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }

    $_SESSION['flash'][$type] = $message;
}

/**
 * Check if a flash message exists
 *
 * @param string $type The message type to check
 * @return bool True if message exists
 */
function has_flash($type) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['flash'][$type]);
}

/**
 * Get and remove a flash message
 *
 * The message will be deleted after being retrieved (shown only once)
 *
 * @param string $type The message type
 * @return string|null The message or null
 */
function get_flash($type) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]); // Delete after retrieving
        return $message;
    }

    return null;
}

/**
 * Clear all flash messages
 *
 * @return void
 */
function clear_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [];
}

// ========================================
// 12. CITATION GENERATOR
// ========================================

/**
 * Generate APA-style citation for a thesis
 *
 * @param array $thesis Thesis data array
 * @return string Formatted citation
 */
function generate_apa_citation(array $thesis): string {
    // Extract thesis information with fallback defaults
    $author = $thesis['author'] ?? 'Unknown Author';
    $year = date('Y', strtotime($thesis['created_at'] ?? 'now'));
    $title = $thesis['title'] ?? 'Untitled';
    $institution = $thesis['institution'] ?? 'Academic Institution';

    // Return formatted APA citation
    return "{$author}. ({$year}). <em>{$title}</em> [Undergraduate thesis, {$institution}]. ResearchHub.";
}

// ========================================
// 13. BOOKMARK HELPERS
// ========================================

/**
 * Check if a thesis is bookmarked by current user
 *
 * @param int $thesisId The thesis ID
 * @return bool True if bookmarked
 */
function is_bookmarked($thesisId): bool {
    if (!is_logged_in()) {
        return false;
    }

    $user = current_user();

    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM thesis_bookmarks
            WHERE user_id = ? AND thesis_id = ?
        ");
        $stmt->execute([$user['id'], $thesisId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;

    } catch (Exception $e) {
        error_log("Bookmark check error: " . $e->getMessage());
        return false;
    }
}

// ========================================
// 14. RECENTLY VIEWED
// ========================================

/**
 * Add a thesis to the recently viewed list
 *
 * Keeps track of last 10 theses viewed by user
 *
 * @param int $thesisId The thesis ID
 * @return void
 */
function add_to_recently_viewed($thesisId): void {
    if (!isset($_SESSION['recently_viewed'])) {
        $_SESSION['recently_viewed'] = [];
    }

    // Remove if already exists (to move it to front)
    $key = array_search($thesisId, $_SESSION['recently_viewed']);
    if ($key !== false) {
        unset($_SESSION['recently_viewed'][$key]);
    }

    // Add to beginning of list
    array_unshift($_SESSION['recently_viewed'], $thesisId);

    // Keep only last 10
    $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);
}

/**
 * Get recently viewed theses
 *
 * @param int $limit Maximum number to return
 * @return array List of thesis data
 */
function get_recently_viewed($limit = 5): array {
    if (empty($_SESSION['recently_viewed'])) {
        return [];
    }

    try {
        $db = Database::getInstance();
        $ids = array_slice($_SESSION['recently_viewed'], 0, $limit);

        if (empty($ids)) {
            return [];
        }

        // Create placeholders for SQL query
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Fetch theses
        $stmt = $db->prepare("
            SELECT t.*, u.name as author
            FROM theses t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.id IN ($placeholders) AND t.status = 'approved'
        ");
        $stmt->execute($ids);
        $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sort by order in recently_viewed array
        $sorted = [];
        foreach ($ids as $id) {
            foreach ($theses as $thesis) {
                if ($thesis['id'] == $id) {
                    $sorted[] = $thesis;
                    break;
                }
            }
        }

        return $sorted;

    } catch (Exception $e) {
        error_log("Recently viewed error: " . $e->getMessage());
        return [];
    }
}
