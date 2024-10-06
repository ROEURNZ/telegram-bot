<?php
require '../vendor/autoload.php'; // Load Composer packages

use GuzzleHttp\Client;
use Telegram\Bot\Api;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

// Create a Guzzle client with SSL verification disabled
$guzzleClient = new Client(['verify' => false]);

// Pass the Guzzle client to the Telegram API
$httpClient = new GuzzleHttpClient($guzzleClient);

// Initialize the Telegram Bot API with the Guzzle client
$telegram = new Api('7508163863:AAEj80XH3zuh1mq-ktfDgAUqSiCpIjF8FKA', false, $httpClient);

// Check bot information
$botInfo = $telegram->getMe();
print_r($botInfo);

// Check for webhooks
$webhookInfo = $telegram->getWebhookInfo();
print_r($webhookInfo); // Check if a webhook is set

// Poll for updates
echo "Polling for updates...\n";
$updates = $telegram->getUpdates(['offset' => -1]); // Use -1 for the latest
print_r($updates); // Check the output

// Handle updates
if (!empty($updates)) {
    foreach ($updates as $update) {
        // Get the message object
        $message = $update->getMessage();

        // Extract details
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        // Respond to the message (for example, echoing back the text)
        $telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "You said: $text"
        ]);
    }
} else {
    echo "No new updates available.\n";
}
