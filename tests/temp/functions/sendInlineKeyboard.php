<?php

// Function to send a message with an inline keyboard
function sendInlineKeyboard($chatId, $apiUrl)
{
    $url = $apiUrl . "sendMessage";

    $keyboard = [
        "inline_keyboard" => [
            [
                ["text" => "Yes", "callback_data" => "yes"],
                ["text" => "No", "callback_data" => "no"]
            ]
        ]
    ];

    $data = [
        'chat_id' => $chatId,
        'text' => "Do you want to proceed?",
        'reply_markup' => json_encode($keyboard)
    ];

    file_get_contents($url . "?" . http_build_query($data));
}
