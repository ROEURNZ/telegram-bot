<?php

function isAllowedImage($filePath)
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'webp'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    return in_array($extension, $allowedExtensions);
}


function showContactSharing($chatId, $language, $baseLanguage, $token)
{
    global $baseLanguage; 
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => $baseLanguage[$language]['share_contact'], 'request_contact' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $baseLanguage[$language]['contact_request'], $token, $replyMarkup);
}

function showLocationSharing($chatId, $language, $baseLanguage, $token)
{
    $replyMarkup = json_encode([
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
}



function handleCallbackQuery($update, $baseLanguage, $userLanguages, $token)
{
    // Check if the callback_query exists in the update array
    if (isset($update['callback_query'])) {
        $callbackData = $update['callback_query']['data'];
        $chatId = $update['callback_query']['message']['chat']['id'];
        $messageId = $update['callback_query']['message']['message_id'];
        $language = $userLanguages[$chatId] ?? 'en';

        // Prepare the keyboard to disable the opposite button
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => $baseLanguage[$language]['yes'], 'callback_data' => 'yes', 'disabled' => $callbackData === 'yes'],
                    ['text' => $baseLanguage[$language]['no'], 'callback_data' => 'no', 'disabled' => $callbackData === 'no']
                ]
            ]
        ];

        // Update the message with the modified keyboard (disable the other button)
        editMessageReplyMarkup($chatId, $messageId, $keyboard, $token);

        // Now perform action based on callback (either "yes" or "no")
        if ($callbackData === "yes") {
            showContactSharing($chatId, $language, $baseLanguage, $token);
        } elseif ($callbackData === "no") {
            sendMessage($chatId, $baseLanguage[$language]['skip_share_contact'], $token, json_encode(['remove_keyboard' => true]));
        }
    }
}

// Function to send the inline keyboard to the user
function sendInlineKeyboard($chatId, $language, $baseLanguage, $token)
{
    $messageText = $baseLanguage[$language]['prompt'];
    $yesText = $baseLanguage[$language]['yes'];
    $noText = $baseLanguage[$language]['no'];

    // Create the initial inline keyboard with active buttons
    $replyMarkup = json_encode([
        'inline_keyboard' => [
            [
                ['text' => $yesText, 'callback_data' => 'yes'],
                ['text' => $noText, 'callback_data' => 'no']
            ]
        ]
    ]);

    // Send the message with the inline keyboard
    sendMessage($chatId, $messageText, $token, $replyMarkup);
}




function editMessageReplyMarkup($chatId, $messageId, $keyboard, $token) {
    $url = "https://api.telegram.org/bot$token/editMessageReplyMarkup";
    $data = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'reply_markup' => json_encode($keyboard)
    ];

    // Use curl for error handling and better reliability
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    if(curl_errno($ch)) {
        // Handle error (you might want to log this somewhere)
        error_log('cURL Error: ' . curl_error($ch));
    }
    curl_close($ch);

    return $response;
}

function selectLanguage($chatId, $language, $baseLanguage, $token) {
    $replyMarkup = json_encode([
        'keyboard' => [
            [
                ['text' => $baseLanguage['en']['language_option']],
                ['text' => $baseLanguage['kh']['language_option']]
            ]
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);

    // Send the message prompting the user to choose a language
    sendMessage($chatId, $baseLanguage[$language]['please_choose_language'], $token, $replyMarkup);
}
