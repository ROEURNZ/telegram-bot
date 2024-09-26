<?php

namespace App\TelegramBot\Webhooks;

use App\TelegramBot\Webhooks\WebhookMiddleware;
use App\TelegramBot\Webhooks\WebhookEvents;
use App\TelegramBot\Webhooks\EventListeners\MessageListener;
use App\TelegramBot\Webhooks\EventListeners\CallbackQueryListener;
use App\TelegramBot\Webhooks\EventListeners\InlineQueryListener;

class WebhookHandler
{
    protected $middleware;
    protected $messageListener;
    protected $callbackQueryListener;
    protected $inlineQueryListener;

    public function __construct()
    {
        $this->middleware = new WebhookMiddleware();
        $this->messageListener = new MessageListener();
        $this->callbackQueryListener = new CallbackQueryListener();
        $this->inlineQueryListener = new InlineQueryListener();
    }

    public function handleUpdate(array $update): void
    {
        if (!$this->middleware->isValid($update)) {
            throw new \Exception("Invalid webhook data");
        }

        // Check for message event
        if (isset($update[WebhookEvents::MESSAGE])) {
            $this->messageListener->handle($update[WebhookEvents::MESSAGE]);
        }

        // Check for callback query event
        if (isset($update[WebhookEvents::CALLBACK_QUERY])) {
            $this->callbackQueryListener->handle($update[WebhookEvents::CALLBACK_QUERY]);
        }

        // Check for inline query event
        if (isset($update[WebhookEvents::INLINE_QUERY])) {
            $this->inlineQueryListener->handle($update[WebhookEvents::INLINE_QUERY]);
        }
    }
}
