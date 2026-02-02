<?php
// Load environment to check APP_ENV
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, '"\'');
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
        }
    }
}

// Set error reporting based on environment
$isProduction = ($_ENV['APP_ENV'] ?? 'production') === 'production';

if ($isProduction) {
    // Production: Log errors, don't display them
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php_errors.log');

    // Force HTTPS in production
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
} else {
    // Development: Display all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Configure secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// Enable secure flag for HTTPS connections
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Set session lifetime from .env or default to 2 hours
$sessionLifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 7200);
ini_set('session.gc_maxlifetime', $sessionLifetime);
ini_set('session.cookie_lifetime', $sessionLifetime);

// Start session with secure settings
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Regenerate session ID periodically to prevent fixation attacks
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Check for session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionLifetime)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Include helpers first
if (file_exists(__DIR__ . '/helpers.php')) {
    require_once __DIR__ . '/helpers.php';
} else {
    die("Missing helpers.php file");
}

// Autoload function with error handling
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/controllers/' . $class . '.php'
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // If we get here, the class wasn't found
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
    echo "<strong>Error:</strong> Class '$class' not found.<br>";
    echo "Looked in: " . implode(', ', $paths) . "<br>";
    echo "Available files: " . implode(', ', glob(__DIR__ . '/{models,controllers}/*.php', GLOB_BRACE));
    echo "</div>";
    return false;
});

$route = $_GET['route'] ?? 'home';

