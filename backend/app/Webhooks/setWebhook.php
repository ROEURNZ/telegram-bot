<?php
// file name 'setWebhook.php'

include __DIR__ . "/../Config/bot_config.php"; // Ensure the correct path to bot_config.php
$token = $api_key;

// Use the ngrok URL or your actual server URL for the webhook
$webhookUrl = "https://efe0-175-100-10-91.ngrok-free.app/webhook.php";

// Telegram API endpoint for setting the webhook
$setWebhookUrl = "https://api.telegram.org/bot{$token}/setWebhook";

// Parameters to send in the webhook request
$params = [
    'url' => $webhookUrl
];

// Initialize cURL session to send the request
$ch = curl_init($setWebhookUrl); // Correctly initializing the cURL session

// Configure cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

// Disable SSL verification for development (use with caution)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    // Output the response from Telegram
    echo $response;
}

// Close the cURL session
curl_close($ch);
