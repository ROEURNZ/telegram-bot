<?php

// file name 'webhook.php'

include __DIR__ ."/../Config/bot_config.php";
$token = $api_key;

// Get the update sent from Telegram
$update = file_get_contents("php://input");
$updateArray = json_decode($update, true);

// Include necessary files
require_once __DIR__ . '/../Handlers/ComandHandlers.php'; 


// Get the incoming update from Telegram
$updates = json_decode(file_get_contents('php://input'), true);

if ($updates) {
    processUpdates([$updates], $token);
}


