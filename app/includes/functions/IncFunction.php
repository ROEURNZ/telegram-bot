<?php

include __DIR__ . "/../../../vendor/autoload.php";


function isBarcodeImage($filePath)
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedExtensions);
}

function isInvoiceImage($filePath)
{
    $allowedInvoiceExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedInvoiceExtensions);
}

function isMRZImage($filePath)
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedExtensions);
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