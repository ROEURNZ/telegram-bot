<?php 
global $api_key, $url_bot;
// Load Composer's autoloader and .env file
require __DIR__  .'/../../vendor/autoload.php'; 

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../../"); 
$dotenv->load();

$api_key = $_ENV['TELEGRAM_API_KEY'] ;
// Make the Telegram API URL globally accessible
$url_bot = 'https://api.telegram.org/bot' . $api_key . '/';

define('BASE_URL', $_ENV['BASE_URL']);
define('COMPANY_NAME', $_ENV['COMPANY_NAME']);

// echo $api_key;