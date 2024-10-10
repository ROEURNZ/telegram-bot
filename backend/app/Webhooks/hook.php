<?php

// Set your bot token
$config = require("../app/Config/bot_config.php");
$botToken = $config['bot_token'] ?? null; // Your bot token
$chatId= $config['chat_id'] ?? null; // Your bot token
$webhookUrl = 'https://webhook.php'; // Change to your actual URL

// Set the webhook
$response = file_get_contents("https://api.telegram.org/bot$botToken/setWebhook?url=$webhookUrl");

echo $response; // Check the response for success or failure
?>
