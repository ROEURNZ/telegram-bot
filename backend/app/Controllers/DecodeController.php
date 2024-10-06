<?php

/**
 * Start a session and set the maximum execution time for the script.
 */
session_start();
set_time_limit(120); // 2 minutes is equal 120 seconds, 60 seconds x n = 
date_default_timezone_set("Asia/Phnom_Penh");

/**
 * Load configuration and service files necessary for the bot.
 */
require_once __DIR__ . '/../config/bot_config.php';

/**
 * Define default validation messages in case they are not loaded from localization.
 */
$validationMessages = [
    'required' => 'Required field missing.',
    'invalid_file_type' => 'Invalid file type',
    'file_too_large' => 'File is too large',
    'failed_to_save_image' => 'Failed to save image',
    'image_upload_failed' => 'Image upload failed',
    'decoding_failed' => 'Failed to decode the image',
    'failed_to_send_image' => 'Failed to send image',
    'no_images_uploaded' => 'No images were uploaded',
];

/**
 * Load the bot configuration and validate it.
 */
$configFilePath = '../config/bot_config.php';
if (!file_exists($configFilePath)) {
    die($validationMessages['required'] ?: 'Error: Configuration file not found.');
}

$config = include($configFilePath);
$botToken = $config['bot_token'] ?? null;
$chatId = $config['chat_id'] ?? null;

if (!$botToken || !$chatId) {
    die($validationMessages['required'] ?: 'Error: Bot token or chat ID is not configured properly.');
}

/**
 * Set the directory path for storing uploaded images.
 */
$imagesPath = __DIR__ . "../../../storage/app/public/images/decoded/";
if (!is_dir($imagesPath)) {
    mkdir($imagesPath, 0777, true); // Create directory if it does not exist
}

/**
 * Identify the type of barcode based on the decoded content.
 *
 * @param string $decodedCode The decoded barcode content.
 * @return string The type of barcode identified.
 */
function identifyBarcodeType($decodedCode)
{
    if (strpos($decodedCode, 'BEGIN:VCARD') !== false) {
        return 'QR Code';
    } elseif (filter_var($decodedCode, FILTER_VALIDATE_URL)) {
        return 'QR Code (URL)';
    } elseif (preg_match('/^[0-9]{12,13}$/', $decodedCode)) {
        return 'EAN/UPC Barcode';
    } elseif (preg_match('/^[0-9]{14}$/', $decodedCode)) {
        return 'ITF-14 Barcode';
    } elseif (preg_match('/^[0-9]{8}$/', $decodedCode)) {
        return 'EAN8 Barcode';
    } elseif (preg_match('/^[0-9]{12}$/', $decodedCode)) {
        return 'UPC Barcode';
    } elseif (preg_match('/^[A-Z0-9\-\.\ \$\/\+\%]{1,43}$/', $decodedCode)) {
        return 'Code 39 Barcode';
    } elseif (preg_match('/^[A-Z0-9\-\.\ \$\/\+\%]{1,47}$/', $decodedCode)) {
        return 'Code 93 Barcode';
    } elseif (preg_match('/^[\x20-\x7E]{1,}$/', $decodedCode)) {
        return 'Code 128 Barcode';
    } elseif (preg_match('/^[A-D0-9\-\.\ \$\/\+\%]{1,}$/', $decodedCode)) {
        return 'Codabar Barcode';
    } elseif (preg_match('/^[0-9]{2,}$/', $decodedCode)) {
        return 'ITF Barcode';
    } elseif (preg_match('/^[0-9]{1,}$/', $decodedCode)) {
        return 'MSI Barcode';
    } elseif (preg_match('/^[0-9]{1,}$/', $decodedCode)) {
        return 'Pharmacode Barcode';
    } else {
        return 'Unknown Barcode Type';
    }
}

/**
 * Download the file from Telegram based on the file ID.
 *
 * @param string $botToken The Telegram bot token.
 * @param string $fileId The ID of the file to download.
 * @param string $savePath The path to save the downloaded file.
 * @return bool True on success, false on failure.
 */
function downloadTelegramFile($botToken, $fileId, $savePath)
{
    // Get file info from Telegram
    $fileInfoUrl = "https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}";
    $fileInfoResponse = file_get_contents($fileInfoUrl);
    $fileInfo = json_decode($fileInfoResponse, true);

    if (isset($fileInfo['result']['file_path'])) {
        $filePath = $fileInfo['result']['file_path'];
        $downloadUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

        // Download and save the file
        return file_put_contents($savePath, file_get_contents($downloadUrl)) !== false;
    }

    return false; // Failed to get file path
}

