<?php 
// include __DIR__ . '/../functions/polling.php';
// Loop to keep the bot running indefinitely
while (true) {
    // Build the URL to fetch updates with the current offset
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=30"; // Set timeout to 30 seconds
    // Initialize cURL to get updates
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    if ($response === false) {
        echo "Error fetching updates: " . curl_error($ch);
        curl_close($ch);
        sleep(1);
        continue;
    }

    curl_close($ch);

    $updates = json_decode($response, true);

    if (isset($updates['result'])) {
        // Process each update (adjust this function based on your bot's logic)
        processUpdates($updates['result'], $token);

        // Update the offset to the ID of the last processed update + 1
        if (!empty($updates['result'])) {
            $lastUpdateId = end($updates['result'])['update_id'];
            $offset = $lastUpdateId + 1;
        }
    }

    // Sleep for a short time to avoid spamming Telegram API
    sleep(1);
}