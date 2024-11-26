<?php

require_once 'Config/config.php';
include 'functions/getUpdates.php';
include 'functions/sendInlineKeyboard.php';
include 'functions/showContactSharing.php';
include 'functions/sendMessage.php';

$offset = 0;

while (true) {
    // Get updates
    $updates = getUpdates($offset, $apiUrl);

    foreach ($updates as $update) {
        $offset = $update['update_id'] + 1;

        // Handle text messages
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];

            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                if ($text === "/start") {
                    // Send a message with an inline keyboard
                    sendInlineKeyboard($chatId, $apiUrl);
                } elseif ($text === "/share_contact") {
                    // Show contact sharing prompt
                    showContactSharing($chatId, 'en', $baseLanguage, $botToken);
                }
            }
        }

        // Handle callback queries
        if (isset($update['callback_query'])) {
            $callbackData = $update['callback_query']['data'];
            $chatId = $update['callback_query']['message']['chat']['id'];

            if ($callbackData === "yes") {
                showContactSharing($chatId, 'en', $baseLanguage, $botToken);
            } elseif ($callbackData === "no") {
                sendMessage($chatId, "You skipped sending your contact.", $apiUrl);
            }
        }
    }

    sleep(1);
}
