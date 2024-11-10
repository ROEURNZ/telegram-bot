<?php

include __DIR__ . "/../../../vendor/autoload.php";




function isAllowedImage($filePath)
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'webp'];
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    return in_array($extension, $allowedExtensions);
}


function showContactSharing($chatId, $token, $language)
{
    global $baseLanguage; 
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => $baseLanguage[$language]['share_contact'], 'request_contact' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $baseLanguage[$language]['contact_request'], $token, $replyMarkup);
}

function showLocationSharing($chatId, $token, $language)
{
    global $baseLanguage;
    $replyMarkup = json_encode([
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
}

function selectLanguage($chatId, $language, $baseLanguage, $token) {
    // Prepare the reply markup for the language selection keyboard
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

