<?php

session_start();
// set_time_limit(300);
date_default_timezone_set("Asia/Phnom_Penh");
// Load configuration and dependencies
require_once __DIR__ . '/../config/bot_config.php';
require_once __DIR__ . '/../Services/HttpClient.php';
require_once __DIR__ . '/../Handlers/CurlHelper.php';

use App\Services\HttpClient;
use App\Handlers\CurlHelper;

// Load and validate bot configuration
$configFilePath = __DIR__ . '/../config/bot_config.php';
if (!file_exists($configFilePath)) {
    die('Error: Configuration file not found.');
}

$config = include($configFilePath);
$botToken = $config['bot_token'] ?? null;
$chatId = $config['chat_id'] ?? null;

if (!$botToken || !$chatId) {
    die('Error: Bot token or chat ID is not configured properly.');
}

// Define the Telegram API URL
$apiUrl = "https://api.telegram.org/bot{$botToken}/";

// Directory to save downloaded images
$imagesPath = __DIR__ . "/../../storage/app/public/images/decoded/";
if (!is_dir($imagesPath)) {
    mkdir($imagesPath, 0777, true);
}

/**
 * @var mixed
 * @access Array to keep track of already decoded files
 */
$decodedFiles = [];

// Counter for file naming
$fileCounter = 1;

// Function to get Telegram updates
function getUpdates($apiUrl) {
    $response = file_get_contents($apiUrl . "getUpdates");
    return json_decode($response, true);
}

// Function to download a file from Telegram
function downloadFile($filePath, $apiUrl, $botToken) {
    $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";
    return file_get_contents($fileUrl) ?: false;
}

// Function to process downloaded images for barcode decoding
function processDownloadedImage($filePath) {
    $decodedCode = @shell_exec("zbarimg --raw " . escapeshellarg($filePath));
    if ($decodedCode === null || trim($decodedCode) === '') {
        return ["error" => "Decoding failed: " . htmlspecialchars(basename($filePath))];
    }
    return [
        'file' => $filePath,
        'code' => trim($decodedCode),
        'barcode_type' => identifyBarcodeType(trim($decodedCode)),
    ];
}

// Function to identify barcode type
function identifyBarcodeType($decodedCode) {
    // Remove any leading or trailing whitespace
    $trimmedCode = trim($decodedCode);
    
    // Check for known barcode formats
    if (strpos($trimmedCode, 'BEGIN:VCARD') !== false) return 'QR Code';
    if (filter_var($trimmedCode, FILTER_VALIDATE_URL)) return 'QR Code (URL)';
    if (preg_match('/^[0-9]{8}$/', $trimmedCode)) return 'EAN-8 Barcode'; // EAN-8
    if (preg_match('/^[0-9]{12}$/', $trimmedCode)) return 'UPC Barcode'; // UPC (12-digit)
    if (preg_match('/^[0-9A-Z\-]{2,30}$/', $trimmedCode)) return 'Code 39 Barcode'; // Code 39
    if (preg_match('/^[0-9A-Z\-]{1,30}$/', $trimmedCode)) return 'Code 93 Barcode'; // Code 93
    if (preg_match('/^([0-9]{1,12}|[0-9]{1,5}(\s|[0-9]{1,5}){0,1})$/', $trimmedCode)) return 'Code 128 Barcode'; // Code 128
    if (preg_match('/^[A-B0-9]+$/', $trimmedCode)) return 'Codabar Barcode'; // Codabar
    if (preg_match('/^[0-9]{2,10}$/', $trimmedCode)) return 'ITF Barcode'; // ITF
    if (preg_match('/^[0-9]{1,10}$/', $trimmedCode)) return 'MSI Barcode'; // MSI
    if (preg_match('/^[0-9]{1,10}$/', $trimmedCode)) return 'Pharmacode Barcode'; // Pharmacode

    return 'Unknown Barcode Type';
}

// Function to validate the decoded result
function isValidDecodedResult($code) {
    // Define validation rules for decoded results
    return !empty($code); // Add more rules as needed
}

