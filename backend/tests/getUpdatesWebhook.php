<?php


// Set your bot token
$config = require("../app/Config/bot_config.php");
$botToken = $config['bot_token'] ?? null; // Your bot token
$chatId = $config['chat_id'] ?? null; // Your bot chatID

// Get the incoming update
$update = json_decode(file_get_contents("php://input"), true);

// Check if the update contains a message
if (isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"]; // Get the Chat ID
    $userId = $update["message"]["from"]["id"]; // Get the User ID
    $messageText = $update["message"]["text"]; // Get the message text

    // Log the user details and message
    error_log("User ID: $userId\n");
    error_log("Chat ID: $chatId\n");
    error_log("Message: $messageText\n");

    // Send a response back to the user
    $responseText = "You said: " . $messageText;
    file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($responseText));
}

