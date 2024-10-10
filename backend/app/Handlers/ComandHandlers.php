<?php
// backend/app/Handlers/CommandHandlers.php

include __DIR__ . '/../includes/IncludeCommands.php';


function processUpdates($updates, $token)
{

    static $userLanguages = []; // Store user language preferences
    global $messages; // Access global messages variable
    // Instantiate the model
    $ezzeModel = new EzzeModels();
    // $ezzeModel = new EzzeModels($pdo);
    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $userId = $update['message']['from']['id'];

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
                    // Set commands based on selected language
                    setCommands($token, $language);
                    showContactSharing($chatId, $token, $language);
                    continue;
                }

                // Handle /share_contact command only if language is selected
                else if ($text === '/share_contact') {
                    // Check if the language has been selected
                    if (isset($userLanguages[$chatId])) {
                        $language = $userLanguages[$chatId];
                        // Call the function to show contact sharing in the selected language
                        showContactSharing($chatId, $token, $language);
                    } else {
                        // If language is not set, prompt user to select a language first
                        sendMessage($chatId, $messages['en']['please_select_language'], $token); // Default to English prompt

                        // Optionally, send a specific language prompt based on the default
                        if (isset($messages[$language]['language_prompt'])) {
                            sendMessage($chatId, $messages[$language]['language_prompt'], $token);
                        } else {
                            // Fallback to English if the user's selected language doesn't have the prompt
                            sendMessage($chatId, $messages['en']['language_prompt'], $token);
                        }
                    }
                }
                // Handle /decode command only if the user's contact is registered
                else if ($text === '/decode') {
                    // Check if the user's contact is registered in the database using the model
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Proceed with decoding if contact is validated
                        sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                    } else {
                        // If contact is not registered, prompt the user to share contact first
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        // Optionally, suggest sharing contact
                        sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                    }
                }

                // Handle /share_location command only if user is validated and decode is completed
                else if ($text === '/share_location') {
                    // Check if user is validated and has completed the decode
                    if ($ezzeModel->checkUserExists($userId) && $ezzeModel->hasCompletedDecode($userId)) {
                        // Proceed with location sharing if both conditions are met
                        sendMessage($chatId, $messages[$language]['location_prompt'], $token);
                    } else {
                        // If either condition is not met, prompt the user accordingly
                        if (!$ezzeModel->checkUserExists($userId)) {
                            sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                            sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                        } elseif (!$ezzeModel->hasCompletedDecode($userId)) {
                            sendMessage($chatId, $messages[$language]['decode_not_completed'], $token);
                            sendMessage($chatId, $messages[$language]['upload_barcode_prompt'], $token);
                        }
                    }
                } elseif ($text === '/menu') {
                    // Check if the user exists in the database (registered by sharing contact)
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Provide the list of available commands
                        sendMessage($chatId, $messages[$language]['menu'], $token);
                    } else {
                        // If user is not registered, prompt to share contact first
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                    }
                }

                // Handle /change_language command only if the user has shared their location
                else if ($text === '/change_language') {
                    // Check if user exists in the database
                    if ($ezzeModel->checkUserExists($userId)) {
                        // If user has shared their location
                        if ($ezzeModel->hasSharedLocation($chatId)) {
                            // Provide the option to change language
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
                        } else {
                            // If user hasn't shared location, prompt them to share location first
                            sendMessage($chatId, $messages[$language]['location_not_shared'], $token);
                            sendMessage($chatId, $messages[$language]['location_prompt'], $token);
                        }
                    } else {
                        // If user does not exist in the database, prompt to register or share contact
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                    }
                }

                // Handle existing user case and proceed to upload barcode or decode
                elseif ($text === '/upload_barcode' || $text === '/decode') {
                    // Check if user exists in the database
                    if ($ezzeModel->checkUserExists($userId)) {
                        sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                    } else {
                        // If user is not registered, prompt them to register
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                    }
                }
            }

            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                // Extract the User ID from the message
                $userId = $update['message']['from']['id'];
                $messageId = $update['message']['message_id'];
                $date = date('Y-m-d H:i:s');
                // Dynamically retrieve chat ID from the update
                $chatId = $update['message']['chat']['id'];
                $language = $userLanguages[$chatId] ?? 'en';
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";
                // Store the username directly without the URL
                // $username = $update['message']['from']['username'] ?? 'No username available';
                // Prepare parameters for addUser
                $params = [
                    'user_id' => $userId,
                    'chat_id' => $chatId,
                    'msg_id' => $messageId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'phone_number' => $phoneNumber,
                    'date' => $date,
                    'language' => $language
                ];


                $response = $ezzeModel->addUser($params);
                echo $response;

                // $usernameLink = ($username !== 'No username available') ? "https://t.me/$username" : $username;
                // Prepare the response message based on the selected language
                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_contact'],
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

            // Handle image upload (Barcode / QR code)
            if (isset($update['message']['photo'])) {
                $photo = end($update['message']['photo']);  // Get the largest image size
                $chatId = $update['message']['chat']['id'];
                $userId = $update['message']['from']['id'];
                $messageId = $update['message']['message_id'];
                $fileId = $photo['file_id'];
                $fileUniqueId = $photo['file_unique_id'];

                // Retrieve the file data from Telegram
                $fileData = file_get_contents("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");
                $fileData = json_decode($fileData, true);

                if (isset($fileData['result']['file_path'])) {
                    $filePath = $fileData['result']['file_path'];
                    $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";
                    $imagesPath = __DIR__ . "/../../storage/app/public/images/decoded/";

                    // Download and save the image locally
                    $downloadedImage = file_get_contents($fileUrl);
                    $localFilePath = $imagesPath . basename($filePath);

                    // Ensure the directory exists
                    if (!is_dir($imagesPath)) {
                        mkdir($imagesPath, 0777, true);
                    }

                    // Save the downloaded image locally
                    file_put_contents($localFilePath, $downloadedImage);

                    // Process the barcode image
                    $decodedBarcodeData = processBarcodeImage($localFilePath);

                    if (isset($decodedBarcodeData['code'])) {
                        // Store the decoded barcode data
                        $code = $decodedBarcodeData['code'];
                        $type = $decodedBarcodeData['type'];

                        // Save decoded barcode to session
                        if (!isset($_SESSION['decodedBarcodes'][$chatId])) {
                            $_SESSION['decodedBarcodes'][$chatId] = [];
                        }
                        $_SESSION['decodedBarcodes'][$chatId][] = $decodedBarcodeData;

                        // Ask for location sharing if this is the first barcode scanned
                        if (count($_SESSION['decodedBarcodes'][$chatId]) == 1) {
                            // Prepare the markup for location sharing keyboard
                            $locationMarkup = json_encode([
                                // 'keyboard' => [
                                //     [['text' => $messages[$language]['share_location'], 'request_location' => true]]
                                // ],
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);

                            // Send the location request message along with the keyboard
                            sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                        }

                        // Prepare parameters for database insertion
                        $params = [
                            'user_id' => $userId,              
                            'type' => $type,                   
                            'code' => $code,                   
                            'msg_id' => $messageId,            
                            'file_id' => $fileId,              
                            'file_unique_id' => $fileUniqueId, 
                        ];

                        // Insert barcode record into the database
                        $response = $ezzeModel->addBarcode($params);
                        echo $response;
                    } else {
                        // Handle error if barcode decoding failed
                        sendMessage($chatId, $messages[$language]['barcode_error'], $token);
                    }
                } else {
                    // Handle error if unable to retrieve the file from Telegram
                    sendMessage($chatId, $messages[$language]['image_error'], $token);
                }
            }


            // Handle location sharing
            if (isset($update['message']['location'])) {
                $userId = $update['message']['from']['id'];
                $chatId = $update['message']['chat']['id'];
                $latitude = $update['message']['location']['latitude'];
                $longitude = $update['message']['location']['longitude'];
                $date = date('Y-m-d H:i:s');

                // Retrieve the decoded barcodes stored in session
                $decodedBarcodes = $_SESSION['decodedBarcodes'][$chatId] ?? [];

                // Format the current date and time
                $formattedDate = formatDate($language); // Get the formatted date based on the user's language
                $formattedTime = formatTime($language); // Get the formatted time based on the user's language

                // Prepare the Google Maps URL
                $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";

                // Format the barcode list for the response message
                $barcodeList = implode("\n", array_map(function ($barcode, $index) {
                    return ($index + 1) . ". {$barcode['code']} ({$barcode['type']})";
                }, $decodedBarcodes, array_keys($decodedBarcodes)));

                // Save location data to the database
                $params = [
                    'user_id' => $userId,
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'location_url' => $locationUrl,
                    'date' => $date
                ];
                $response = $ezzeModel->addLocation($params);
                echo $response;

                // Prepare the response message
                $responseMessage = sprintf(
                    $messages[$language]['thanks_for_location'],
                    $formattedDate,
                    $formattedTime,
                    $barcodeList,
                    $locationUrl
                );

                // Send the location confirmation message
                sendMessage($chatId, $responseMessage, $token);

                // Clear the session for this chat after processing the barcodes and location
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



// Function to process code images for barcode decoding
function processBarcodeImage($filePath)
{
    $decodeCmd = @shell_exec(escapeshellcmd("zbarimg --raw " . escapeshellarg($filePath)));
    $code = trim($decodeCmd);
    if ($decodeCmd === null || $code === '') {
        return ["error" => "Decoding failed: " . htmlspecialchars(basename($filePath))];
    }
    $file = $filePath;
    $type = identifyBarcodeType($code);
    return [
        'file' => $file,
        'code' => $code,
        'type' => $type,
    ];
}


// Function to identify barcode type
function identifyBarcodeType($code)
{
    $trimmedCode = trim($code);
    // Check for known barcode formats
    if (strpos($trimmedCode, 'BEGIN') !== false) return 'QR Code';
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


include __DIR__ . '/../includes/functions/polling.php';
