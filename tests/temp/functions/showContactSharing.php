<?php

// Function to show the contact sharing prompt
function showContactSharing($chatId, $language, $baseLanguage, $token)
{
    global $baseLanguage; 
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => $baseLanguage[$language]['share_contact'], 'request_contact' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $baseLanguage[$language]['contact_request'], "https://api.telegram.org/bot$token/", $replyMarkup);
}
