<?php 
global $api_key;
include __DIR__ ."/dynDirs.php";
// Load Composer's autoloader and .env file
require __DIR__ . $n2 .'/vendor/autoload.php'; 

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . $z2); 
$dotenv->load();

$api_key = $_ENV['TELEGRAM_API_KEY'] ?? null;
// Make the Telegram API URL globally accessible
$url = 'https://api.telegram.org/bot' . $api_key . '/';

define('BASE_URL', $_ENV['BASE_URL'] ?? null);
define('COMPANY_NAME', $_ENV['COMPANY_NAME'] ?? null);

// echo $api_key;