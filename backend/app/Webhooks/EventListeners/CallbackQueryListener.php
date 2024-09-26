<?php

namespace App\TelegramBot\Webhooks\EventListeners;

use App\TelegramBot\Services\BotService; // Example service for handling bot actions

class CallbackQueryListener
{
    protected $botService;

    public function __construct()
    {
        $this->botService = new BotService();
    }

    public function handle(array $callbackQuery): void
    {
        $callbackData = $callbackQuery['data'];
        $chatId = $callbackQuery['message']['chat']['id'];

        // Process the callback data
        $this->botService->handleCallback($chatId, $callbackData);
    }
}
