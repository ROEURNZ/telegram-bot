<?php 

// backend/app/Handlers/CommandHandlers.php
// Load configuration
require_once __DIR__ . '/../Config/dirl.php';
new XDirLevel();

$config = require __DIR__ . $x1 . '/Config/bot_config.php';
$token = $api_key;

set_time_limit(0); // This will allow the script to run indefinitely
session_start();
date_default_timezone_set("Asia/Phnom_Penh");
$offset = 0;

$botApiUrl = "https://api.telegram.org/bot{$token}/";

// Load localization files
$messages = [
    'en' => include __DIR__ . $x1 . '/Localization/languages/en/english.php',
    'kh' => include __DIR__ . $x1 . '/Localization/languages/kh/khmer.php',
];

include __DIR__ . $x1 . '/Localization/dateformat/dateFormat.php';
include __DIR__ . $x1 . '/Commands/SystemCommand.php';

include __DIR__ . '/../Models/EzzeModel.php';

// Instantiate the model
$ezzeModel = new EzzeModels();

// $ezzeModel = new EzzeModels($pdo);
// Function to send messages
function sendMessage($chatId, $message, $token, $replyMarkup = null)
{
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => false,
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
        echo "Error sending message: " . curl_error($ch);
    }

    curl_close($ch);
}

