# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**ResearchHub** is a web-based thesis management and publication system. It manages the complete lifecycle of academic research papers, from submission through review to publication.

**Tech Stack**: PHP 8.2, MySQL 8.0, Apache, Docker

**Team**: Keon Bastien B Blanco (Lead), Rayn F. Alba, Ramon Miguel S. Marquez, Sean Nathan Tyler M. Torres

## Development Environment Setup

### Docker (Recommended)

```bash
# Start all services (PHP + MySQL)
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down

# Access application
# URL: http://localhost:8000
```

The database initializes automatically from `sql/init.sql` on first run.

### XAMPP (Windows Alternative)

1. Place project in `C:\xampp\htdocs\thesis-management-system`
2. Start Apache and MySQL from XAMPP Control Panel
3. Import `sql/init.sql` via phpMyAdmin
4. Update `.env` with local credentials (DB_HOST=localhost)
5. Access at `http://localhost/thesis-management-system`

### Database Setup

```bash
# Initialize database (Docker)
docker-compose exec db mysql -u thesis_user -psecure_password_123 thesis_db < sql/init.sql

# Test accounts (password: "password")
# - Admin: admin@pcc.edu.ph
# - Faculty: teacher@pcc.edu.ph
# - Librarian: librarian@pcc.edu.ph
# - Students: student1@pcc.edu.ph, student2@pcc.edu.ph, student3@pcc.edu.ph
```

## Architecture

### MVC Pattern with Manual Routing

The application uses a **manual MVC pattern** without a framework:

- **Entry Point**: `index.php` - All requests route through this file
- **Router**: Switch statement in index.php based on `?route=` parameter
- **Controllers**: `controllers/*Controller.php` - Handle business logic
- **Models**: `models/*.php` - Database interactions using PDO
- **Views**: `views/**/*.php` - Pure PHP templates, no JavaScript framework
- **Autoloader**: SPL autoloader loads controllers and models automatically

**Routing Example**:
- URL: `index.php?route=admin/dashboard`
- Maps to: `AdminController->dashboard()`
- View: `views/admin/dashboard.php`

### Key Files

| File/Directory | Purpose |
|---------------|---------|
| `index.php` | Application entry point, routing logic, request handling |
| `helpers.php` | Global helper functions (routing, auth, CSRF, formatting) |
| `models/Database.php` | Singleton PDO connection with .env loading |
| `models/Thesis.php` | Thesis CRUD operations, search, statistics |
| `models/User.php` | User management, authentication |
| `controllers/AdminController.php` | Admin dashboard, thesis approval workflow |
| `controllers/ThesisController.php` | Thesis submission, viewing |
| `controllers/AuthController.php` | Login, registration, role selection |
| `sql/init.sql` | Complete database schema with sample data |

### Database Design

**Core Tables**:
- `users` - User accounts with roles (student, faculty, admin, librarian)
- `theses` - Research papers with workflow status and metadata
- `thesis_comments` - Feedback and review comments
- `thesis_categories` - Subject area classifications
- `thesis_keywords` - Search optimization tags
- `categories`, `keywords` - Taxonomy tables

**Thesis Status Workflow**:
```
draft → submitted → under_review → revision_required
                              ↓
                         approved (published) OR rejected
```

**Key Columns in `theses` table**:
- `status` - Workflow state (ENUM)
- `is_public` - Visibility flag (1 = public, 0 = private)
- `academic_year` - Format: "2024-2025"
- `strand` - SHS tracks: STEM, ABM, HUMSS, etc.
- `view_count`, `download_count` - Analytics

### Authentication & Authorization

**Session-based authentication** implemented via helper functions in `helpers.php`:

```php
require_login();              // Redirect if not logged in
require_role(['admin']);      // Require specific role(s)
is_logged_in();               // Check login status
current_user();               // Get current user array
has_role(['faculty', 'admin']); // Check role membership
```

**User Roles**:
- `student` - Can submit theses, view own submissions
- `faculty` - Can review and approve theses
- `admin` - Full system access, user management
- `librarian` - View and manage published theses

### Helper Functions

`helpers.php` provides essential utilities:

**Routing**:
```php
route('admin/dashboard')     // Generate route URL
asset('css/style.css')       // Generate asset path
redirect('home')             // Redirect to route
url('route', ['id' => 5])    // URL with query params
```

**CSRF Protection**:
```php
csrf_token()                 // Generate token
csrf_field()                 // Output hidden input
verify_csrf_token()          // Validate token
```

**Formatting**:
```php
e($string)                   // HTML escape
format_date($date)           // "January 1, 2025"
format_file_size($bytes)     // "2.5 MB"
str_limit($text, 100)        // Truncate string
```

**Flash Messages**:
```php
set_flash('success', 'Saved!')
get_flash('error')
has_flash('warning')
```

## Development Patterns

### Database Schema Evolution

The system uses **runtime column detection** instead of traditional migrations:

```php
// In Thesis.php
private function columnExists($columnName) {
    $stmt = $this->db->prepare("SHOW COLUMNS FROM theses LIKE ?");
    $stmt->execute([$columnName]);
    return $stmt->rowCount() > 0;
}
```

When adding new columns, wrap queries conditionally:
```php
if ($this->columnExists('new_column')) {
    // Use new column
} else {
    // Fallback behavior
}
```

Use `ensureRequiredColumns()` method to add columns at runtime if missing.

### File Upload Handling

