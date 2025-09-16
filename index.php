<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

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

        // 🔧 NEW PDF VIEWING ROUTES (No JavaScript)
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

        // Keep existing admin routes
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
            require_role(['admin']); // Only admins can delete
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

        // ---------- Research ----------
        case 'research':
            if (class_exists('ResearchController')) {
                (new ResearchController())->index();
            } else {
                // Fallback to simple view
                require __DIR__ . '/views/research_simple.php';
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

        // ---------- Home ----------
        case 'home':
        default:
            if (class_exists('HomeController')) {
                (new HomeController())->index();
            } else {
                // Fallback to simple home
                require __DIR__ . '/views/home_simple.php';
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