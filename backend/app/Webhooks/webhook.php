<?php
/* @ROEURNZ=> File name & directory
 * backend/app/Webhooks/webhook.php
 * 
 */

// Load configuration and dependencies
$config = require_once __DIR__ . '/../config/bot_config.php';
require_once __DIR__ . '/../Services/HttpClient.php';
require_once __DIR__ . '/../Handlers/CurlHelper.php';

use App\Services\HttpClient;

$input = file_get_contents("php://input");
$update = json_decode($input, true);

if (isset($update['message'])) {
    // Handle the incoming message
    $chatId = $update['message']['chat']['id'];
    $messageText = $update['message']['text'];

    // Example: Send a reply
    $httpClient = new HttpClient($config['bot_token'], $chatId);
    $httpClient->sendMessage("You said: " . $messageText);
}

// Log the received update (optional)
file_put_contents('webhook.log', $input . PHP_EOL, FILE_APPEND);