// Simple error handling for routes
try {
    switch ($route) {
        // ---------- Auth ----------
        case 'auth/select':   
            require __DIR__ . '/views/auth/role_select.php';
            break;

        case 'auth/login':
            if (class_exists('AuthController')) {
                (new AuthController())->login();
            } else {
                echo "AuthController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'auth/logout':
            session_destroy();
            redirect('home');
            break;

        case 'auth/forgot-password':
            if (class_exists('AuthController')) {
                (new AuthController())->forgotPassword();
            } else {
                echo "AuthController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'auth/reset-password':
            if (class_exists('AuthController')) {
                (new AuthController())->resetPassword();
            } else {
                echo "AuthController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- Thesis ----------
        case 'thesis/create':
            require_login();
            if (class_exists('ThesisController')) {
                (new ThesisController())->create();
            } else {
                echo "ThesisController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'thesis/list':
            require_login();
            if (class_exists('ThesisController')) {
                (new ThesisController())->list();
            } else {
                echo "ThesisController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'thesis/show':
            if (class_exists('ThesisController')) {
                (new ThesisController())->show();
            } else {
                echo "ThesisController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- Admin ----------
        case 'admin/dashboard':
            require_role(['faculty','admin','librarian']); 
            if (class_exists('AdminController')) {
                (new AdminController())->dashboard();
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/approve':
            require_login();
            require_role(['faculty', 'admin']);
            $id = (int)($_GET['id'] ?? 0);
            (new AdminController())->approve($id);
            break;

        case 'admin/reject':
            require_login();
            require_role(['faculty', 'admin']);
            $id = (int)($_GET['id'] ?? 0);
            (new AdminController())->reject($id);
            break;

        // PDF Viewing Routes (No JavaScript)
        case 'admin/pdfview':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('AdminController')) {
                (new AdminController())->pdfView($id);
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/viewpdf':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('AdminController')) {
                (new AdminController())->viewPdf($id);
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/comments':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('AdminController')) {
                (new AdminController())->comments($id);
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/view':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('AdminController')) {
                (new AdminController())->viewThesis($id);
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/download':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('AdminController')) {
                (new AdminController())->downloadThesis($id);
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/delete':
            require_login();
            require_role(['admin', 'faculty']); // Admins and faculty can delete (with restrictions)
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('AdminController')) {
                (new AdminController())->delete($id);
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/users':
            require_login();
            require_role(['admin']);
            if (class_exists('AdminController')) {
                (new AdminController())->users();
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/reports':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            if (class_exists('AdminController')) {
                (new AdminController())->reports();
            } else {
                echo "AdminController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- Analytics Routes ----------
        case 'admin/analytics':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            if (class_exists('AnalyticsController')) {
                (new AnalyticsController())->dashboard();
            } else {
                echo "AnalyticsController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/analytics/research':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            if (class_exists('AnalyticsController')) {
                $_GET['type'] = 'research'; // Set type for research dashboard
                (new AnalyticsController())->dashboard();
            } else {
                echo "AnalyticsController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/analytics/export':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            if (class_exists('AnalyticsController')) {
                (new AnalyticsController())->export();
            } else {
                echo "AnalyticsController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/analytics/export-research':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            if (class_exists('AnalyticsController')) {
                (new AnalyticsController())->exportResearchData();
            } else {
                echo "AnalyticsController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/analytics/pdf':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            if (class_exists('AnalyticsController')) {
                (new AnalyticsController())->generatePDFReport();
            } else {
                echo "AnalyticsController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- User Activity Reports (Individual & Combined) ----------
        case 'admin/individual-report':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            require_once __DIR__ . '/controllers/UserActivityController.php';
            if (class_exists('UserActivityController')) {
                (new UserActivityController())->individualReport();
            } else {
                echo "UserActivityController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/combined-report':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            require_once __DIR__ . '/controllers/UserActivityController.php';
            if (class_exists('UserActivityController')) {
                (new UserActivityController())->combinedReport();
            } else {
                echo "UserActivityController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/export-individual-csv':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            require_once __DIR__ . '/controllers/UserActivityController.php';
            if (class_exists('UserActivityController')) {
                (new UserActivityController())->exportIndividualCSV();
            } else {
                echo "UserActivityController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'admin/export-combined-csv':
            require_login();
            require_role(['faculty', 'admin', 'librarian']);
            require_once __DIR__ . '/controllers/UserActivityController.php';
            if (class_exists('UserActivityController')) {
                (new UserActivityController())->exportCombinedCSV();
            } else {
                echo "UserActivityController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- Bookmarks/Favorites ----------
        case 'bookmarks':
        case 'favorites':
            require_login();
            if (class_exists('BookmarkController')) {
                (new BookmarkController())->index();
            } else {
                echo "BookmarkController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'bookmark/toggle':
            require_login();
            if (class_exists('BookmarkController')) {
                (new BookmarkController())->toggle();
            } else {
                echo "BookmarkController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- Profile ----------
        case 'profile':
            require_login();
            if (class_exists('ProfileController')) {
                (new ProfileController())->index();
            } else {
                echo "<div style='color: red; padding: 20px; text-align: center;'>";
                echo "<h2>ProfileController not found</h2>";
                echo "<p>The ProfileController.php file is missing from the controllers folder.</p>";
                echo "<a href='?route=home' style='color: blue;'>Go Home</a>";
                echo "</div>";
            }
            break;

        case 'profile/statistics':
            require_login();
            if (class_exists('ProfileController')) {
                (new ProfileController())->statistics();
            } else {
                echo "ProfileController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'profile/update':
            require_login();
            if (class_exists('ProfileController')) {
                (new ProfileController())->update();
            } else {
                echo "ProfileController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- Research ----------
        case 'research':
            if (class_exists('ResearchController')) {
                (new ResearchController())->index();
            } else {
                echo "ResearchController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'research/download':
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('ResearchController')) {
                (new ResearchController())->download();
            } else {
                echo "ResearchController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'research/show':
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('ResearchController')) {
                (new ResearchController())->show();
            } else {
                echo "ResearchController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        case 'research/viewer':
            $id = (int)($_GET['id'] ?? 0);
            if (class_exists('ResearchController')) {
                (new ResearchController())->pdfViewer();
            } else {
                echo "ResearchController not found. <a href='?route=home'>Go Home</a>";
            }
            break;

        // ---------- About ----------
        case 'about':
            require __DIR__ . '/views/about.php';
            break;

        // ---------- API Routes ----------
        case 'api/search':
            header('Content-Type: application/json');
            if (class_exists('HomeController')) {
                (new HomeController())->search();
            } else {
                echo json_encode(['results' => []]);
            }
            break;

        case 'api/research/search':
            header('Content-Type: application/json');
            if (class_exists('ResearchController')) {
                (new ResearchController())->instantSearch();
            } else {
                echo json_encode(['results' => [], 'count' => 0]);
            }
            break;

        // ---------- Home ----------
        case 'home':
        default:
            if (class_exists('HomeController')) {
                (new HomeController())->index();
            } else {
                echo "HomeController not found. <a href='?route=home'>Go Home</a>";
            }
            break;
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
    echo "<h2>Error</h2>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
    echo "<br><a href='?route=home'>Go Home</a>";
    echo "</div>";
}
?>