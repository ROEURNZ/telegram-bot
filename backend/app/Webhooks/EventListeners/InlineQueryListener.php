<?php

namespace App\TelegramBot\Webhooks\EventListeners;

use App\TelegramBot\Services\BotService; // Example service for handling bot actions

class InlineQueryListener
{
    protected $botService;

    public function __construct()
    {
        $this->botService = new BotService();
    }

    public function handle(array $inlineQuery): void
    {
        $queryId = $inlineQuery['id'];
        $queryText = $inlineQuery['query'];

        // Process the inline query
        $results = $this->botService->getInlineQueryResults($queryText);

        // Send the results back to Telegram
        $this->botService->sendInlineQueryResults($queryId, $results);
    }
}
