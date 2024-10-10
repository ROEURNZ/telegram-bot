<?php 
/* 
 * @ROEURNZ => File name & directory
 * backend/app/Config/bot_config.php
 */

// Include the DirectoryLevels class
require_once __DIR__ . '/dirl.php'; 
// Instantiate the DirLevels class
new XDirLevel(); // This sets the global directory variables

// Load Composer's autoloader
require __DIR__ . $x2.'/vendor/autoload.php';

// Load the .env file from the backend directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . $x2.'/'); 
$dotenv->load();

// Define the log directory and the errors subdirectory
$logDir = __DIR__ .$x1.'/storage/logs'; // Main logs directory
$errorLogDir = $logDir . '/errors'; // Subdirectory for error logs

// Custom error logging function
if (!function_exists('logError')) {
    function logError($message) {
        global $errorLogDir; // Use the global error log directory
        $logFile = $errorLogDir . '/error_log.log'; // Specify your log file name here

        // Prepare the error message with a timestamp
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] ERROR: $message" . PHP_EOL;

        // Write the error message to the log file
        if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
            throw new Exception('Failed to write to error log file.');
        }
    }
}

// Function to create directory if it doesn't exist, and log action
if (!function_exists('createDirectory')) {
    function createDirectory($dir) {
        if (!is_dir($dir)) {  // Use is_dir() to check if it's a directory
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            } else {
                logError("Directory created: $dir");
            }
        } else {
            logError("Directory already exists: $dir");
        }
    }
}

// Create the logs and error directories
createDirectory($logDir);
createDirectory($errorLogDir);

// Set error reporting and logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', $errorLogDir . '/error_log.log');

// Define the Telegram Bot API key from the .env file
global $api_key;
global $token;
$api_key = $_ENV['TELEGRAM_API_KEY'];
$token = $api_key;


// Validate API key presence
if (empty($api_key)) {
    $errorMessage = 'TELEGRAM_API_KEY is not set in the .env file.';
    logError($errorMessage);
    throw new Exception($errorMessage);
}

// Make the URL globally accessible
global $url;
$url = 'https://api.telegram.org/bot' . $api_key . '/';
