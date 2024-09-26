<?php
// backend/app/Commands/setMyCommands.php

// Load configuration
$config = require '../config/bot_config.php';

// Function to set commands for the bot
function setCommands($token) {
    $commands = [
        ['command' => 'start', 'description' => 'Start the bot'],
        ['command' => 'help', 'description' => 'Get help'],
        ['command' => 'menu', 'description' => 'Show menu options'],
    ];

    $data = ['commands' => $commands];
    $url = "https://api.telegram.org/bot{$token}/setMyCommands";

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

    // Execute the request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if ($response === false) {
        echo "cURL error: " . curl_error($ch);
        curl_close($ch);
        return null;
    }

    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($responseCode !== 200) {
        echo "Error: HTTP response code $responseCode";
        return null;
    }

    return json_decode($response, true);
}

// Set commands for the bot
$response = setCommands($config['bot_token']);

// Handle the response
if ($response) {
    if (isset($response['ok']) && $response['ok']) {
        echo "Commands set successfully!";
    } else {
        echo "Error setting commands: " . ($response['description'] ?? 'Unknown error');
    }
} else {
    echo "Failed to set commands due to previous errors.";
}

