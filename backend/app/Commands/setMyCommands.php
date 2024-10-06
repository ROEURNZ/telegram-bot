<?php
// backend/app/Commands/setMyCommands.php

// Start the session to access session variables
session_start();

// Load configuration
$config = require '../config/bot_config.php';
$token = $config['bot_token'];

$_SESSION['language'] = 'en'; // Set based on user input or initial setting

// Function to set Telegram bot commands
function setCommands($language, $token)
{
    // Define commands for English and Khmer
    $commands = [
        'en' => [
            [
                "command" => "start",
                "description" => "Click on /start command to begin the bot"
            ],
            [
                "command" => "stop",
                "description" => "Click on /stop command to stop the bot"
            ],
            [
                "command" => "share_contact",
                "description" => "Share your contact"
            ],
            [
                "command" => "decode",
                "description" => "Decode a barcode or QR code"
            ],
            [
                "command" => "share_location",
                "description" => "Share your location"
            ],
            [
                "command" => "help",
                "description" => "Get help"
            ],
            [
                "command" => "menu",
                "description" => "Open the menu"
            ],
            [
                "command" => "change_language",
                "description" => "Change the language"
            ],
        ],
        'kh' => [
            [
                "command" => "start",
                "description" => "សូមចុចលើពាក្យ /start ដើម្បីចាប់ផ្ដើម។"
            ],
            [
                "command" => "stop",
                "description" => "សូមចុចលើពាក្យ /stop ដើម្បីបញ្ឃប់។"
            ],
            [
                "command" => "share_contact",
                "description" => "ចែករំលែកទំនាក់ទំនងរបស់អ្នក"
            ],
            [
                "command" => "decode",
                "description" => "ដកស្រង់អត្ថបទពីកូដបារ៉ូដ ឬកូដ QR"
            ],
            [
                "command" => "share_location",
                "description" => "ចែករំលែកទីតាំងរបស់អ្នក"
            ],
            [
                "command" => "help",
                "description" => "ទទួលបានជំនួយ"
            ],
            [
                "command" => "menu",
                "description" => "បើកម៉ឺនុយ"
            ],
            [
                "command" => "change_language",
                "description" => "ប្ដូរភាសា"
            ],
        ],
    ];

    // Get commands based on the selected language
    $commandsToSet = isset($commands[$language]) ? $commands[$language] : $commands['en'];

    // Prepare the data for the API call
    $data = [
        'commands' => $commandsToSet,
    ];

    $url = "https://api.telegram.org/bot{$token}/setMyCommands";

    // Initialize cURL for sending the request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode the data to JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Execute the request and check for errors
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
    if ($response === false || $httpCode !== 200) {
        echo "Error setting commands: " . curl_error($ch) . " | HTTP Code: " . $httpCode;
    } else {
        echo "Commands set successfully: " . $response;
    }

    // Close the cURL session
    curl_close($ch);
}

// Call the function to set commands every time the bot starts
setCommands($_SESSION['language'], $token);
