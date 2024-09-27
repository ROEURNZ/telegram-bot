<?php
// backend/app/Handlers/handle_commands.php
// Load configuration
$config = require '../config/bot_config.php';
set_time_limit(300); // Allow the script to run indefinitely
session_start();
// Function to send messages
function sendMessage($chatId, $message, $token, $replyMarkup = null) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
    ];

    if ($replyMarkup) {
        $data['reply_markup'] = $replyMarkup;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    
    if ($response === false) {
        echo "Error sending message: " . curl_error($ch);
    }

    curl_close($ch);
}


// Function to process updates
function processUpdates($updates, $token) {
    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];

            // Handle text messages
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                switch ($text) {
                    case '/start':
                        sendMessage($chatId, "Welcome to your Bot!", $token);
                        
                        $replyMarkup = json_encode([
                            'keyboard' => [
                                [['text' => '🇺🇸 English']],
                                [['text' => '🇰🇭 ភាសាខ្មែរ']]
                            ],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, "Please choose your language:", $token, $replyMarkup);
                        break;

                    case '🇺🇸 English':
                    case '🇰🇭 ភាសាខ្មែរ':
                        $contactMarkup = json_encode([
                            'keyboard' => [[['text' => 'Share My Contact', 'request_contact' => true]]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, "Please share your contact information.", $token, $contactMarkup);
                        break;

                    case '/help':
                        sendMessage($chatId, "This is your help message. You can use /start to begin.", $token);
                        break;

                    case '/menu':
                        sendMessage($chatId, "Menu options: /start, /help", $token);
                        break;
                }
            }

            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";

                $responseMessage = "Thanks for sharing your contact!\n";
                $responseMessage .= "Full Name: {$firstName} {$lastName}\n";
                $responseMessage .= "Phone Number: {$phoneNumber}\n";
                $responseMessage .= "Username: {$username}";

                sendMessage($chatId, $responseMessage, $token);
                sendMessage($chatId, "Please upload a barcode or QR code image.", $token);
            }

            // Handle image upload (barcode/QR code)
            if (isset($update['message']['photo'])) {
                $fileId = end($update['message']['photo'])['file_id'];

                // Retrieve the file data
                $fileData = file_get_contents("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");
                $fileData = json_decode($fileData, true);

                if (isset($fileData['result']['file_path'])) {
                    $filePath = $fileData['result']['file_path'];
                    $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

                    // Download the image
                    $downloadedImage = file_get_contents($fileUrl);
                    $localFilePath = "../images/" . basename($filePath);

                    // Ensure the directory exists and is writable
                    if (!is_dir('../images')) {
                        mkdir('../images', 0777, true);
                    }

                    file_put_contents($localFilePath, $downloadedImage);

                    // Process the barcode image
                    $decodedBarcode = processBarcodeImage($localFilePath);

                    if ($decodedBarcode) {
                        // Store the decoded barcode in the session
                        $_SESSION['decodedBarcode'][$chatId] = $decodedBarcode;
                        // Ask for location sharing
                        $locationMarkup = json_encode([
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, "Please share your LIVE location to continue.", $token, $locationMarkup);
                    } else {
                        sendMessage($chatId, "Could not decode the barcode. Please try again with a clearer image.", $token);
                    }
                } else {
                    sendMessage($chatId, "Failed to retrieve the image file. Please try again.", $token);
                }
            }

            // Handle location sharing
            if (isset($update['message']['location'])) {
                $location = $update['message']['location'];
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];

                // Retrieve the decoded barcode from the session
                $decodedBarcode = $_SESSION['decodedBarcode'][$chatId] ?? 'No barcode decoded.';

                // Get the current date and time
                $dateTime = date('M-d-Y H:i');

                // Prepare the response message
                $responseMessage = "Date: {$dateTime}\n";
                $responseMessage .= "Code: {$decodedBarcode}\n"; // Include the decoded barcode/QR code here
                $responseMessage .= "Location: https://www.google.com/maps/dir/{$latitude},{$longitude}";

                sendMessage($chatId, $responseMessage, $token);

                // Optionally, you may want to unset the decodedBarcode session data if it's a one-time use
                unset($_SESSION['decodedBarcode'][$chatId]);
            }

        }
    }
}


// Function to process the barcode image
function processBarcodeImage($filePath) {
    // Ensure the zbarimg command is executable
    $command = escapeshellcmd("zbarimg --raw " . escapeshellarg($filePath));
    $output = shell_exec($command);
    return $output ? trim($output) : false; // Return false on failure
}

// Main loop to fetch updates
$offset = 0;
while (true) {
    $updates = file_get_contents("https://api.telegram.org/bot{$config['bot_token']}/getUpdates?offset={$offset}");
    $updates = json_decode($updates, true);

    if (isset($updates['result'])) {
        processUpdates($updates['result'], $config['bot_token']);
        $offset = end($updates['result'])['update_id'] + 1;
    } else {
        echo "No updates found.";
    }

    sleep(1);
}
