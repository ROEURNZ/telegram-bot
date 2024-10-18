<?php

/**
 * Start a session and set the maximum execution time for the script.
 */
session_start();
set_time_limit(300);
date_default_timezone_set("Asia/Phnom_Penh");

/**
 * Load configuration and service files necessary for the bot.
 */
require_once __DIR__ . '/../config/bot_config.php';
$botToken = $api_key;
var_dump($botToken);
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



// Set initial offset to fetch updates starting from the first one
$lastUpdateId = 0;

while (true) {
    // Construct the URL with the offset to get new updates
    $url = "https://api.telegram.org/bot$botToken/getUpdates?offset=" . ($lastUpdateId + 1);

    // Initialize cURL to fetch updates
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a 30-second timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification (not recommended in production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Execute the cURL request
    $updates = curl_exec($ch);

    // Check if there was an error
    if ($updates === false) {
        echo "Failed to fetch updates. cURL error: " . curl_error($ch) . "\n";
        curl_close($ch);
        break; // Exit the loop if the request fails
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the response from JSON
    $updatesArray = json_decode($updates, true);

    // Check if there are new updates in the result
    if (isset($updatesArray['result']) && !empty($updatesArray['result'])) {
        foreach ($updatesArray['result'] as $update) {
            // Check if the update contains a message
            if (isset($update['message'])) {
                $message = $update['message'];
                $userId = $message['from']['id'];
                echo "User ID: " . $userId . "\n";

                // Update the last processed update ID
                $lastUpdateId = $update['update_id'];
            }
        }
    } else {
        // No updates received
        echo "No new updates.\n";
    }

    // Sleep for 2 seconds to avoid hitting Telegram's rate limits
    sleep(2);
}

/**
 * Load the bot configuration and validate it.
 */
$configFilePath = '../config/bot_config.php';
if (!file_exists($configFilePath)) {
    die($validationMessages['required'] ?: 'Error: Configuration file not found.');
}

$config = include($configFilePath);
$botToken = $api_key;
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
 * Process the uploaded image: validate, save, and return the results.
 *
 * @param array $image The uploaded image details.
 * @param string $imagesPath The path to save the image.
 * @param array $validationMessages The localization messages for validation.
 * @return array The result of the image processing.
 */
function processUploadedImage($image, $imagesPath, $validationMessages)
{
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

    $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $mimeType = mime_content_type($image['tmp_name']);

    if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
        return ["error" => $validationMessages['invalid_file_type'] . ": " . htmlspecialchars($image['name'])];
    }

    if ($image['size'] > 5 * 1024 * 1024) {
        return ["error" => $validationMessages['file_too_large'] . ": " . htmlspecialchars($image['name'])];
    }

    $fileName = pathinfo($image['name'], PATHINFO_FILENAME);
    $filePath = $imagesPath . $fileName . '.' . $fileExtension;

    // Ensure a unique file name if the file already exists
    $fileCounter = 1;
    while (file_exists($filePath)) {
        $filePath = $imagesPath . $fileName . '-' . $fileCounter++ . '.' . $fileExtension;
    }

    if (!move_uploaded_file($image['tmp_name'], $filePath)) {
        return ["error" => $validationMessages['failed_to_save_image'] . ": " . htmlspecialchars($fileName)];
    }

    return ["path" => $filePath, "name" => basename($filePath), "size" => $image['size'], "type" => $mimeType];
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
 * Handle image uploads and decoding.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    // Check for upload errors
    if ($_FILES['images']['error'][0] !== UPLOAD_ERR_OK) {
        die($validationMessages['image_upload_failed'] ?: 'Error: Image upload failed.');
    }

    $decodedResults = [];
    $uploadedImages = $_FILES['images'];

    // Process each uploaded image
    foreach ($uploadedImages['tmp_name'] as $key => $tmpName) {
        $image = [
            'name' => $uploadedImages['name'][$key],
            'tmp_name' => $tmpName,
            'error' => $uploadedImages['error'][$key],
            'size' => $uploadedImages['size'][$key],
        ];

        $result = processUploadedImage($image, $imagesPath, $validationMessages);
        if (isset($result['error'])) {
            echo "<p class='text-red-600'>" . htmlspecialchars($result['error']) . "</p>";
            continue;
        }

        // Decode the barcode from the image
        $decodedCode = @shell_exec("zbarimg --raw " . escapeshellarg($result['path']));
        if ($decodedCode === null || trim($decodedCode) === '') {
            echo "<p class='text-red-600'>" . $validationMessages['decoding_failed'] . " " . htmlspecialchars($result['name']) . "</p>";
            continue;
        }

        $barcodeType = identifyBarcodeType(trim($decodedCode));
        $decodedResults[] = [
            'file' => $result['path'],
            'code' => trim($decodedCode),
            'name' => $result['name'],
            'size' => $result['size'],
            'type' => $result['type'],
            'barcode_type' => $barcodeType,
        ];

        $fileSizeInKb = round($result['size'] / 1024, 2);
        $message = "Uploaded Image\n" .
            "File Name: " . $result['name'] . "\n" .
            "File Size: " . $fileSizeInKb . " KB\n" .
            "File Type: " . $result['type'] . "\n" .
            "Decoded Info: " . trim($decodedCode) . "\n" .
            "Decode Image Type: " . $barcodeType;

        // Send the decoded information and image to the Telegram bot
        if (!sendImage($botToken, $chatId, $result['path'], $message)) {
            echo "<p class='text-red-600'>" . $validationMessages['failed_to_send_image'] . "</p>";
        } else {
            echo "<p class='text-green-600'>Image sent successfully!</p>";
        }
    }

    // Store decoded results in session and redirect to the decode view
    $_SESSION['decodedResults'] = $decodedResults;
    header('Location: ../../public/views/DecodeView.php');
    exit;
} else {
    // Handle case when no images are uploaded
    die($validationMessages['no_images_uploaded'] ?: "No images uploaded.");
}