// Function to echo results with formatting
function echoResults($fileName, $fullPath, $result, $timestamp, $imageId, $httpClient) {
    // Get file size
    $fileSize = filesize($fullPath); // Get the file size in bytes
    $fileSizeFormatted = number_format($fileSize / 1024, 2) . ' KB'; // Convert to KB and format

    // Prepare the message to send to Telegram
    $message = "File Name: " . htmlspecialchars($fileName) . "\n" .
               "Decoded Code: " . htmlspecialchars($result['code']) . "\n" .
               "Barcode Type: " . htmlspecialchars($result['barcode_type']) . "\n" .
               "Timestamp: " . htmlspecialchars($timestamp) . "\n" .
               "Image ID: " . htmlspecialchars($imageId) . "\n" .
               "File Size: " . htmlspecialchars($fileSizeFormatted) . "\n" . // Add file size to message
               "Decoded Info: [View Image](https://github.com/ROEURNZ/telegram-bot/tree/master/backend/storage/app/public/images/decoded/" . basename($fullPath) . ")"; // Replace with your public URL

    // Output the results in a formatted way
    echo "<div class='result'>";
    echo "<h3>Decoded Result for: " . htmlspecialchars($fileName) . "</h3>";
    echo "<p>Decoded Code: " . htmlspecialchars($result['code']) . "</p>";
    echo "<p>Barcode Type: " . htmlspecialchars($result['barcode_type']) . "</p>";
    echo "<p>Timestamp: " . htmlspecialchars($timestamp) . "</p>";
    echo "<p>Image ID: " . htmlspecialchars($imageId) . "</p>";
    echo "<p>File Size: " . htmlspecialchars($fileSizeFormatted) . "</p>"; // Display file size

    // Send the message to Telegram via the HttpClient
    $messageSent = $httpClient->sendMessage($message);

    if ($messageSent) {
        echo "<p>Message sent to Telegram successfully!</p>";
    } else {
        echo "<p>Failed to send message to Telegram.</p>";
    }

    echo "</div>";
}


// Fetch the latest updates from Telegram
$updates = getUpdates($apiUrl);

if (isset($updates['result'])) {
    $decodedResults = [];
    $httpClient = new HttpClient($botToken, $chatId);

    foreach ($updates['result'] as $update) {
        // Handle photo messages
        if (isset($update['message']['photo'])) {
            $photo = end($update['message']['photo']);
            $fileId = $photo['file_id'];
            $fileResponse = file_get_contents($apiUrl . "getFile?file_id={$fileId}");
            $fileInfo = json_decode($fileResponse, true);

            if ($fileInfo['ok']) {
                $filePath = $fileInfo['result']['file_path'];
                $fileData = downloadFile($filePath, $apiUrl, $botToken);
                if ($fileData !== false) {
                    // Use the counter for file naming
                    $fileName = "barcode-" . $fileCounter++ . ".jpg"; // Incrementing counter for each new file
                    $fullPath = $imagesPath . basename($fileName); // Use basename to sanitize

                    if (file_put_contents($fullPath, $fileData) !== false) {
                        // Check if the image has already been decoded
                        if (in_array($fullPath, $decodedFiles)) {
                            echo "<p class='text-yellow-500'>The image <strong>" . htmlspecialchars($fileName) . "</strong> has already been decoded.</p>";
                            continue; // Skip this result
                        }

                        // Process the image for decoding
                        $result = processDownloadedImage($fullPath);
                        if (isset($result['error'])) {
                            echo "<p class='text-red-600'>" . htmlspecialchars($result['error']) . "</p>";
                        } else {
                            // Validate decoded result before storing it
                            if (!isValidDecodedResult($result['code'])) {
                                echo "<p class='text-red-600'>Invalid decoded result: " . htmlspecialchars($result['code']) . "</p>";
                                continue; // Skip this result
                            }
                            $decodedResults[] = $result;
                            $decodedFiles[] = $fullPath; // Add to the decoded files array
                            $timestamp = date('Y-m-d H:i:s');
                            $imageId = uniqid();
                            echoResults($fileName, $fullPath, $result, $timestamp, $imageId, $httpClient);
                        }
                    } else {
                        echo "Failed to save photo data for file_id (Photo): " . htmlspecialchars($fileId) . "\n";
                    }
                } else {
                    echo "Failed to download photo data.\n";
                }
            } else {
                echo "Failed to get file info for file_id (Photo): " . htmlspecialchars($fileId) . "\n";
            }
        }
    }
} else {
    echo "No updates found or error fetching updates.";
}
/*
echo "<pre>";
print_r($decodedFiles); // Check what files are currently tracked
echo "</pre>";
*/