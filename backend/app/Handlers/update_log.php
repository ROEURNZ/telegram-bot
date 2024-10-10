<?php
// backend/app/Handlers/CommandHandlers.php

include __DIR__ . '/../includes/IncludeCommands.php';


function processUpdates($updates, $token)
{

    static $userLanguages = []; // Store user language preferences
    global $messages; // Access global messages variable

    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];

            // Set the current chat ID in the session
            $_SESSION['currentChatId'] = $chatId;
            // Default language is English if not selected
            $language = $userLanguages[$chatId] ?? 'en';


            // Handle text messages
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];

                // Check if the bot has started
                if (!isset($_SESSION['started'][$chatId]) || !$_SESSION['started'][$chatId]) {
                    if ($update['message']['text'] !== '/start') {
                        sendMessage($chatId, $messages[$language]['please_start'], $token);
                        continue;
                    }
                }

                // Handle START_COMMAND
                if ($update['message']['text'] === '/start') {
                    $_SESSION['started'][$chatId] = true;
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
                }

                // Handle language selection
                if (in_array($update['message']['text'], [$messages['en']['language_option'], $messages['kh']['language_option']])) {
                    $language = $update['message']['text'] === $messages['en']['language_option'] ? 'en' : 'kh';
                    $userLanguages[$chatId] = $language;
                    $_SESSION['userLanguages'][$chatId] = $language;

                    sendMessage($chatId, $messages[$language]['language_selection'], $token);
                    showContactSharing($chatId, $token, $language);

                    // Set commands based on selected language
                    setCommands($language, $token, $chatId);
                    continue;
                }

                // Handle STOP_COMMAND
                if ($update['message']['text'] === '/stop') {
                    // Clear user session data
                    unset($_SESSION['userLanguages'][$chatId]);
                    unset($_SESSION['decodedBarcodes'][$chatId]);
                    unset($_SESSION['contact_shared'][$chatId]);
                    unset($_SESSION['started'][$chatId]);

                    // Send confirmation message
                    sendMessage($chatId, $messages[$language]['stopped'], $token);
                    continue;
                }

                // Handle commands
                if ($text === '/share_contact') {
                    sendMessage($chatId, $messages[$language]['language_selection'], $token);
                    showContactSharing($chatId, $token, $language);
                } elseif ($text === '/decode') {
                    sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                } elseif ($text === '/share_location') {
                    // Send the location sharing prompt
                    sendMessage($chatId, $messages[$language]['location_prompt'], $token);
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
                                    'callback_data' => 'en'
                                ],
                                [
                                    'text' => $messages['kh']['language_option'],
                                    'callback_data' => 'kh'
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
                // Extract the User ID from the message
                $userId = $update['message']['from']['id'];
                // Dynamically retrieve chat ID from the update
                $chatId = $update['message']['chat']['id'];
                $language = $userLanguages[$chatId] ?? 'en';
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";


                // Prepare the response message based on the selected language
                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_contact'],
                    $userId,
                    $chatId,
                    $firstName,
                    $lastName,
                    $phoneNumber,
                    $username
                );
                sendMessage($chatId, $responseMessage, $token);
                // Show the menu again after sharing contact


                // Send a follow-up message
                sendMessage($chatId, $messages[$language]['upload_barcode'], $token,  json_encode(['remove_keyboard' => true]));

                // Set session flag to indicate contact shared
                $_SESSION['contact_shared'][$chatId] = true;
                // Set the current chat ID in the session
                $_SESSION['currentChatId'] = $chatId;
                // Re-apply the commands after contact sharing is done
                setCommands($token, $language);
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
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);
                            sendMessage($chatId, $messages[$language]['location_request'], $token,  json_encode(['remove_keyboard' => true]));
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
                unset($_SESSION['decodedBarcodes'][$chatId]);
            }
        }
    }
}




// Function to show contact sharing options
function showContactSharing($chatId, $token, $language)
{
    global $messages; // Access the messages array

    // First, send the contact sharing request with the keyboard
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
        // 'keyboard' => [[['text' => $messages[$language]['share_location'], 'request_location' => true]]],
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ]);
    sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
}

// Function to process the barcode image
function processBarcodeImage($filePath)
{
    // Ensure the zbarimg command is executable
    $command = escapeshellcmd("zbarimg --raw " . escapeshellarg($filePath));
    $output = shell_exec($command);
    return $output ? trim($output) : false; // Return false on failure
}


include __DIR__ . '/../includes/functions/polling.php';
