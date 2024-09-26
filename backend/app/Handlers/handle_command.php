<?php
// backend/app/Handlers/handle_commands.php

// Load configuration
$config = require '../config/bot_config.php';
set_time_limit(300); // Allow the script to run indefinitely

// Function to send messages
function sendMessage($chatId, $message, $token, $replyMarkup = null) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
    ];

    // Add reply markup if provided
    if ($replyMarkup) {
        $data['reply_markup'] = $replyMarkup;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

    $response = curl_exec($ch);
    
    if ($response === false) {
        echo "Error sending message: " . curl_error($ch);
    }

    curl_close($ch);
}

// Function to process updates
function processUpdates($updates, $token) {
    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            
            // Handle text messages
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                switch ($text) {
                    case '/start':
                        sendMessage($chatId, "Hello {$update['message']['from']['first_name']}! Welcome to your bot. Please send me your contact.", $token);
                        
                        // Prompt to share contact
                        $replyMarkup = json_encode([
                            'keyboard' => [[['text' => 'Share Contact', 'request_contact' => true]]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, "Please share your contact information using the button below.", $token, $replyMarkup);
                        break;
                    
                    case '/help':
                        sendMessage($chatId, "This is your help message. You can use /start to begin.", $token);
                        break;

                    case '/menu':
                        sendMessage($chatId, "Menu options: /start, /help", $token);
                        break;

                    // Add more cases as needed for other commands
                }
            }

            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available"; // Use username if available

                // Respond with contact details
                $responseMessage = "Thanks for sharing your contact!\n";
                $responseMessage .= "Full Name: {$firstName} {$lastName}\n";
                $responseMessage .= "Phone Number: {$phoneNumber}\n";
                $responseMessage .= "Username: {$username}"; // Add username link

                sendMessage($chatId, $responseMessage, $token);

                // Prompt to share location
                $locationMarkup = json_encode([
                    'keyboard' => [[['text' => 'Share Location', 'request_location' => true]]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]);
                sendMessage($chatId, "Please share your current location using the button below.", $token, $locationMarkup);
            }

            // Handle location sharing
            if (isset($update['message']['location'])) {
                $location = $update['message']['location'];
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];

                // Respond with location details
                $responseMessage = "Thanks for sharing your location!\n";
                $responseMessage .= "Latitude: {$latitude}\n";
                $responseMessage .= "Longitude: {$longitude}";

                sendMessage($chatId, $responseMessage, $token);
            }
        }
    }
}

// Main loop to fetch updates
$offset = 0; // Offset for pagination
while (true) {
    // Get updates from Telegram
    $updates = file_get_contents("https://api.telegram.org/bot{$config['bot_token']}/getUpdates?offset={$offset}");
    $updates = json_decode($updates, true);

    // Handle each update
    if (isset($updates['result'])) {
        processUpdates($updates['result'], $config['bot_token']);
        
        // Update the offset to avoid processing the same updates again
        $offset = end($updates['result'])['update_id'] + 1;
    } else {
        echo "No updates found.";
    }

    // Sleep for a short duration to avoid spamming the API
    sleep(2); // Sleep for 2 seconds (adjust as needed)
}

