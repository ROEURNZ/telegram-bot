<?php

$config = require("../app/Config/bot_config.php");
$botToken = $config['bot_token'] ?? null; // Your bot token
$chatId= $config['chat_id'] ?? null; // Your bot token
$userId = "5938977499"; // Replace with the actual user ID you want to check

// Prepare the request URL
$url = "https://api.telegram.org/bot$botToken/getChatMember?chat_id=$chatId&user_id=$userId";

// Make a GET request to the Telegram API
$response = file_get_contents($url);
$data = json_decode($response, true);

// Check if the request was successful
if ($data['ok']) {
    $status = $data['result']['status']; // Possible statuses: "creator", "administrator", "member", "restricted", "left", "kicked", "unknown"

    if ($status === 'creator' || $status === 'administrator' || $status === 'member') {
        echo "User is a member of the chat (may be online).";
    } elseif ($status === 'restricted') {
        echo "User is restricted in the chat.";
    } elseif ($status === 'left' || $status === 'kicked') {
        echo "User is offline or not a member of the chat.";
    } else {
        echo "User status is unknown.";
    }
} else {
    echo "Error: " . $data['description'];
}
?>
