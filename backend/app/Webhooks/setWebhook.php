<?php
// file name 'setWebhook.php'

include __DIR__ ."/webhook.php";
include __DIR__ ."/../Config/bot_config.php";
$token = $api_key;

$webhookUrl = "https://efe0-175-100-10-91.ngrok-free.app/webhook.php";

// Telegram API endpoint for setting the webhook
$url = "https://api.telegram.org/bot{$token}/setWebhook?url={$webhookUrl}";

// Initialize cURL to set the webhook
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);

if ($response === false) {
    echo "Error setting webhook: " . curl_error($ch);
} else {
    $result = json_decode($response, true);
    if ($result['ok']) {
        echo "Webhook set successfully!";
    } else {
        echo "Failed to set webhook: " . $result['description'];
    }
}

curl_close($ch);
