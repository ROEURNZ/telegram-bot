<?php
global $api_key;
// Load Composer's autoloader and .env file
require __DIR__ . '/vendor/autoload.php'; // If same directory level, you just set __DIR__. '/with_your_file_path'
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // If nested directory level, you have to set __DIR__. '/../with_your_file_path'
$dotenv->load();

$api_key = $_ENV['TELEGRAM_API_KEY'] ?? null;
// Make the Telegram API URL globally accessible
$url = 'https://api.telegram.org/bot' . $api_key . '/';


define('BASE_URL', $_ENV['BASE_URL'] ?? null);
define('COMPANY_NAME', $_ENV['COMPANY_NAME'] ?? null);


// echo BASE_URL; 
// echo COMPANY_NAME;  


/**
// Define log directory and error log file
$logDir = __DIR__ . '/storage/logs/errors';
$errorLogFile = "$logDir/database_log.log";

// Custom error logging function
function logError($message) {
    global $errorLogFile;
    $logMessage = sprintf("[%s] ERROR: %s\n", date('Y-m-d H:i:s'), $message);
    if (file_put_contents($errorLogFile, $logMessage, FILE_APPEND) === false) {
        throw new Exception('Failed to write to error log file.');
    }
}

// Create directory if it doesn't exist and log action
function createDirectory($dir) {
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
    }
    logError(is_dir($dir) ? "Directory already exists: $dir" : "Directory created: $dir");
}

// Create logs and error directories
// createDirectory($logDir); // Uncomment if you want to create the directory

// Set error reporting and logging
error_reporting(E_ALL); // Uncomment if you want to enable error reporting
ini_set('log_errors', 1); // Uncomment if you want to log errors
ini_set('error_log', $errorLogFile); // Uncomment if you want to set the error log file

// Validate API key presence
if (empty($api_key)) {
    $errorMessage = 'TELEGRAM_API_KEY is not set in the .env file.';
    logError($errorMessage); // Uncomment if logging is enabled
    throw new Exception($errorMessage);
}

*/