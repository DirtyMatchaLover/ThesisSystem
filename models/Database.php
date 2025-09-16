<?php
class Database {
    private static $instance = null;

    // Private constructor to prevent direct instantiation
    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            // Load environment variables from .env file if it exists
            self::loadEnvFile();
            
            // Use Docker environment variables with fallbacks for local development
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $dbname = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'thesis_db';
            $username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
            $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
            $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
                
                // Test the connection
                self::$instance->query("SELECT 1");
                
            } catch (PDOException $e) {
                // Log the error (in production, don't expose database details)
                error_log("Database Connection Failed: " . $e->getMessage());
                
                // In development, show the error. In production, show a generic message.
                if (getenv('APP_ENV') === 'development' || $_ENV['APP_ENV'] ?? 'production' === 'development') {
                    die("Database Connection Failed: " . $e->getMessage());
                } else {
                    die("Database connection error. Please contact support.");
                }
            }
        }
        return self::$instance;
    }

    /**
     * Load environment variables from .env file
     */
    private static function loadEnvFile() {
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue; // Skip comments
                }
                
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    
                    if (!array_key_exists($name, $_ENV)) {
                        $_ENV[$name] = $value;
                    }
                }
            }
        }
    }

    /**
     * Get the PDO connection instance
     * Alternative method name for compatibility
     */
    public static function getConnection() {
        return self::getInstance();
    }

    /**
     * Test database connection
     */
    public static function testConnection() {
        try {
            $pdo = self::getInstance();
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            return $result['test'] === 1;
        } catch (Exception $e) {
            error_log("Database test failed: " . $e->getMessage());
            return false;
        }
    }
}