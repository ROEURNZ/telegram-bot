<?php

$config = include __DIR__ .  '/../../Config/api_key.php';
$token = $api_key;
$offset = 0;

while (true) {
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=30";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
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
        processUpdates($updates['result'], $token);

        if (!empty($updates['result'])) {
            $lastUpdateId = end($updates['result'])['update_id'];
            $offset = $lastUpdateId + 1;
        }
    }

    sleep(1);
}



/*
 <?php

*function pollTelegramUpdates($token, $offset = 0) {
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=30";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error fetching updates: " . curl_error($ch);
        curl_close($ch);
        sleep(1);
        return pollTelegramUpdates($token, $offset);
    }
    curl_close($ch);
    $updates = json_decode($response, true);
    if (isset($updates['result'])) {
        processUpdates($updates['result'], $token);
        if (!empty($updates['result'])) {
            $lastUpdateId = end($updates['result'])['update_id'];
            $offset = $lastUpdateId + 1;
        }
    }
    sleep(1);
    return pollTelegramUpdates($token, $offset);
}
pollTelegramUpdates($token);

 */