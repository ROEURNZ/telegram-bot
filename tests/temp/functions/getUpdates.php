<?php

// Function to fetch updates from Telegram
function getUpdates($offset, $apiUrl)
{
    $url = $apiUrl . "getUpdates?offset=$offset&timeout=10";
    $response = file_get_contents($url);
    $responseData = json_decode($response, true);

    return $responseData['result'] ?? [];
}
