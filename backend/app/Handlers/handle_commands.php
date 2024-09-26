<?php
// the filename: backend/app/Handlers/handle_commands.php

require_once __DIR__ . '/../Controllers/BotDecodeController.php';
// Load configuration
$config = require '../config/bot_config.php';
set_time_limit(300); // Allow the script to run indefinitely
date_default_timezone_set("Asia/Phnom_Penh");

// Load localization files
$messages = [
    'en' => include('../Localization/languages/en/english.php'),
    'kh' => include('../Localization/languages/kh/khmer.php'),
];

// Function to send messages with optional inline keyboard
function sendMessage($chatId, $message, $token, $replyMarkup = null) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];

    if ($replyMarkup) {
        $data['reply_markup'] = $replyMarkup;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    
    if ($response === false) {
        error_log("Error sending message: " . curl_error($ch)); // Log error instead of echo
    }

    curl_close($ch);
}

// Function to process updates
function processUpdates($updates, $token) {
    static $userLanguages = []; // Store user language preferences
    static $step = []; // Track steps for users

    global $messages; // Access global messages variable

    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];

            // Handle text messages
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                // Store user's language preference
                if ($text === '🇺🇸English' || $text === '🇰🇭ភាសាខ្មែរ') {
                    $userLanguages[$chatId] = ($text === '🇺🇸English') ? 'en' : 'kh';
                    $language = $userLanguages[$chatId];

                    // Acknowledge the language selection
                    sendMessage($chatId, $messages[$language]['language_selection'], $token);
                    showContactSharing($chatId, $token, $language);
                    continue; // Skip further processing for this message
                }

                // Default response if /start or /help is used
                if ($text === '/start') {
                    sendMessage($chatId, $messages['en']['welcome_message'], $token);
                    // Prompt for language selection
                    $replyMarkup = json_encode([
                        'keyboard' => [
                            [['text' => '🇺🇸English'], ['text' => '🇰🇭ភាសាខ្មែរ']]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);
                    sendMessage($chatId, $messages['en']['please_choose_language'], $token, $replyMarkup);
                    continue; // Skip further processing for this message
                }

                if ($text === '/help') {
                    sendMessage($chatId, $messages['en']['help_message'], $token);
                    continue;
                }
            }

            // Handle the /decode command
            if (isset($text) && $text === '/decode') {
                $language = $userLanguages[$chatId] ?? 'en'; // Default to English if not set
            
                if (!isset($step[$chatId])) {
                    // First time decoding, prompt for image upload
                    sendMessage($chatId, $messages[$language]['decode_prompt'], $token);
                    $step[$chatId] = 'decode'; // Set step to decoding
                } elseif ($step[$chatId] === 'decode') {
                    if (isset($update['message']['photo'])) {
                        // Process the uploaded image
                        $photoId = end($update['message']['photo'])['file_id']; // Get the highest resolution image
                        sendMessage($chatId, $messages[$language]['image_received'], $token);
                        // Add further logic to download/process the image here
                        $step[$chatId] = null; // Reset the step after processing
                    } else {
                        // Waiting for image
                        sendMessage($chatId, $messages[$language]['waiting_for_image'], $token);
                    }
                } else {
                    // If not in the correct step for decoding
                    sendMessage($chatId, $messages[$language]['complete_previous_steps'], $token);
                }
                continue; // Skip further processing for this message
            }
            
            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";

                // Respond with contact details based on language preference
                $language = $userLanguages[$chatId] ?? 'en'; // Default to English if not set
                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_contact'],
                    $firstName,
                    $lastName,
                    $phoneNumber,
                    $username
                );

                sendMessage($chatId, $responseMessage, $token);

                // Send location prompt
                sendMessage($chatId, $messages[$language], $token);
                showLocationSharing($chatId, $token, $language);
            }

            // Handle location sharing
            if (isset($update['message']['location'])) {
                $location = $update['message']['location'];
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];

                $mapsUrl = "https://www.google.com/maps?q={$latitude},{$longitude}";

                $language = $userLanguages[$chatId] ?? 'en'; // Default to English if not set
                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_location'],
                    $latitude,
                    $longitude,
                    $mapsUrl
                );

                sendMessage($chatId, $responseMessage, $token);
                sendMessage($chatId, $messages[$language]['thank_you_location'], $token);
            }
        }
    }
}

// Function to show contact sharing options
function showContactSharing($chatId, $token, $language) {
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => ($language === 'en' ? 'Share Contact' : 'ព័ត៌មានទំនាក់ទំនង'), 'request_contact' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, ($language === 'en' ? "Please share your contact information using the button below:" : "សូមផ្ដល់ព័ត៌មានទំនាក់ទំនងរបស់អ្នកដោយប្រើប៊ូតុងខាងក្រោម។"), $token, $replyMarkup);
}

// Function to show location sharing options
function showLocationSharing($chatId, $token, $language) {
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => ($language === 'en' ? 'Share Location' : 'ផ្ញើទីតាំង'), 'request_location' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, ($language === 'en' ? "Please share your current location!" : "សូមផ្ញើទីតាំងរបស់អ្នក!"), $token, $replyMarkup);
}

// Main loop to fetch updates
$offset = 0; // Offset for pagination
while (true) {
    $updates = file_get_contents("https://api.telegram.org/bot{$config['bot_token']}/getUpdates?offset={$offset}");
    $updates = json_decode($updates, true);

    if (isset($updates['result'])) {
        processUpdates($updates['result'], $config['bot_token']);
        $offset = end($updates['result'])['update_id'] + 1;
    } else {
        error_log("No updates found."); // Log no updates
    }

    sleep(2); 
}
