<?php

namespace App\TelegramBot\Webhooks;

class WebhookMiddleware
{
    public function isValid(array $update): bool
    {
        // Implement validation logic (e.g., token validation, signature verification)
        // Example: Check for required fields in the update
        return isset($update['update_id']);
    }
}