**Location**: `uploads/theses/`
**Allowed Types**: PDF only (configured in `.env`)
**Max Size**: 10MB (configurable via UPLOAD_MAX_SIZE)

**Upload Pattern**:
1. Validate file type and size
2. Generate unique filename: `{timestamp}_{user_id}_{sanitized_original}.pdf`
3. Move to uploads directory
4. Store path in database: `uploads/theses/{filename}`

### Activity Logging

AdminController includes optional activity logging:

```php
private function logActivity($userId, $action, $thesisId = null)
```

**Actions tracked**:
- `thesis_approved`, `thesis_rejected`, `thesis_deleted`
- `thesis_viewed`, `thesis_downloaded`
- `thesis_review_requested`

Falls back gracefully if logging tables don't exist.

### Error Handling

**Development Mode** (APP_ENV=development):
- Display full error messages
- Show database connection errors
- PHP error_reporting(E_ALL) enabled

**Production Mode**:
- Generic error messages
- Errors logged to `error_log()`
- Redirect to error pages

## Common Development Tasks

### Adding a New Route

1. Add route case in `index.php`:
```php
case 'your/route':
    require_login(); // Optional
    (new YourController())->yourMethod();
    break;
```

2. Create controller method in `controllers/YourController.php`
3. Create view in `views/your/view.php`

### Creating a New Controller

```php
<?php
require_once __DIR__ . '/../models/YourModel.php';

class YourController {
    private $model;

    public function __construct() {
        $this->model = new YourModel();
    }

    public function index() {
        $data = $this->model->getData();
        require __DIR__ . '/../views/your/index.php';
    }
}
```

### Creating a New Model

```php
<?php
require_once __DIR__ . '/Database.php';

class YourModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
```

### Adding Database Columns

**Option 1**: Add to `sql/init.sql` for fresh installs

**Option 2**: Runtime addition (recommended for existing deployments):
```php
public function ensureNewColumn() {
    if (!$this->columnExists('new_column')) {
        $this->db->exec("ALTER TABLE theses ADD COLUMN new_column VARCHAR(255)");
    }
}
```

### Working with Comments System

```php
// In AdminController
private function saveComment($thesis_id, $user_id, $type, $comment)
private function getComments($thesis_id)

// Comment types: 'review', 'feedback', 'revision_request', 'approval', 'rejection'
```

## Important Technical Notes

### URL Structure

All URLs use query parameters (no mod_rewrite):
- Homepage: `index.php` or `index.php?route=home`
- Admin Dashboard: `index.php?route=admin/dashboard`
- View Thesis: `index.php?route=thesis/show&id=5`

### Path Handling

- **Assets**: Use `asset()` helper - generates `/path/to/asset`
- **Routes**: Use `route()` helper - generates `/index.php?route=...`
- **File System**: Use `__DIR__` for absolute paths
- **BASE_PATH**: Empty string for Docker, set for subdirectory installs

### Session Management

Sessions start in `index.php` before any output.

**Session Variables**:
```php
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'role' => 'student'
]
```

### AJAX Responses

Controllers detect AJAX via `$_SERVER['HTTP_X_REQUESTED_WITH']`:
```php
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}
```

### PDF Handling

Two routes for PDF access:
1. `admin/pdfview?id=X` - Shows PDF viewer page with thesis info
2. `admin/viewpdf?id=X` - Serves raw PDF for iframe embedding

Headers for inline viewing:
```php
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="..."');
```

### Search Implementation

`Thesis->search($query, $filters)` supports:
- **Query**: Searches title, abstract, author name
- **Filters**: status, strand, academic_year, date_from, date_to

Uses LIKE queries with `%term%` pattern matching.

## Database Views

Pre-built views in `sql/init.sql`:
- `published_theses_view` - Public theses with author and category info
- `strand_statistics_view` - Aggregated stats by academic strand
- `monthly_submission_trends` - Time-series submission data

Query these views directly for reporting features.

## Testing Utilities

Debug scripts in root directory (remove in production):
- `debug_home.php` - Test homepage rendering
- `debug_thesis_visibility.php` - Check thesis visibility logic
- `complete-test.php` - Full system integration test
- `sample_data_generator.php` - Generate test theses
- `remove-sample-data.php` - Clean test data
- `reset-users.php` - Reset user accounts

## Security Considerations

- **SQL Injection**: All queries use prepared statements with PDO
- **CSRF**: Use `csrf_field()` in all forms, validate with `verify_csrf_token()`
- **XSS**: Use `e()` helper or `htmlspecialchars()` for output
- **File Upload**: Whitelist PDF only, validate MIME type
- **Authentication**: Session-based, passwords hashed with bcrypt
- **Authorization**: Role checks on every protected route

## Analytics System

`AnalyticsController` provides:
- Thesis submission trends
- Approval rates by strand
- Download/view statistics
- CSV/PDF export capabilities

Routes:
- `admin/analytics` - Main dashboard
- `admin/analytics/research` - Research-specific analytics
- `admin/analytics/export` - CSV export
- `admin/analytics/pdf` - PDF report generation

## Deployment Notes

1. Update `.env` with production credentials
2. Set `APP_ENV=production`
3. Remove debug scripts from root
4. Configure file upload limits in php.ini
5. Set proper permissions: `chown -R www-data:www-data uploads/`
6. Enable HTTPS and configure SSL
7. Set up database backups
8. Configure error logging to files, not screen
