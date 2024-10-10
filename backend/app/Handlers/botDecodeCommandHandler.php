<?php

session_start();
set_time_limit(300);
date_default_timezone_set("Asia/Phnom_Penh");

// Load configuration and dependencies
$config = require __DIR__ . '/../config/bot_config.php';
$botToken = $config['bot_token'] ?? null;
$chatId= $config['chat_id'] ?? null; 


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





if (isset($updates['result'])) {
    $decodedResults = [];

    foreach ($updates['result'] as $update) {
        // Handle commands
        if (isset($update['message']['text'])) {
            $text = trim($update['message']['text']);
            $chatId = $update['message']['chat']['id'];

            // Handle /decode command
            if ($text === '/decode') {
                echoResults("Decode Request", "", ["code" => "Please send a barcode image to decode."], date("Y-m-d H:i:s"), $update['message']['message_id'], $botToken, $chatId);
                continue;
            }
        }

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
                    $fileName = "barcode-" . $fileCounter++ . ".jpg";
                    $fullPath = $imagesPath . basename($fileName);

                    if (file_put_contents($fullPath, $fileData) !== false) {
                        $result = processDownloadedImage($fullPath);

                        if (isValidDecodedResult($result['code'])) {
                            echoResults($fileName, $fullPath, $result, date("Y-m-d H:i:s"), $update['message']['message_id'], $botToken, $chatId);
                        } else {
                            echoResults($fileName, $fullPath, ["error" => "Decoding failed."], date("Y-m-d H:i:s"), $update['message']['message_id'], $botToken, $chatId);
                        }
                    } else {
                        echoResults("File Save Error", "", ["error" => "Failed to save the file."], date("Y-m-d H:i:s"), $update['message']['message_id'], $botToken, $chatId);
                    }
                } else {
                    echoResults("File Download Error", "", ["error" => "Failed to download the file."], date("Y-m-d H:i:s"), $update['message']['message_id'], $botToken, $chatId);
                }
            } else {
                echoResults("File Info Error", "", ["error" => "Failed to retrieve file info."], date("Y-m-d H:i:s"), $update['message']['message_id'], $botToken, $chatId);
            }
        }
    }
} else {
    echo "Error fetching updates.";
}


/**
 * Array to keep track of already decoded files
 * @var mixed
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
    $trimmedCode = trim($decodedCode);
    // Check for known barcode formats
    if (strpos($trimmedCode, 'BEGIN:VCARD') !== false) return 'QR Code';
    if (filter_var($trimmedCode, FILTER_VALIDATE_URL)) return 'QR Code (URL)';
    if (preg_match('/^[0-9]{8}$/', $trimmedCode)) return 'EAN-8 Barcode';
    if (preg_match('/^[0-9]{12}$/', $trimmedCode)) return 'UPC Barcode';
    if (preg_match('/^[0-9A-Z\-]{2,30}$/', $trimmedCode)) return 'Code 39 Barcode';
    if (preg_match('/^[0-9A-Z\-]{1,30}$/', $trimmedCode)) return 'Code 93 Barcode';
    if (preg_match('/^([0-9]{1,12}|[0-9]{1,5}(\s|[0-9]{1,5}){0,1})$/', $trimmedCode)) return 'Code 128 Barcode';
    if (preg_match('/^[A-B0-9]+$/', $trimmedCode)) return 'Codabar Barcode';
    if (preg_match('/^[0-9]{2,10}$/', $trimmedCode)) return 'ITF Barcode';
    if (preg_match('/^[0-9]{1,10}$/', $trimmedCode)) return 'MSI Barcode';
    if (preg_match('/^[0-9]{1,10}$/', $trimmedCode)) return 'Pharmacode Barcode';

    return 'Unknown Barcode Type';
}

// Function to validate the decoded result
function isValidDecodedResult($code) {
    return !empty($code);
}

// Function to echo results with formatting
function echoResults($fileName, $fullPath, $result, $timestamp, $imageId, $botToken, $chatId) {
    // Get file size
    $fileSize = filesize($fullPath);
    $fileSizeFormatted = number_format($fileSize / 1024, 2) . ' KB';

    // Prepare the message to send to Telegram
    $message = "File Name: " . htmlspecialchars($fileName) . "\n" .
               "Decoded Code: " . htmlspecialchars($result['code']) . "\n" .
               "Barcode Type: " . (isset($result['barcode_type']) ? htmlspecialchars($result['barcode_type']) : "Unknown") . "\n" .
               "Timestamp: " . htmlspecialchars($timestamp) . "\n" .
               "Image ID: " . htmlspecialchars($imageId) . "\n" .
               "File Size: " . htmlspecialchars($fileSizeFormatted) . "\n" .
               "Decoded Info: [View Image](https://github.com/ROEURNZ/telegram-bot/tree/master/backend/storage/app/public/images/decoded/" . basename($fullPath) . ")";

    // Output the results in a formatted way
    echo "<div class='result'>";
    echo "<h3>Decoded Result for: " . htmlspecialchars($fileName) . "</h3>";
    echo "<p>Decoded Code: " . htmlspecialchars($result['code']) . "</p>";
    echo "<p>Barcode Type: " . (isset($result['barcode_type']) ? htmlspecialchars($result['barcode_type']) : "Unknown") . "</p>";
    echo "<p>Timestamp: " . htmlspecialchars($timestamp) . "</p>";
    echo "<p>Image ID: " . htmlspecialchars($imageId) . "</p>";
    echo "<p>File Size: " . htmlspecialchars($fileSizeFormatted) . "</p>";

    // Send the message to Telegram
    $sendPhotoUrl = "https://api.telegram.org/bot{$botToken}/sendPhoto"; 
    $postData = [
        'chat_id' => $chatId, 
        'caption' => $message,       
        'photo' => new \CURLFile(realpath($fullPath)), 
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl); 
    curl_setopt($ch, CURLOPT_POST, true);          
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for HTTPS://

    $response = curl_exec($ch);
    
    if ($response === false) {
        error_log('CURL Error: ' . curl_error($ch)); 
        curl_close($ch); 
        echo "<p>Failed to send message to Telegram.</p>";
    } else {
        echo "<p>Message sent to Telegram successfully!</p>";
    }

    curl_close($ch); 
    echo "</div>";
}

