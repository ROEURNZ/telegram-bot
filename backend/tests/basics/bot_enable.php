<?php
// Your Telegram bot token
$botToken = "7520890760:AAHcSkbK-vtsJqasnHQi_VRn5qC5s4olKKE";

// Telegram API URL for getMe method
$url = "https://api.telegram.org/bot$botToken/getMe";

// Send a GET request to the Telegram API
$response = file_get_contents($url);

// Decode the JSON response
$botInfo = json_decode($response, true);

// Check if the request was successful
if ($botInfo['ok']) {
    // Extract bot information
    $botId = $botInfo['result']['id'];
    $botUsername = $botInfo['result']['username'];
    $canReadAllGroupMessages = $botInfo['result']['can_read_all_group_messages'];
    $supportsInlineQueries = $botInfo['result']['supports_inline_queries'];
    $canConnectToBusiness = $botInfo['result']['can_connect_to_business'];
    $hasMainWebApp = $botInfo['result']['has_main_web_app'];
    
    // Display bot information
    echo "Bot ID: " . $botId . PHP_EOL;
    echo "Bot Username: @" . $botUsername . PHP_EOL;
    echo "Can Read All Group Messages: " . ($canReadAllGroupMessages ? 'true' : 'false') . PHP_EOL;
    echo "Supports Inline Queries: " . ($supportsInlineQueries ? 'true' : 'false') . PHP_EOL;
    echo "Can Connect to Business: " . ($canConnectToBusiness ? 'true' : 'false') . PHP_EOL;
    echo "Has Main Web App: " . ($hasMainWebApp ? 'true' : 'false') . PHP_EOL;
} else {
    // Display error message
    echo "Failed to get bot info. Error: " . $botInfo['description'];
}
?>
