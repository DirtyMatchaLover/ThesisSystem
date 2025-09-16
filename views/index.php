<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Autoload models & controllers
spl_autoload_register(function ($class) {
    $paths = ['models', 'controllers'];
    foreach ($paths as $p) {
        $file = __DIR__ . "/$p/$class.php";
        if (file_exists($file)) { 
            require_once $file; 
            return; 
        }
    }
});
require_once __DIR__ . '/helpers.php';

$route = $_GET['route'] ?? 'home';

switch ($route) {
    // ---------- Auth ----------
    case 'auth/select':   
        require __DIR__ . '/views/auth/role_select.php';
        break;

    case 'auth/login':    
        (new AuthController())->login();
        break;

    case 'auth/register':
        (new AuthController())->register();
        break;

    case 'auth/logout':
        (new AuthController())->logout();
        break;

    // ---------- Thesis ----------
    case 'thesis/create':
        require_login();
        (new ThesisController())->create();
        break;

    case 'thesis/list':
        require_login();
        (new ThesisController())->list();
        break;

    case 'thesis/show':
        (new ThesisController())->show();
        break;

    // ---------- Admin ----------
    case 'admin/dashboard':
        require_role(['faculty','admin','librarian']); 
        (new AdminController())->dashboard();
        break;

    case 'admin/approve':
        require_role(['faculty','admin','librarian']);
        (new AdminController())->approve($_GET['id'] ?? 0);
        break;

    case 'admin/reject':
        require_role(['faculty','admin','librarian']);
        (new AdminController())->reject($_GET['id'] ?? 0);
        break;

    case 'admin/delete':
        require_role(['admin']); // Only admins can delete
        (new AdminController())->delete($_GET['id'] ?? 0);
        break;

    case 'admin/users':
        require_role(['admin']);
        (new AdminController())->users();
        break;

    case 'admin/reports':
        require_role(['faculty','admin','librarian']);
        (new AdminController())->reports();
        break;

    case 'admin/export':
        require_role(['faculty','admin','librarian']);
        (new AdminController())->export();
        break;

    case 'admin/bulk':
        require_role(['faculty','admin','librarian']);
        (new AdminController())->bulkAction();
        break;

    // ---------- API Routes ----------
    case 'api/search':
        (new HomeController())->search();
        break;

    // ---------- Research ----------
    case 'research':
        (new ResearchController())->index();
        break;

    case 'research/show':
        (new ResearchController())->show();
        break;

    case 'research/download':
        (new ResearchController())->download();
        break;

    // ---------- Home ----------
    case 'home':
    default:
        (new HomeController())->index();
        break;
}