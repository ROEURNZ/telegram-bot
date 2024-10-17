<?php
// File name: webhook.php

include __DIR__ . "/../Config/bot_config.php"; 
$token = $api_key;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the request method is POST and Content-Type is not application/json
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    error_log("Incorrect Content-Type: " . $_SERVER['CONTENT_TYPE']);
    http_response_code(400); // Respond with a 400 Bad Request status
    exit; // Exit the script to prevent further execution
}

// Get the raw POST data
$update = file_get_contents("php://input"); 

// Log the raw update data and headers for debugging
file_put_contents('webhook.log', "Raw input: " . $update . PHP_EOL, FILE_APPEND);
file_put_contents('webhook.log', "Headers: " . json_encode(getallheaders()) . PHP_EOL, FILE_APPEND); 

// Decode the JSON data into an associative array
$updateArray = json_decode($update, true);

// Check for JSON errors
if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents('webhook.log', "JSON Decode Error: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
}

// Log the decoded update array
file_put_contents('webhook.log', "Decoded update: " . print_r($updateArray, true) . PHP_EOL, FILE_APPEND); 

// Check if the update is valid and contains an update_id
if ($updateArray && isset($updateArray['update_id'])) {
    require_once __DIR__ . '/../Handlers/CommandHandlers.php'; 
    
    // Process the update
    processUpdates([$updateArray], $token); 
    
    // Send a 200 OK response to Telegram
    http_response_code(200);
    echo "OK"; 
} else {
    // Invalid update received
    http_response_code(400);
    echo "Invalid update";
}
