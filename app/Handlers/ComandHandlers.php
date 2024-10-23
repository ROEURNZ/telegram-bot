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
            $phoneNumber = $update['message']['contact']['phone_number'];

            // Default language is English if not selected
            $language = $userLanguages[$chatId] ?? $ezzeModel->getUserLanguage($chatId) ?? 'en';

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

                    // Update language setting and respond based on session flag
                    if (isset($_SESSION['is_changing_language']) && $_SESSION['is_changing_language']) {
                        // Language change process is active
                        $responseText = $language === 'en' ? "Language set to English" : "អ្នកបានកំណត់ជាភាសាខ្មែរ";
                        sendMessage($chatId, $responseText, $token, json_encode(['remove_keyboard' => true]));
                        unset($_SESSION['is_changing_language']); // Reset the flag
                    } else {
                        // Regular language selection
                        $responseText = $language === 'en' ? "You have selected English" : "អ្នកបានជ្រើសរើសភាសាខ្មែរ";
                        sendMessage($chatId, $responseText, $token);

                        // If not part of the `/change_language`, proceed to prompt for contact or image
                        if ($ezzeModel->tgUsername($userId) === null) {
                            // Prompt user to share contact if not already done
                            showContactSharing($chatId, $token, $language);
                        } else {
                            // Ask user to upload a barcode or QR code
                            sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                        }
                    }

                    // Set language selection flag if not set
                    if (!isset($_SESSION['language_selected'])) {
                        $_SESSION['language_selected'] = true;
                    }

                    // Hide language option keyboard by sending an empty keyboard
                    $emptyKeyboard = json_encode([
                        'remove_keyboard' => true,
                    ]);
                    sendMessage($chatId, " ", $token, $emptyKeyboard); // Send an empty message with the keyboard to remove it

                    // Update available commands based on the selected language
                    setCommands($token, $currentMessages);

                    continue;
                }

                // Handle /change_language command
                if ($userCommand === '/change_language') {
                    // Set a session flag to track the language change process
                    $_SESSION['is_changing_language'] = true;

                    // Check if user exists in the database
                    if (!$ezzeModel->checkUserExists($userId)) {
                        // User does not exist, ask them to share their contact
                        showContactSharing($chatId, $token, $language);
                    } else {
                        // User exists, ask them to choose a language
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
                    }
                    continue;
                }





                // Handle /share_contact command only if language is selected
                if ($userCommand === '/share_contact') {
                    // Check if the user exists
                    if (!$ezzeModel->checkUserExists($userId)) {
                        // If the user doesn't exist, show contact sharing prompt
                        showContactSharing($chatId, $token, $language);
                        setCommands($token, $currentMessages);
                    } else {
                        // If the user exists
                        if (!$ezzeModel->hasSelectedLanguage($userId)) {
                            // If the language is not set, prompt the user to select a language
                            sendMessage($chatId, $messages['en']['please_select_language'], $token);
                        } else {
                            // If the user has a selected language, update user details
                            $params = [
                                'chat_id' => $chatId,
                                'msg_id' => $messageId,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'username' => $username,
                                'phone_number' => $phoneNumber,
                                'date' => date('Y-m-d H:i:s'),
                                'language' => $language
                            ];

                            // Update the user's language if it has changed
                            if ($ezzeModel->getUserLanguage($chatId) !== $language) {
                                $ezzeModel->updateUserLanguage($userId, $language);
                            }

                            // Update user details
                            $ezzeModel->updateUser($params);
                        }
                    }
                }


                // Handle /decode command only if the user's contact is registered
                if ($userCommand === '/decode') {
                    // Check if the user's contact is registered in the database using the model
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Proceed with decoding if contact is validated
                        sendMessage($chatId, $messages[$language]['upload_barcode'], $token);
                        $_SESSION['currentCommand'][$chatId] = 'decode'; // Set the current command
                    } else {
                        // If contact is not registered, prompt the user to share contact first
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                        // Optionally, suggest sharing contact
                        sendMessage($chatId, $messages[$language]['share_contact_prompt'], $token);
                    }
                }

                // Handle /ocr command only if the user's contact is registered
                if ($userCommand === '/ocr') {
                    // Check if the user's contact is registered in the database using the model
                    if ($ezzeModel->checkUserExists($userId)) {
                        // Proceed with OCR if contact is validated
                        sendMessage($chatId, $messages[$language]['upload_invoice'], $token);
                        $_SESSION['currentCommand'][$chatId] = 'ocr'; // Set the current command
                    } else {
                        // If contact is not registered, prompt the user to share contact first
                        sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                    }
                }

                // Handle /share_location command
                // if ($userCommand === '/share_location') {
                //     // Check if user is validated
                //     if ($ezzeModel->checkUserExists($userId)) {
                //         // Check if user has completed the decode
                //         if ($ezzeModel->hasCompletedDecode($userId)) {
                //             setCommands($token, $currentMessages);
                //             sendMessage($chatId, $messages[$language]['location_prompt'], $token);
                //         } else {
                //             sendMessage($chatId, $messages[$language]['decode_not_completed'], $token);
                //         }
                //     } else {
                //         sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
                //     }
                // }

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

                    $response = $ezzeModel->registerUsers([
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

            // Handle image upload (Barcode / QR code or Invoice)
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

                        // Check the current command and process the image accordingly
                        if ($_SESSION['currentCommand'][$chatId] === 'ocr') {
                            // Check if the uploaded image is an invoice
                            if (isInvoiceImage($localFilePath)) {
                                // Process the invoice image
                                require_once __DIR__ . '/../includes/functions/OCRFunction.php';
                                $ocrResult = processInvoiceImage($localFilePath);
                                $_SESSION['imageType'][$chatId] = 'invoice';
                                $rawText = $ocrResult['text'];

                                // Check if VAT-TIN was extracted
                                if (isset($ocrResult['vatTin']) && $ocrResult['vatTin'] !== 'VAT-TIN not found.') {
                                    // Save the extracted VAT-TIN to session
                                    $_SESSION['extractedVatTin'][$chatId] = $ocrResult['vatTin'];

                                    $ocrData = [
                                        'user_id' => $userId,
                                        'vat_tin' => $ocrResult['vatTin'],
                                        'msg_id' => $messageId,
                                        'raw_data' => $rawText,
                                        'file_id' => $fileId,
                                        'status' => 1, // Set initial status to 1 (e.g., VAT-TIN found)
                                        'date' => date('Y-m-d H:i:s')
                                    ];

                                    // Save OCR data to database
                                    $ezzeModel->addOcrData($ocrData);
                                    // Send the location request message along with the keyboard
                                    sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                } else {
                                    sendMessage($chatId, $messages[$language]['require_invoice_image'], $token);
                                }
                            } else {
                                // Handle unsupported image type for OCR
                                sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($_SESSION['currentCommand'][$chatId] === 'decode') {
                            // Check if the uploaded image is a barcode/QR code
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
                                    $_SESSION['imageType'][$chatId] = 'barcode';

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
                                        json_encode([
                                            'resize_keyboard' => true,
                                            'one_time_keyboard' => true,
                                        ]);

                                        // Send the location request message along with the keyboard
                                        sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $messages[$language]['require_barcode_image'], $token);
                                    // return;  // Exit silently if barcode decoding fails
                                }
                            } else {
                                // Handle unsupported image type for decoding
                                sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($_SESSION['currentCommand'][$chatId] !== 'decode' || $_SESSION['currentCommand'][$chatId] !== 'ocr') {
                            // Handle unsupported commands; check if image is a barcode
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
                                    $_SESSION['imageType'][$chatId] = 'barcode';

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
                                        json_encode([
                                            'resize_keyboard' => true,
                                            'one_time_keyboard' => true,
                                        ]);

                                        // Send the location request message along with the keyboard
                                        sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $messages[$language]['require_barcode_image'], $token);
                                    // return;  // Exit silently if barcode decoding fails
                                }
                            }
                        } else {
                            // Handle unsupported image type for decoding
                            sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                        }
                    } else {
                        sendMessage($chatId, $messages[$language]['file_retrieval_failed'], $token);
                    }
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
                    $imageType = $_SESSION['imageType'][$chatId] ?? null; // Get the image type

                    // Format the current date and time
                    $formattedDate = formatDate($language);
                    $formattedTime = formatTime($language);

                    // Prepare the Google Maps URL
                    $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";

                    // Format the barcode or VAT-TIN list for the response message
                    $responseList = '';
                    if ($imageType === 'barcode' && !empty($decodedBarcodes)) {
                        $responseList .= implode("\n", array_map(function ($barcode, $index) {
                            return ($index + 1) . ". <code><b>{$barcode['code']}</b></code>";
                        }, $decodedBarcodes, array_keys($decodedBarcodes))) . "\n";
                    }
                    if ($imageType === 'invoice' && !empty($vatTin)) {
                        if (is_array($vatTin)) {
                            // If VAT-TINs are in an array, loop through and add them to the response
                            foreach ($vatTin as $index => $tin) {
                                $responseList .= ($index + 1) . ". <code><b>{$tin}</b></code>\n";
                            }
                        } else {
                            // If it's a single VAT-TIN, just add it as before
                            $responseList .= "<code><b>{$vatTin}</b></code>\n";
                        }
                    }

                    // Get all barcode_ids from the session
                    $barcodeIds = array_column($decodedBarcodes, 'id'); // Extract all barcode IDs
                    $barcodeIdList = implode(',', $barcodeIds); // Create a comma-separated string of IDs

                    $ocrId = $_SESSION['extractedVatTin'][$chatId] ?? null; // Get the OCR ID from session if applicable

                    // Save location data to the database
                    $params = [
                        'user_id' => $userId,
                        'barcode_id' => $barcodeIdList, // Store all barcode IDs as a string
                        'ocr_id' => $ocrId,
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'location_url' => $locationUrl,
                        'date' => $date,
                        'share_status' => 1,
                    ];
                    $response = $ezzeModel->addLocation($params);
                    echo $response;

                    // Prepare the response message based on the image type
                    if ($imageType === 'barcode') {
                        $responseMessage = sprintf(
                            $messages[$language]['decoded_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    } elseif ($imageType === 'invoice') {
                        $responseMessage = sprintf(
                            $messages[$language]['extracted_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    }

                    // Send the location confirmation message
                    sendMessage($chatId, $responseMessage, $token);

                    // Clear the session variables for this chat
                    unset($_SESSION['currentCommand'][$chatId]);
                    unset($_SESSION['decodedBarcodes'][$chatId]);
                    unset($_SESSION['extractedVatTin'][$chatId]);
                    unset($_SESSION['imageType'][$chatId]); // Clear the image type
                } else {
                    sendMessage($chatId, $messages[$language]['contact_not_registered'], $token);
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

function isInvoiceImage($filePath)
{
    // Example: You can enhance this function as needed
    $allowedInvoiceExtensions = ['jpg', 'jpeg', 'png', 'pdf']; // Assuming PDFs are allowed
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedInvoiceExtensions);
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
