<?php

// backend/app/Handlers/CommandHandlers.php
// Load configuration

$config = require '../config/bot_config.php';
$token = $config['bot_token'];
$chatId = $config['chat_id'];
// set_time_limit(180); // Allow the script to run indefinitely
set_time_limit(0); // This will allow the script to run indefinitely
session_start();
date_default_timezone_set("Asia/Phnom_Penh");
$offset = 0;

// Load localization files
$messages = [
    'en' => include('../Localization/languages/en/english.php'),
    'kh' => include('../Localization/languages/kh/khmer.php'),
];

include('../Localization/dateformat/dateFormat.php');

// Include the setMyCommands functionality
include('../Commands/setMyCommands.php');
// Define commands
define('STOP_COMMAND', '/stop');
define('START_COMMAND', '/start');

// Function to send messages
function sendMessage($chatId, $message, $token, $replyMarkup = null)
{
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => false
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

function processUpdates($updates, $token)
{

    static $userLanguages = []; // Store user language preferences
    global $messages; // Access global messages variable

    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];

            // Default language is English if not selected
            $language = $userLanguages[$chatId] ?? 'en';
            // Set commands based on selected language
            setCommands($language, $token);
            // Handle text messages
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                // Check if the bot has started
                if (!isset($_SESSION['started'][$chatId]) || !$_SESSION['started'][$chatId]) {
                    if ($update['message']['text'] !== START_COMMAND) {
                        sendMessage($chatId, $messages[$language]['please_start'], $token);
                        continue;
                    }
                }

                // Handle START_COMMAND
                if ($update['message']['text'] === START_COMMAND) {
                    $_SESSION['started'][$chatId] = true; // Mark the bot as started
                    sendMessage($chatId, $messages[$language]['welcome_message'], $token);
                    $replyMarkup = json_encode([
                        'keyboard' => [
                            [
                                ['text' => $messages['en']['language_option']],
                                ['text' => $messages['kh']['language_option']]
                            ]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);
                    sendMessage($chatId, $messages[$language]['please_choose_language'], $token, $replyMarkup);
                    continue;
                    // Set commands based on selected language
                    setCommands($language, $token);
                }

                // Handle language selection
                if (in_array($update['message']['text'], [$messages['en']['language_option'], $messages['kh']['language_option']])) {
                    $language = $update['message']['text'] === $messages['en']['language_option'] ? 'en' : 'kh';
                    $userLanguages[$chatId] = $language;
                    $_SESSION['userLanguages'][$chatId] = $language;

                    // Set commands based on selected language
                    setCommands($language, $token);

                    sendMessage($chatId, $messages[$language]['language_selection'], $token);
                    showContactSharing($chatId, $token, $language); // Ask for contact sharing
                    continue;
                }

                // Handle STOP_COMMAND
                if ($update['message']['text'] === STOP_COMMAND) {
                    // Clear user session data
                    unset($_SESSION['userLanguages'][$chatId]);
                    unset($_SESSION['decodedBarcodes'][$chatId]);
                    unset($_SESSION['contact_shared'][$chatId]);
                    unset($_SESSION['started'][$chatId]); // Mark bot as stopped

                    // Send confirmation message
                    sendMessage($chatId, $messages[$language]['stopped'], $token);
                    continue;
                }

                // // Handle callback queries
                // if (isset($update['callback_query'])) {
                //     $callbackData = $update['callback_query']['data'];
                //     $chatId = $update['callback_query']['from']['id'];

                //     if ($callbackData === 'START_COMMAND') {
                //         $_SESSION['started'][$chatId] = true; // Mark the bot as started
                //         sendMessage($chatId, 'You have started the bot!', $token);
                //     }
                // }

                // Handle commands
                if ($text === '/share_contact') {
                    sendMessage($chatId, $messages[$language]['language_selection'], $token);
                    $replyMarkup = json_encode([
                        'keyboard' => [[['text' => $messages[$language]['share_contact'], 'request_contact' => true]]],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);
                    sendMessage($chatId, $messages[$language]['contact_prompt'], $token);
                } elseif ($text === '/decode') {
                    sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                } elseif ($text === '/share_location') {
                    // Send the location sharing prompt
                    sendMessage($chatId, $messages[$language]['location_prompt'], $token);

                    // Prepare the location sharing button
                    $replyMarkup = json_encode([
                        'keyboard' => [[['text' => $messages[$language]['share_location'], 'request_location' => true]]],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);

                    // Show the location sharing options to the user
                    sendMessage($chatId, $messages[$language]['location_request'], $token, $replyMarkup);
                    showLocationSharing($chatId, $token, $language);
                } elseif ($text === '/help') {
                    sendMessage($chatId, $messages[$language]['help'], $token);
                } elseif ($text === '/menu') {
                    sendMessage($chatId, $messages[$language]['menu'], $token);
                } elseif ($text === '/change_language') {
                    $replyMarkup = json_encode([
                        'keyboard' => [
                            [
                                [
                                    'text' => $messages['en']['language_option'],
                                    'callback_data' => 'language_en'
                                ],
                                [
                                    'text' => $messages['kh']['language_option'],
                                    'callback_data' => 'language_kh'
                                ]
                            ]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);

                    sendMessage($chatId, $messages[$language]['please_choose_language'], $token, $replyMarkup);
                }
            }

            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                $language = $userLanguages[$chatId] ?? 'en';
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";

                // Prepare the response message based on the selected language

                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_contact'],
                    $firstName,
                    $lastName,
                    $phoneNumber,
                    $username
                );
                sendMessage($chatId, $responseMessage, $token);
                sendMessage($chatId, $messages[$language]['upload_barcode'], $token);


                // Set commands based on selected language
                setCommands($language, $token);
                // Set session flag to indicate contact shared
                $_SESSION['contact_shared'][$chatId] = true;
                continue;
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
                    $imagesPath = __DIR__ . "/../../storage/app/public/images/decoded/";

                    // Download the image
                    $downloadedImage = file_get_contents($fileUrl);
                    $localFilePath = $imagesPath . basename($filePath);

                    // Ensure the directory exists and is writable
                    if (!is_dir($imagesPath)) {
                        mkdir($imagesPath, 0777, true);
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
                                'keyboard' => [[['text' => $messages[$language]['share_location'], 'location_request' => true]]],
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);
                            sendMessage($chatId, $messages[$language]['location_request'], $token, $locationMarkup);
                        }
                    } else {
                        sendMessage($chatId, $messages[$language]['barcode_error'], $token);
                    }
                } else {
                    sendMessage($chatId, $messages[$language]['image_error'], $token);
                }
            }

            // Handle location sharing
            if (isset($update['message']['location'])) {
                $location = $update['message']['location'];
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];
                $decodedBarcodes = $_SESSION['decodedBarcodes'][$chatId] ?? [];
                // $language = $userLanguages[$chatId] ?? 'en'; // Default to English if not set

                // Format the current date and time
                $formattedDate = formatDate($language); // Get the formatted date based on the user's language
                $formattedTime = formatTime($language); // Get the formatted time based on the user's language

                // Prepare the location URL
                $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";

                // Format the barcode list for the response
                $barcodeList = implode("\n", array_map(function ($barcode, $index) {
                    return ($index + 1) . ". $barcode";
                }, $decodedBarcodes, array_keys($decodedBarcodes)));

                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_location'],
                    $formattedDate,
                    $formattedTime,
                    $barcodeList,
                    $locationUrl,
                );

                sendMessage($chatId, $responseMessage, $token);

                // Display language selection buttons again after sharing location
                $replyMarkup = json_encode([
                    'keyboard' => [
                        [
                            ['text' => $messages['en']['language_option']],
                            ['text' => $messages['kh']['language_option']]
                        ]
                    ],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]);

                // Send the language selection message again
                sendMessage($chatId, $messages[$language]['restart_message'], $token, $replyMarkup);


                // Optionally, you can restart the process with a welcome message or prompt
                // $restartMessage = $messages[$language]['restart_message']; // Define this message in your $messages array
                // sendMessage($chatId, $restartMessage, $token); // Send the restart message to guide the user

                // Clear the decoded barcodes after the message is sent
                unset($_SESSION['decodedBarcodes'][$chatId]);
            }
        }
    }
}


