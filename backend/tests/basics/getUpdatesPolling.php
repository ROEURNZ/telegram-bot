<?php

// Set your bot token
$config = require("../app/Config/bot_config.php");
$botToken = $config['bot_token'] ?? null; // Your bot token
$chatId= $config['chat_id'] ?? null; // Your bot token

set_time_limit(100); // Allow the script to run indefinitely

// Initialize offset for updates
$offset = 0;

while (true) {
    // Fetch updates from Telegram
    $response = file_get_contents("https://api.telegram.org/bot$botToken/getUpdates?offset=$offset");
    $data = json_decode($response, true);

    // Check if the request was successful
    if ($data['ok']) {
        foreach ($data['result'] as $update) {
            // Get the update ID and update the offset
            $updateId = $update['update_id'];
            $offset = $updateId + 1;

            // Check if there's a message in the update
            if (isset($update['message'])) {
                $chatId = $update['message']['chat']['id']; // Get the Chat ID
                $userId = $update['message']['from']['id']; // Get the User ID
                $messageText = $update['message']['text']; // Get the message text

                // Log or process the message
                echo "User ID: $userId\n";
                echo "Chat ID: $chatId\n";
                echo "Message: $messageText\n";

                // Send a response back to the user
                $responseText = "You said: " . $messageText;
                file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($responseText));
            }
        }
    }

    // Sleep for a short period before the next request
    sleep(1);
}

