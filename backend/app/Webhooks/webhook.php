<?php

// file name 'webhook.php'


include __DIR__ ."/../Config/bot_config.php";
$token = $api_key;

// Get the update sent from Telegram
$update = file_get_contents("php://input");
$updateArray = json_decode($update, true);

// You can now process the update
if (isset($updateArray['message'])) {
    $chatId = $updateArray['message']['chat']['id'];
    $messageText = $updateArray['message']['text'];

    // Respond to the message (adjust this according to your bot's logic)
    if ($messageText === "/start") {
        sendMessage($chatId, "Welcome to the bot!", $token);
    } else {
        sendMessage($chatId, "You said: $messageText", $token);
    }
}

// Function to send a message via the Telegram API
function sendMessage($chatId, $message, $token) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $postFields = [
        'chat_id' => $chatId,
        'text' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
}
