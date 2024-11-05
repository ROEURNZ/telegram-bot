<?php

require __DIR__ . '/../vendor/autoload.php';     
include __DIR__ . "/../app/Config/dynDirs.php";

use Dotenv\Dotenv;
// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . $z1);
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
class EzzeTeamDatabase {
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
            self::$instance = new EzzeTeamDatabase();
        }
        return self::$instance->pdo;
    }
}

