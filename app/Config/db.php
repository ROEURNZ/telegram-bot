<?php
/* 
 * @ROEURNZ => File name & directory
 * backend/app/Config/db.php
 */

// Include the DirectoryLevels class
require_once __DIR__ . '/dirl.php'; 
new XDirLevel(); // Sets global directory variables

require __DIR__ . $x2 .'/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.$x2.'/');      
$dotenv->load();

$dbHost = $_ENV['DB_HOST'];
$dbUsername = $_ENV['DB_USERNAME'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbDatabase = $_ENV['DB_DATABASE'];

// Validate database connection variables
if (empty($dbHost) || empty($dbUsername) || empty($dbPassword) || empty($dbDatabase)) {
    die("Database configuration is missing.");
}

// Define logs directory
$logDir = __DIR__ .$x2.'/storage/logs';

if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        global $dbHost, $dbUsername, $dbPassword, $dbDatabase;

        $dsn = "mysql:host=$dbHost;dbname=$dbDatabase;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $dbUsername, $dbPassword);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("SET NAMES 'utf8mb4'");
        $this->pdo->exec("SET CHARACTER SET 'utf8mb4'");
        $this->pdo->exec("SET collation_connection = 'utf8mb4_unicode_ci'");
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}

// Check connection
try {
    $pdo = Database::getInstance();
    $pdo->query("SELECT 1");
    echo "Database connection successful!";
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage() . "\n", 3, $logDir . '/database_errors.log');
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
}
