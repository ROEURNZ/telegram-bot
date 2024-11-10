<?php 
require __DIR__  .'/../../vendor/autoload.php'; 

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . "/../../");

$dotenv->load();

global $api_key;

$api_key = $_ENV['TELEGRAM_API_KEY'] ;

// define('BASE_URL', $_ENV['BASE_URL']);
// define('COMPANY_NAME', $_ENV['COMPANY_NAME']);
