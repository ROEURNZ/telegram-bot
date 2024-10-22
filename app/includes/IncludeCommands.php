<?php 

$config = include __DIR__ .  '/../Config/api_key.php';
$token = $api_key;
include __DIR__ . '/../Config/dynDirs.php';
set_time_limit(0); 
session_start();
date_default_timezone_set("Asia/Phnom_Penh");
$offset = 0;

$botApiUr = "https://api.telegram.org/bot{$token}/";

// Load localization files
$messages = [
    'en' => include __DIR__ . $n1 . '/Localization/languages/en/english.php',
    'kh' => include __DIR__ . $n1 . '/Localization/languages/kh/khmer.php',
];

include __DIR__ . $n1 . '/Localization/dateformat/dateFormat.php';
// require_once __DIR__ . $x1 . '/Commands/SystemCommand.php';

include __DIR__ . '/../Models/EzzeModel.php';



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


// Retrieve the selected language from the session or use a default
$chatId = isset($_SESSION['currentChatId']) ? $_SESSION['currentChatId'] : null;
$language = isset($_SESSION['userLanguages'][$chatId]) ? $_SESSION['userLanguages'][$chatId] : 'en'; 

// Get messages based on the selected language
$currentMessages = $messages[$language];



// Function to set Telegram bot commands
function setCommands($token, $messages) {
    // Prepare commands array in the format required by Telegram API
    $commandsToSet = [
        ['command' => 'start', 'description' => $messages['start_desc']],
        ['command' => 'share_contact', 'description' => $messages['share_contact_desc']],
        ['command' => 'decode', 'description' => $messages['decode_desc']],
        ['command' => 'ocr', 'description' => $messages['ocr_desc']], 
        ['command' => 'share_location', 'description' => $messages['share_location_desc']],
        ['command' => 'help', 'description' => $messages['help_desc']],
        ['command' => 'menu', 'description' => $messages['menu_desc']],
        ['command' => 'change_language', 'description' => $messages['change_language_desc']],
    ];

    // Prepare the data for the API call
    $data = ['commands' => $commandsToSet];
    $url = "https://api.telegram.org/bot{$token}/setMyCommands";

    // Initialize cURL for sending the request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Execute the request and check for errors
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    if ($response === false || $httpCode !== 200) {
        echo "Error setting commands: " . curl_error($ch) . " | HTTP Code: " . $httpCode;
    } else {
        echo "Commands set successfully: " . $response;
    }

    // Close the cURL session
    curl_close($ch);
}

// Call the function to set commands every time the bot starts
setCommands($token, $currentMessages);
