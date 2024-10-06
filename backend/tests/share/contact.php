<?php
set_time_limit(300); // Allow the script to run indefinitely

// Load the bot configuration
$config = require("../../app/Config/bot_config.php");
$botToken = "7370069715:AAGVt-jkPxRbt8Dok5zO3U3gbjU5kDNF0jE";
// Check if the bot token is available
if (!$botToken) {
    die("Bot token is not set in the configuration.");
}

// Get the latest update ID from a file or set it to 0
$lastUpdateIdFile = 'last_update_id.txt';
$lastUpdateId = file_exists($lastUpdateIdFile) ? (int)file_get_contents($lastUpdateIdFile) : 0;

// Main loop to poll for updates
while (true) {
    // Prepare the request URL
    $url = "https://api.telegram.org/bot$botToken/getUpdates?offset=$lastUpdateId&limit=1&timeout=30";
    $response = @file_get_contents($url); // Use '@' to suppress warnings temporarily

    if ($response !== false) {
        $updates = json_decode($response, true);

        // Check if the request was successful and there are updates
        if (isset($updates['ok']) && $updates['ok'] && !empty($updates['result'])) {
            foreach ($updates['result'] as $update) {
                $lastUpdateId = $update['update_id'] + 1; // Increment the last update ID
                file_put_contents($lastUpdateIdFile, $lastUpdateId); // Save the last update ID

                if (isset($update['message'])) {
                    $chatId = $update['message']['chat']['id'];
                    $messageText = $update['message']['text'] ?? '';

                    // First check for contact sharing
                    if (isset($update['message']['contact'])) {
                        handleContact($update['message']['contact'], $update['message']['from']['id'], $chatId);
                    } 
                    // Then check for location sharing
                    elseif (isset($update['message']['location'])) {
                        handleLocation($update['message']['location'], $update['message']['from']['id'], $chatId);
                    } 
                    // Handle user commands directly
                    else {
                        handleCommands($chatId, $messageText, $update);
                    }
                }
            }
        } else {
            // Log the response if there are issues
            error_log("Error retrieving updates: " . json_encode($updates));
        }
    } else {
        // Log the error if the request fails
        error_log("Failed to get updates: " . error_get_last()['message']);
    }

    // Optional: Sleep for a short duration to avoid hammering the API
    usleep(500000); // Sleep for 0.5 seconds
}

// Function to handle user commands with a complete process per command
function handleCommands($chatId, $messageText, $update) {
    switch ($messageText) {
        case '/start':
            sendMessage($chatId, "Welcome! You can use the following commands to share information:\n- /share_contact\n- /share_location");
            break;
        case '/share_contact':
            sendMessage($chatId, "Please share your contact using Telegram's share contact feature.");
            break;
        case '/share_location':
            sendMessage($chatId, "Please share your location using Telegram's location sharing feature.");
            break;
        default:
            // Handle unrecognized input
            sendMessage($chatId, "Unknown command. Use /start to see available options.");
            break;
    }
}

// Function to send a message
function sendMessage($chatId, $text, $replyMarkup = null) {
    global $botToken; // Access the bot token from the global scope
    $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($text);
    
    // Add reply_markup if provided
    if ($replyMarkup) {
        $url .= "&reply_markup=" . urlencode($replyMarkup);
    }

    @file_get_contents($url); // Use '@' to suppress warnings temporarily
}

// Function to handle the shared contact
function handleContact($contact, $userId, $chatId) {
    $contactName = trim($contact['first_name'] . ' ' . ($contact['last_name'] ?? ''));
    $contactPhone = $contact['phone_number'];

    // Log or process the contact information
    error_log("User ID: $userId shared contact: $contactName, Phone: $contactPhone");

    // Respond to the user
    sendMessage($chatId, "Thank you for sharing your contact!\nName: $contactName\nPhone: $contactPhone");
}

// Function to handle the shared location
function handleLocation($location, $userId, $chatId) {
    $latitude = $location['latitude'];
    $longitude = $location['longitude'];

    // Log or process the location information
    error_log("User ID: $userId shared location: Latitude: $latitude, Longitude: $longitude");

    // Respond to the user
    sendMessage($chatId, "Thank you for sharing your location!\nLatitude: $latitude\nLongitude: $longitude");
}
