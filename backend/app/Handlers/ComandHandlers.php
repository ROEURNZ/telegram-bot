<?php

// backend/app/Handlers/CommandHandlers.php
include __DIR__ . '/../includes/IncludeCommands.php';
function processUpdates($updates, $token)
{
    global $currentMessages;
    static $userLanguages = []; // Store user language preferences
    global $messages; // Access global messages variable
    // Instantiate the model globally
    $ezzeModel = new EzzeModels();
    setCommands($token, $currentMessages);

    // $ezzeModel = new EzzeModels($pdo);
    foreach ($updates as $update) {
        if (isset($update['message'])) {
            setCommands($token, $currentMessages);
            $chatId = $update['message']['chat']['id'];
            $userId = $update['message']['from']['id'];
            $firstName = $update['message']['from']['first_name'] ?? '';
            $lastName = $update['message']['from']['last_name'] ?? '';
            $username = $update['message']['from']['username'] ?? '';
            $messageId = $update['message']['message_id'];


            // Default language is English if not selected
            $language = $userLanguages[$chatId] ?? $ezzeModel->getUserLanguage($chatId, $username) ?? 'en';


            // Get messages based on the selected language

            $currentMessages = $messages[$language];
            // Handle text messages
            if (isset($update['message']['text'])) {
                $userCommand = $update['message']['text'];
                // $phoneNumber = $update['message']['text']['phone_number'];
                // Handle START_COMMAND immediately when the user presses the Start button
                if ($userCommand === '/start') {
                    // Check if the user exists
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Existing user, send welcome back message
                        $welcomeMessage = sprintf($messages[$language]['welcome_message'], "<b>$firstName</b>", "<b>$lastName</b>");
                        sendMessage($chatId, $welcomeMessage, $token, ['parse_mode' => 'HTML']);
                    } else {
                        // New user, send introduction message
                        sendMessage($chatId, $messages[$language]['new_user_message'], $token);
                    }

                    // Show language options
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

                    // Prompt user to choose a language
                    sendMessage($chatId, $messages[$language]['please_choose_language'], $token, $replyMarkup);
                }

                // Handle language selection
                if (in_array($userCommand, [$messages['en']['language_option'], $messages['kh']['language_option']])) {
                    // Set the language based on user selection
                    $language = $userCommand === $messages['en']['language_option'] ? 'en' : 'kh';

                    // Update user languages array and session
                    $userLanguages[$chatId] = $language;
                    $_SESSION['userLanguages'][$chatId] = $language;

                    // Update the user's language in the database
                    $ezzeModel->updateUserLanguage($chatId, $language);

                    sendMessage($chatId, $messages[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));
                    setCommands($token, $currentMessages);

                    if ($ezzeModel->tgUsername($userId) === null) {

                        showContactSharing($chatId, $token, $language);
                    } else {
                        sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                    }
                    continue;
                }
                // Handle /change_language command
                if ($userCommand === '/change_language') {
                    // Check if user exists in the database
                    if ($ezzeModel->checkUserExists($userId)) {
                        setCommands($token, $currentMessages);
                        // Update the user's language in the database
                        $ezzeModel->updateUserLanguage($chatId, $language);
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
                        // If user does not exist in the database, prompt to register or share contact
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                    }
                }



                // Handle /share_contact command only if language is selected
                if ($userCommand === '/share_contact') {
                    if (!$ezzeModel->checkUserExists($userId)) {
                        // If the user doesn't exist, show contact sharing prompt
                        showContactSharing($chatId, $token, $language);


                        setCommands($token, $currentMessages);
                    } else {
                        // If the user exists
                        if (!$ezzeModel->hasSelectedLanguage($userId) || !$ezzeModel->getUserLanguage($chatId, $username)) {
                            // If the language is not set, prompt the user to select a language
                            sendMessage($chatId, $messages['en']['please_select_language'], $token);
                        } else {
                            // Update the user's language in the database if needed
                            $ezzeModel->updateUserLanguage($userId, $language);
                        }
                    }
                }

                // Handle /decode command only if the user's contact is registered
                if ($userCommand === '/decode') {
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

                // Handle /decode command only if the user's contact is registered
                if ($userCommand === '/ocr') {
                    // Check if the user's contact is registered in the database using the model
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Proceed with decoding if contact is validated
                        sendMessage($chatId, $messages[$language]['upload_invoice'], $token);
                    } else {
                        // If contact is not registered, prompt the user to share contact first
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        // Optionally, suggest sharing contact
                        sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                    }
                }

                // Handle /share_location command only if user is validated and decode is completed
                if ($userCommand === '/share_location') {
                    // Check if user is validated and has completed the decode
                    if ($ezzeModel->checkUserExists($userId) && $ezzeModel->hasCompletedDecode($userId)) {
                        setCommands($token, $currentMessages);

                        // Proceed with location sharing if both conditions are met
                        sendMessage($chatId, $messages[$language]['location_prompt'], $token);
                    } else {
                        // If either condition is not met, prompt the user accordingly
                        if (!$ezzeModel->checkUserExists($userId)) {
                            sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        } elseif (!$ezzeModel->hasCompletedDecode($userId)) {
                            sendMessage($chatId, $messages[$language]['decode_not_completed'], $token);
                            // Optional: Uncomment the following line if you want to prompt the user to upload a barcode
                            // sendMessage($chatId, $messages[$language]['upload_barcode_prompt'], $token);
                        } else {
                            sendMessage($chatId, $messages[$language]['upload_barcode_prompt'], $token);
                        }
                    }
                }
                if ($userCommand === '/menu') {
                    // Check if the user exists in the database (registered by sharing contact)
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Provide the list of available commands
                        sendMessage($chatId, $messages[$language]['menu'], $token);
                    } else {
                        // If user is not registered, prompt to share contact first
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                    }
                }

            }

            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                if (!$ezzeModel->checkUserExists($userId) && !$ezzeModel->hasSelectedLanguage($userId)) {
                    setCommands($token, $currentMessages);

                    // Extract the User ID from the message
                    $userId = $update['message']['from']['id'];
                    $messageId = $update['message']['message_id'];
                    // Dynamically retrieve chat ID from the update
                    $chatId = $update['message']['chat']['id'];
                    $language = $userLanguages[$chatId] ?? 'en';
                    $contact = $update['message']['contact'];
                    $phoneNumber = $contact['phone_number'];
                    $firstName = $contact['first_name'];
                    $lastName = $contact['last_name'] ?? '';
                    $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";

                    $response = $ezzeModel->addUser([
                        'user_id' => $userId,
                        'chat_id' => $chatId,
                        'msg_id' => $messageId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $update['message']['from']['username'],
                        'phone_number' => $phoneNumber,
                        'date' => date('Y-m-d H:i:s'),
                        'language' => $language
                    ]);

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
                    sendMessage($chatId, $messages[$language]['upload_barcode'], $token, json_encode(['remove_keyboard' => true]));

                    // Set session flag to indicate contact shared
                    $_SESSION['contact_shared'][$chatId] = true;
                    // Set the current chat ID in the session
                    $_SESSION['currentChatId'] = $chatId;
                    // Re-apply the commands after contact sharing is done
                    setCommands($token, $currentMessages);
                    continue;
                } else {
                    sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                }
            }

            // Handle image upload (Barcode / QR code)
            if (isset($update['message']['photo'])) {
                if ($ezzeModel->checkUserExists($userId) && $ezzeModel->hasSelectedLanguage($userId)) {
                    setCommands($token, $currentMessages);

                    $photo = end($update['message']['photo']);
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

                        // Check if the image is for a barcode/QR code or an invoice
                        if (isBarcodeImage($localFilePath)) {
                            // Process the barcode image
                            require_once __DIR__ . '/../includes/functions/DecodeFunction.php';
                            $decodedBarcodeData = processBarcodeImage($localFilePath);
                            if (isset($decodedBarcodeData['code'])) {
                                $code = $decodedBarcodeData['code'];
                                $type = $decodedBarcodeData['type'];

                                // Save decoded barcode to session
                                if (!isset($_SESSION['decodedBarcodes'][$chatId])) {
                                    $_SESSION['decodedBarcodes'][$chatId] = [];
                                }
                                $_SESSION['decodedBarcodes'][$chatId][] = $decodedBarcodeData;

                                // Insert the barcode record into the database
                                $ezzeModel->addBarcode([
                                    'user_id' => $userId,
                                    'type' => $type,
                                    'code' => $code,
                                    'msg_id' => $messageId,
                                    'file_id' => $fileId,
                                    'file_unique_id' => $fileUniqueId,
                                    'decoded_status' => 1,
                                ]);

                                // Ask for location sharing if this is the first barcode scanned
                                if (count($_SESSION['decodedBarcodes'][$chatId]) == 1) {
                                    sendMessage($chatId, $messages[$language]['location_request'], $token, [
                                        'reply_markup' => json_encode([
                                            'resize_keyboard' => true,
                                            'one_time_keyboard' => true,
                                            'keyboard' => [[['text' => $messages[$language]['share_location'], 'request_location' => true]]]
                                        ])
                                    ]);
                                }
                            } else {
                                return;  // Exit silently if barcode decoding fails
                            }
                        } else {
                            // Check if the uploaded image is an invoice (You may want to implement a more robust method for determining this)
                            $isInvoiceImage = isInvoiceImage($localFilePath); // Implement this function as per your criteria

                            if ($isInvoiceImage) {
                                // Process the invoice image
                                require_once __DIR__ . '/../includes/functions/OCRFunction.php';
                                $ocrResult = processInvoiceImage($localFilePath);

                                if (isset($ocrResult['vatTin'])) {
                                    // Save the extracted VAT-TIN to session or handle it as needed
                                    $_SESSION['extractedVatTin'][$chatId] = $ocrResult['vatTin'];

                                    // Ask for location sharing after extracting VAT-TIN
                                    json_encode([
                                        'resize_keyboard' => true,
                                        'one_time_keyboard' => true,
                                    ]);

                                    sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                } else {
                                    // Handle the error case for OCR
                                    sendMessage($chatId, $ocrResult['error'], $token);
                                }
                            }
                        }
                    } else {
                        return;  // Exit silently if unable to retrieve the file from Telegram
                    }
                } else {
                    sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                }
            }

            // Handle location sharing
            if (isset($update['message']['location'])) {
                if ($ezzeModel->checkUserExists($userId) && $ezzeModel->hasSelectedLanguage($userId)) {
                    $userId = $update['message']['from']['id'];
                    $chatId = $update['message']['chat']['id'];
                    $latitude = $update['message']['location']['latitude'];
                    $longitude = $update['message']['location']['longitude'];
                    $date = date('Y-m-d H:i:s');

                    // Retrieve the decoded barcodes or VAT-TIN stored in session
                    $decodedBarcodes = $_SESSION['decodedBarcodes'][$chatId] ?? [];
                    $vatTin = $_SESSION['extractedVatTin'][$chatId] ?? null;

                    // Format the current date and time
                    $formattedDate = formatDate($language); // Get the formatted date based on the user's language
                    $formattedTime = formatTime($language); // Get the formatted time based on the user's language

                    // Prepare the Google Maps URL
                    $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";

                    // Format the barcode or VAT-TIN list for the response message
                    $responseList = '';
                    if (!empty($decodedBarcodes)) {
                        $responseList .= implode("\n", array_map(function ($barcode, $index) {
                            return ($index + 1) . ". <code><b>{$barcode['code']}</b></code>";
                        }, $decodedBarcodes, array_keys($decodedBarcodes))) . "\n";
                    }
                    if ($vatTin) {
                        $responseList .= "VAT-TIN: <code><b>{$vatTin}</b></code>";
                    }

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
                        $messages[$language]['ex_location_shared'],
                        $formattedDate,
                        $formattedTime,
                        $responseList,
                        $locationUrl
                    );

                    // Send the location confirmation message
                    sendMessage($chatId, $responseMessage, $token, ['parse_mode' => 'HTML']);

                    // Clear the session for this chat after processing the barcodes, VAT-TIN, and location
                    unset($_SESSION['decodedBarcodes'][$chatId]);
                    unset($_SESSION['vatTin'][$chatId]);
                }
            }


        }
    }
}

function isBarcodeImage($filePath)
{
    // Example: Check file extension
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedExtensions);
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

include __DIR__ . '/../includes/functions/polling.php';

