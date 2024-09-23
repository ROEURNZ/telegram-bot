<?php

namespace App\Services;

class HttpClient
{
    private string $botToken;
    private string $chatId;

    public function __construct(string $botToken, string $chatId)
    {
        $this->botToken = $botToken;
        $this->chatId = $chatId;
    }

    public function sendImage(string $filePath, string $message): bool
    {
        $sendPhotoUrl = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";
        $postData = [
            'chat_id' => $this->chatId,
            'caption' => $message,
            'photo' => new \CURLFile(realpath($filePath)),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log('CURL Error: ' . curl_error($ch));
            return false;
        }
        
        curl_close($ch);
        return true;
    }
}
