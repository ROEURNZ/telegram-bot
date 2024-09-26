<?php

namespace App\TelegramBot\Webhooks\EventListeners;

use App\TelegramBot\Services\BotService; // Example service for handling bot actions

class MessageListener
{
    protected $botService;

    public function __construct()
    {
        $this->botService = new BotService();
    }

    public function handle(array $message): void
    {
        // Process the message (e.g., respond, log, etc.)
        $chatId = $message['chat']['id'];
        $text = $message['text'];

        // Example: Send a response based on the message text
        $this->botService->sendMessage($chatId, "You said: $text");
    }
}