// Function to show contact sharing options
function showContactSharing($chatId, $token, $language)
{
    global $messages; // Access the messages array
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => $messages[$language]['share_contact'], 'request_contact' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $messages[$language]['contact_request'], $token, $replyMarkup);
}

// Function to show location sharing options
function showLocationSharing($chatId, $token, $language)
{
    global $messages; // Access the messages array
    $replyMarkup = json_encode([
        'keyboard' => [[['text' => $messages[$language]['share_location'], 'request_location' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $messages[$language]['location_request'], $token, $replyMarkup);
}

// Function to process the barcode image
function processBarcodeImage($filePath)
{
    // Ensure the zbarimg command is executable
    $command = escapeshellcmd("zbarimg --raw " . escapeshellarg($filePath));
    $output = shell_exec($command);
    return $output ? trim($output) : false; // Return false on failure
}



// Loop to keep the bot running indefinitely
while (true) {
    // Build the URL to fetch updates with the current offset
    $url = "https://api.telegram.org/bot{$token}/getUpdates?offset={$offset}&timeout=30"; // Set timeout to 30 seconds

    // Initialize cURL to get updates
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification (useful for local dev)
    $response = curl_exec($ch);

    if ($response === false) {
        echo "Error fetching updates: " . curl_error($ch);
        curl_close($ch);
        sleep(1); // Wait for a second before trying again
        continue;
    }

    curl_close($ch);

    $updates = json_decode($response, true);

    if (isset($updates['result'])) {
        // Process each update (adjust this function based on your bot's logic)
        processUpdates($updates['result'], $token);

        // Update the offset to the ID of the last processed update + 1
        if (!empty($updates['result'])) {
            $lastUpdateId = end($updates['result'])['update_id'];
            $offset = $lastUpdateId + 1;
        }
    }

    // Sleep for a short time to avoid spamming Telegram API
    sleep(1);
}
