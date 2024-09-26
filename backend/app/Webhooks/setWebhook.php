<?php
/* @ROEURNZ=> File name & directory
 * backend/app/Webhooks/setWebhook.php
 * 
 */
// Load configuration
$config = require_once __DIR__ . '/../config/bot_config.php';

use GuzzleHttp\Client;

function setWebhook($botToken, $webhookUrl) {
    $client = new Client();
    $response = $client->request('POST', "https://api.telegram.org/bot{$botToken}/setWebhook", [
        'form_params' => [
            'url' => $webhookUrl,
        ],
    ]);

    return json_decode($response->getBody(), true);
}

// Set the bot token and webhook URL
$botToken = $config['bot_token'] ?? null;
$webhookUrl = 'https://8350-175-100-10-92.ngrok-free.app/app/Webhooks/webhook.php'; /* I use NGROK for test, I will replace it with my actual URL later. */

if ($botToken) {
    $result = setWebhook($botToken, $webhookUrl);
    if ($result['ok']) {
        echo "Webhook set successfully: " . $result['result']['url'];
    } else {
        echo "Failed to set webhook: " . $result['description'];
    }
} else {
    echo "Error: Bot token is not configured properly.";
}
