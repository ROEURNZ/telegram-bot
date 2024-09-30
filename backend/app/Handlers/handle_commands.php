<?php
// backend/app/Handlers/handle_commands.php
// Load configuration
$config = require '../config/bot_config.php';
set_time_limit(300); // Allow the script to run indefinitely
session_start();

// Load localization files
$messages = [
    'en' => include('../Localization/languages/en/english.php'),
    'kh' => include('../Localization/languages/kh/khmer.php'),
];

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
    global $messages; // Access the global messages array
    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];

            // Determine the user's language preference
            $userLanguage = $_SESSION['user_language'] ?? 'en'; // Default to English

            // Handle text messages
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                switch ($text) {
                    case '/start':
                        sendMessage($chatId, $messages[$userLanguage]['welcome_message'], $token);
                        
                        $replyMarkup = json_encode([
                            'keyboard' => [
                                [['text' => '🇺🇸 English']],
                                [['text' => '🇰🇭 ភាសាខ្មែរ']]
                            ],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, $messages[$userLanguage]['language_selection'], $token, $replyMarkup);
                        break;

                   case '🇺🇸 English':
                        $_SESSION['user_language'] = 'en'; // Set user language to English
                        sendMessage($chatId, "You selected English", $token); // New response
                        $contactMarkup = json_encode([
                            'keyboard' => [[['text' => 'Share My Contact', 'request_contact' => true]]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, $messages['en']['contact_prompt'], $token, $contactMarkup);
                        break;

                    case '🇰🇭 ភាសាខ្មែរ':
                        $_SESSION['user_language'] = 'kh'; // Set user language to Khmer
                        sendMessage($chatId, "អ្នកបានជ្រើសរើសខ្មែរ", $token); // New response
                        $contactMarkup = json_encode([
                            'keyboard' => [[['text' => 'ចែករំលែកទំនាក់ទំនង', 'request_contact' => true]]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, $messages['kh']['contact_prompt'], $token, $contactMarkup);
                        break;

                    case '/help':
                        sendMessage($chatId, $messages[$userLanguage]['help'], $token);
                        break;

                    case '/menu':
                        sendMessage($chatId, $messages[$userLanguage]['menu'], $token);
                        break;

                    case '/share_contact': // Handle the /share_contact command
                        $contactMarkup = json_encode([
                            'keyboard' => [[['text' => 'Share My Contact', 'request_contact' => true]]],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, $messages[$userLanguage]['contact_prompt'], $token, $contactMarkup);
                        break;

                    case '/change_language': // Handle the /change_language command
                        $replyMarkup = json_encode([
                            'keyboard' => [
                                [['text' => '🇺🇸 English']],
                                [['text' => '🇰🇭 ភាសាខ្មែរ']]
                            ],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);
                        sendMessage($chatId, $messages[$userLanguage]['language_selection'], $token, $replyMarkup);
                        break;


                    case '/decode': // Handle the /decode command
                        sendMessage($chatId, $messages[$userLanguage]['upload_barcode'], $token);
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

                // Get the user's selected language
                $language = $_SESSION['user_language'] ?? 'en'; // Default to English if not set

                // Prepare the response message based on the selected language
                if ($language === 'kh') {
                    $responseMessage = sprintf(
                        $messages['kh']['thanks_for_contact'],
                        $firstName,
                        $lastName,
                        $phoneNumber,
                        $username
                    );
                } else {
                    $responseMessage = sprintf(
                        "Thanks for sharing your contact!\nFull Name: %s %s\nPhone Number: %s\nUsername: %s",
                        $firstName,
                        $lastName,
                        $phoneNumber,
                        $username
                    );
                }

                sendMessage($chatId, $responseMessage, $token);
                sendMessage($chatId, $messages[$userLanguage]['upload_barcode'], $token);
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
                        // Store the decoded barcode in the session as an array
                        if (!isset($_SESSION['decodedBarcodes'][$chatId])) {
                            $_SESSION['decodedBarcodes'][$chatId] = [];
                        }
                        $_SESSION['decodedBarcodes'][$chatId][] = $decodedBarcode; // Append to the array

                        // Ask for location sharing if this is the first barcode
                        if (count($_SESSION['decodedBarcodes'][$chatId]) == 1) {
                            $locationMarkup = json_encode([
                                // 'keyboard' => [
                                //     [[ 'text' => 'Share Location', 'request_location' => true ]]
                                // ],
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);
                            sendMessage($chatId, $messages[$userLanguage]['share_location'], $token, $locationMarkup);
                        }
                    } else {
                        sendMessage($chatId, $messages[$userLanguage]['barcode_error'], $token);
                    }
                } else {
                    sendMessage($chatId, $messages[$userLanguage]['image_error'], $token);
                }
            }

            // Handle location sharing
            if (isset($update['message']['location'])) {
                $location = $update['message']['location'];
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];

                // Get the user's selected language
                $language = $_SESSION['user_language'] ?? 'en'; // Default to English if not set

                // Retrieve the decoded barcodes from the session (as an array)
                $decodedBarcodes = $_SESSION['decodedBarcodes'][$chatId] ?? ['No barcode decoded.'];

                // Get the current date and time
                $date = date('M-d-Y');
                $time = date('H:i');

                // Prepare the location URL
                $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";

                // Format the barcode list for the response
                $barcodeList = implode("\n", array_map(function($barcode, $index) {
                    return ($index + 1) . ". $barcode";
                }, $decodedBarcodes, array_keys($decodedBarcodes)));

                // Prepare the response message based on the selected language
                if ($language === 'kh') {
                    $responseMessage = sprintf(
                        $messages['kh']['thank_you_location'],
                        $date,
                        $time,
                        $barcodeList,
                        $locationUrl
                    );
                } else {
                    $responseMessage = sprintf(
                        "Date: %s %s\nDecoded Codes:\n%s\nLocation: %s",
                        $date,
                        $time,
                        $barcodeList,
                        $locationUrl
                    );
                }

                sendMessage($chatId, $responseMessage, $token);

                // Clear the decoded barcodes after the message is sent
                unset($_SESSION['decodedBarcodes'][$chatId]);
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