/**
 * Send an image to the Telegram bot.
 *
 * @param string $botToken The Telegram bot token.
 * @param string $chatId The chat ID to send the photo.
 * @param string $filePath The path of the image to send.
 * @param string $message The caption for the photo.
 * @return bool True on success, false on failure.
 */
function sendImage($botToken, $chatId, $filePath, $message): bool
{
    $sendPhotoUrl = "https://api.telegram.org/bot{$botToken}/sendPhoto";
    $postData = [
        'chat_id' => $chatId,
        'caption' => $message,
        'photo' => new \CURLFile(realpath($filePath)),
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Enable SSL verification

    $response = curl_exec($ch);

    if ($response === false) {
        error_log('CURL Error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return true;
}


/**
 * Send a message to the Telegram bot.
 *
 * @param string $botToken The Telegram bot token.
 * @param string $chatId The chat ID to send the message.
 * @param string $message The message to send.
 * @return bool True on success, false on failure.
 */
function sendMessage($botToken, $chatId, $message): bool
{
    $sendMessageUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sendMessageUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Enable SSL verification

    $response = curl_exec($ch);

    if ($response === false) {
        error_log('CURL Error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    curl_close($ch);
    return true;
}

/**
 * Polling function to get updates from Telegram.
 *
 * @param string $botToken The Telegram bot token.
 * @param int $offset The update offset for polling.
 * @return array The updates received from Telegram.
 */
function getUpdates($botToken, $offset = 0)
{
    $url = "https://api.telegram.org/bot{$botToken}/getUpdates?offset={$offset}&timeout=60";
    $response = file_get_contents($url);
    return json_decode($response, true);
}

/**
 * Main polling loop
 */
$offset = 0;

$imageCounter = 1; // Initialize a counter for the image names

while (true) {
    $updates = getUpdates($botToken, $offset);

    if (isset($updates['result'])) {
        foreach ($updates['result'] as $update) {
            $offset = $update['update_id'] + 1; // Update offset for the next request

            if (isset($update['message']['photo'])) {
                $photo = $update['message']['photo'];
                $fileId = end($photo)['file_id']; // Get the highest resolution photo

                // Determine the original file extension
                $fileExtension = 'jpg'; // Default extension if not known (could be modified based on your needs)

                // Set the save path with the incrementing number and original extension
                $savePath = $imagesPath . 'barcode-' . $imageCounter . '.' . $fileExtension;

                // Only send the waiting message if this is the first image download
                if ($imageCounter === 1) {
                    sendMessage($botToken, $chatId, "Please wait a moment, the bot is downloading the image.");
                }

                // Download the image from Telegram
                if (downloadTelegramFile($botToken, $fileId, $savePath)) {
                    // Decode the barcode from the downloaded image
                    $decodedCode = @shell_exec("zbarimg --raw " . escapeshellarg($savePath));
                    if ($decodedCode === null || trim($decodedCode) === '') {
                        echo "<p class='text-red-600'>" . $validationMessages['decoding_failed'] . " " . htmlspecialchars(basename($savePath)) . "</p>";
                        continue;
                    }

                    $barcodeType = identifyBarcodeType(trim($decodedCode));
                    $fileSizeInKb = round(filesize($savePath) / 1024, 2);
                    $message = "Downloaded Image\n" .
                        "File Name: " . htmlspecialchars(basename($savePath)) . "\n" .
                        "File Size: {$fileSizeInKb} KB\n" .
                        "Decoded Code: " . htmlspecialchars(trim($decodedCode)) . "\n" .
                        "Barcode Type: " . htmlspecialchars($barcodeType);

                    // Send the image and decoded information to the Telegram bot
                    if (!sendImage($botToken, $chatId, $savePath, $message)) {
                        echo "<p class='text-red-600'>" . $validationMessages['failed_to_send_image'] . "</p>";
                    }

                    $imageCounter++; // Increment the counter for the next image
                } else {
                    echo "<p class='text-red-600'>" . $validationMessages['image_upload_failed'] . "</p>";
                }
            }
        }
    }

    // Sleep for a short duration to prevent overwhelming the API
    usleep(500000); // Sleep for 0.5 seconds
}
