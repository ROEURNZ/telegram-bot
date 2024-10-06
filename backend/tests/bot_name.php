<?php

require_once '../vendor/autoload.php'; // Include Composer's autoloader

// Set your bot token and chat ID
$config = require("../app/Config/bot_config.php");
$botToken = $config['bot_token'];
$chatId = $config['chat_id'] ?? ''; // Ensure chat ID is set or handle it appropriately

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;

// Define your bot username
$bot_username = 'ezze_assistant_bot'; // e.g., MyBotName


$apiURL = "https://api.telegram.org/bot$botToken";

// Function to get updates from Telegram
function getUpdates($offset = 0) {
    global $apiURL;
    $response = file_get_contents("$apiURL/getUpdates?offset=$offset");
    return json_decode($response, true);
}

// Function to send a message
function sendMessage($chatId, $text) {
    global $apiURL;
    file_get_contents("$apiURL/sendMessage?chat_id=$chatId&text=" . urlencode($text));
}

// Function to handle commands
function handleCommand($text) {
    global $bot_username;

    switch ($text) {
        case '/start':
            return "Hello! I am $bot_username, your assistant bot.";
        case '/help':
            return "Here are the commands you can use: /start, /help";
        default:
            return "I didn't understand that command.";
    }
}

// Start polling
$offset = 0;

while (true) {
    $updates = getUpdates($offset);
    
    foreach ($updates['result'] as $update) {
        // Get the chat ID and message text
        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'];
        
        // Handle the command and get the response
        $responseText = handleCommand($text);
        
        // Send the response back to the user
        sendMessage($chatId, $responseText);
        
        // Update the offset to the latest update ID
        $offset = $update['update_id'] + 1;
    }
    
    // Sleep for a bit before polling again
    sleep(1);
}
?>
