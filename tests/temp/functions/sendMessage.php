<?php


function sendMessage($chatId, $text, $apiUrl, $replyMarkup = null)
{
    $data = [
        'chat_id' => $chatId,
        'text' => $text,
    ];

    if ($replyMarkup) {
        $data['reply_markup'] = $replyMarkup;
    }

    file_get_contents($apiUrl . "sendMessage?" . http_build_query($data));
}
