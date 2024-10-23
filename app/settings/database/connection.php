<?php
// File name: connection.php
// Purpose: Database connection setup and error handling
/* 
 * @ROEURNZ => File name & directory
 * /database/connection.php
 */

include __DIR__ . "/../app/Config/dynDirs.php";
require __DIR__ . $n1 . '/vendor/autoload.php';     

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . $n1 .'');
$dotenv->load();

// Database configuration
$dbHost = $_ENV['DB_HOST'];
$dbUsername = $_ENV['DB_USERNAME'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbDatabase = $_ENV['DB_DATABASE'];

// Validate database configuration
if (!$dbHost || !$dbUsername || !$dbPassword || !$dbDatabase) {
    die("Database configuration is missing.");
}

// PDO Singleton Class
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        global $dbHost, $dbUsername, $dbPassword, $dbDatabase;

        $dsn = "mysql:host=$dbHost;dbname=$dbDatabase;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $dbUsername, $dbPassword);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}

