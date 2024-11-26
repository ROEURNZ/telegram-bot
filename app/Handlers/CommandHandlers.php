<?php

include __DIR__ . '/../includes/IncludeCommands.php';
include __DIR__ . '/../includes/functions/SetUserCommandFunction.php';

function processUpdates($updates, $token)
{
    global $activeLanguage;
    static $userLanguages = [];
    global $baseLanguage;
    global $userState;
    $userState = $userState ?? [];

    $decModel = new DecodeModel();
    $mrzModel = new MrzExtractModel();

    $ocrModel = new OcrExtractModel();
    $useModel = new UserProfiles();

    foreach ($updates as $update) {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $userId = $update['message']['from']['id'];
            $firstName = $update['message']['from']['first_name'] ?? '';
            $lastName = $update['message']['from']['last_name'] ?? '';
            $username = $update['message']['from']['username'] ?? '';
            $messageId = $update['message']['message_id'];
            $date = date('Y-m-d H:i:s');
            $userState['currentCommand'][$chatId] = $userState['currentCommand'][$chatId] ?? '';

            $language = $userLanguages[$chatId] ?? $useModel->getUserLanguage($chatId) ?? 'en';
            $activeLanguage = $baseLanguage[$language];
            if (isset($update['message']['text'])) {
                $userCommand = $update['message']['text'];
                if ($userCommand === '/start') {
                    if ($useModel->checkUserExists($userId)) {

                        $existingLanguage = $useModel->getUserLanguage($userId);
                        $welcomeMessage = sprintf(
                            $baseLanguage[$existingLanguage]['welcome_message'],
                            "<b>$firstName</b>",
                            "<b>$lastName</b>"
                        );
                        sendMessage($chatId, $welcomeMessage, $token, ['parse_mode' => 'HTML']);
                    } else {
                        // Register new user
                        $phone = $update['message']['contact']['phone_number'];
                        $params = [
                            'user_id' => $userId,
                            'chat_id' => $chatId,
                            'msg_id' => $messageId,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'username' => $username,
                            'phone_number' => null,
                            'date' => $date,
                            'language' => 'en',
                        ];
                        $registrationStatus = $useModel->registerUser($params);

                        if ($registrationStatus === true) {
                            sendMessage($chatId, $baseLanguage['en']['new_user_message'], $token);
                        }
                        // Prompt for language selection
                        selectLanguage($chatId, $language, $baseLanguage, $token);
                    }
                }
                if (in_array($userCommand, [$baseLanguage['en']['language_option'], $baseLanguage['kh']['language_option']])) {
                    $language = $userCommand === $baseLanguage['en']['language_option'] ? 'en' : 'kh';
                    $userLanguages[$chatId] = $language;

                    $useModel->updateUserLanguage($chatId, $language);
      

                    // Use $userState to manage language change status
                    if (isset($userState['is_changing_language'][$chatId]) && $userState['is_changing_language'][$chatId]) {
                        sendMessage($chatId, $baseLanguage[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));
                    }
                     else {
                        sendMessage($chatId, $baseLanguage[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));
                        $phone = $update['message']['contact']['phone_number'];
                        if (!$useModel->checkUserPhoneNumberExists($userId, $phone)) {
                            sendInlineKeyboard($chatId, $language, $baseLanguage, $token);
                            setCommands($token, $activeLanguage);
                        }
                    }
                    continue;
                }

                if ($userCommand === '/change_language') {
                    $userState['is_changing_language'][$chatId] = true;

                    if (!$useModel->checkUserExists($userId)) {
                        // showContactSharing($chatId, $language, $baseLanguage, $token);
                    } else {
                        selectLanguage($chatId, $language, $baseLanguage, $token);
                        setCommands($token, $activeLanguage);
                    }
                    continue;
                }

                if ($userCommand === '/share_contact') {
                    if (!$useModel->checkUserPhoneNumberExists($userId, $phone)) {
                        sendInlineKeyboard($chatId, $language, $baseLanguage, $token);
                        setCommands($token, $activeLanguage);
                    } else {
                        if (!$useModel->selectedLanguage($userId)) {
                            selectLanguage($chatId, $language, $baseLanguage, $token);
                        } else {
                            $phone = $update['message']['contact']['phone_number'];
                            $params = [
                                'chat_id' => $chatId,
                                'msg_id' => $messageId,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'username' => $username,
                                'phone_number' => $phone,
                                'date' => $date,
                                'language' => $language ?? 'en',
                            ];

                            if ($useModel->getUserLanguage($chatId) !== $language) {
                                $useModel->updateUserLanguage($userId, $language);
                                sendMessage($chatId, $baseLanguage[$language]['language_changed'], $token);
                            }
                        }
                    }
                }

                // Handle /decode command only if the user's contact is registered
                if ($userCommand === '/decode') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['upload_barcode'], $token);
                        $userState['currentCommand'][$chatId] = 'decode';
                    }
                }

                // Handle /ocr command similarly
                if ($userCommand === '/ocr') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['upload_invoice'], $token);
                        $userState['currentCommand'][$chatId] = 'ocr';
                    }
                }

                // Handle /mrz command similarly
                if ($userCommand === '/mrz') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['upload_mrz'], $token);
                        $userState['currentCommand'][$chatId] = 'mrz';
                    }
                }


                if ($userCommand === '/share_location') {
                    if ($useModel->checkUserExists($userId)) {
                        if ($decModel->hasCompletedDecode($userId)) {
                            setCommands($token, $activeLanguage);
                            sendMessage($chatId, $baseLanguage[$language]['location_prompt'], $token);
                        } else {
                            sendMessage($chatId, $baseLanguage[$language]['decode_not_completed'], $token);
                        }
                    }
                }

                if ($userCommand === '/menu') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['menu'], $token);
                    }
                }
            }

            if (isset($update['message']['contact'])) {
                setCommands($token, $activeLanguage);
                $userState['contact_shared'][$chatId] = true;
                $userState['currentChatId'] = $chatId;
                $chatId = $update['message']['chat']['id'];
                $userId = $update['message']['from']['id'];
                $messageId = $update['message']['message_id'];
                $language = $userLanguages[$chatId] ?? 'en';
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'];
                $userUrl = "https://t.me/{$username}";

                $params = [
                    'user_id' => $userId,
                    'chat_id' => $chatId,
                    'msg_id' => $messageId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'phone_number' => $phoneNumber,
                    'date' => date('Y-m-d H:i:s'),
                    'language' => $language,
                ];
                $useModel->updateUser($params);

                $responseMessage = sprintf(
                    $baseLanguage[$language]['thanks_for_contact'],
                    $firstName,
                    $lastName,
                    $phoneNumber,
                    $userUrl
                );
                sendMessage($chatId, $responseMessage, $token, json_encode(['remove_keyboard' => true]));

            }

            if (isset($update['message']['photo'])) {
                if ($useModel->checkUserExists($userId) && $useModel->selectedLanguage($userId)) {
                    setCommands($token, $activeLanguage);

                    $photo = end($update['message']['photo']);
                    $chatId = $update['message']['chat']['id'];
                    $userId = $update['message']['from']['id'];
                    $messageId = $update['message']['message_id'];
                    $fileId = $photo['file_id'];
                    $fileUniqueId = $photo['file_unique_id'];
                    $fileData = file_get_contents("https://api.telegram.org/bot{$token}/getFile?file_id={$fileId}");
                    $fileData = json_decode($fileData, true);
                    if (isset($fileData['result']['file_path'])) {
                        $filePath = $fileData['result']['file_path'];
                        $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";
                        $imagesPath = __DIR__ . "/../../storage/app/public/images/decoded/";

                        $downloadedImage = file_get_contents($fileUrl);
                        $localFilePath = $imagesPath . basename($filePath);

                        if (!is_dir($imagesPath)) {
                            mkdir($imagesPath, 0777, true);
                        }

                        file_put_contents($localFilePath, $downloadedImage);

                        // Handle unsupported commands or image uploads when no specific command is set
                        if ($userState['currentCommand'][$chatId] === 'mrz') {
                            if (isAllowedImage($localFilePath)) {
                                require_once __DIR__ . '/../includes/functions/MRZFunction.php';
                                $mrzResult = processMrzImage($localFilePath);
                                $userState['imageType'][$chatId] = 'mrz';

                                // Check if MRZ data exists
                                if (isset($mrzResult['mrzData']) && !empty($mrzResult['mrzData'])) {
                                    if (!isset($userState['extractedMrz'][$chatId])) {
                                        $userState['extractedMrz'][$chatId] = $mrzResult['mrzData'];
                                        $mrzCode = $mrzResult['mrzData'];
                                        $rawMrzData = $mrzResult['text'];

                                        // Only store MRZ data in the database if $mrzCode is not empty
                                        if (!empty($mrzCode)) {
                                            $mrzModel->addMRZData([
                                                'user_id' => $userId,
                                                'mrz_raw' => $rawMrzData,
                                                'uic_data' => $mrzCode,
                                                'msg_id' => $messageId,
                                                'file_id' => $fileId,
                                                'mrz_status' => 1,
                                                'date' => date('Y-m-d H:i:s')
                                            ]);

                                            sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                        } else {
                                            // If MRZ code is empty, don't store and send decode failed message
                                            sendMessage($chatId, $baseLanguage[$language]['decode_failed'], $token);
                                        }
                                    }
                                } else {
                                    // If MRZ data is not found, do not store anything and notify the user
                                    sendMessage($chatId, $baseLanguage[$language]['decode_failed'], $token);
                                }
                            } else {
                                sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($userState['currentCommand'][$chatId] === 'ocr') {
                            // Check if the uploaded image is an invoice
                            if (isAllowedImage($localFilePath)) {
                                // Process the invoice image
                                require_once __DIR__ . '/../includes/functions/OCRFunction.php';
                                $ocrResult = processInvoiceImage($localFilePath);
                                $userState['imageType'][$chatId] = 'invoice';
                                $ocrhasvat = $ocrResult['ocrhasvat'];
                                $ocrData = $ocrResult['taxIdentifiers'] ?? [];
                                $tin = $ocrResult['tin'];
                                $tin = ($ocrhasvat === 1 && $tin) ? $tin : null;

                                // Store OCR data for each image
                                if (!isset($userState['extractedVatTin'][$chatId])) {
                                    $userState['extractedVatTin'][$chatId] = [];
                                }
                                $userState['extractedVatTin'][$chatId][] = $ocrData;

                                // Save OCR data to the database, even if tin is empty
                                $ocrModel->addOcrData([
                                    'user_id' => $userId,
                                    'tin' => $tin,
                                    'msg_id' => $messageId,
                                    'raw_data' => $ocrResult['rawData'],
                                    'file_id' => $fileId,
                                    'ocrtext' => !empty($ocrResult['rawData']) ? 1 : 0,
                                    'ocrhasvat' => $ocrhasvat,
                                    'taxincluded' => 1,
                                    'date' => date('Y-m-d H:i:s')
                                ]);

                                // Notify user with appropriate response
                                if (!empty($ocrData)) {
                                    if (is_array($userState['extractedVatTin'][$chatId]) && count($userState['extractedVatTin'][$chatId]) == 1) {
                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    // Message indicating OCR processing but no data found
                                    sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                }
                            } else {
                                // Handle unsupported image type for OCR
                                sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($userState['currentCommand'][$chatId] === 'decode') {
                            // Check if the uploaded image is a barcode/QR code
                            if (isAllowedImage($localFilePath)) {
                                // Process the barcode image
                                require_once __DIR__ . '/../includes/functions/DecodeFunction.php';
                                $decodedBarcodeData = processBarcodeImage($localFilePath);

                                if (isset($decodedBarcodeData['code'])) {
                                    $code = $decodedBarcodeData['code'];
                                    $type = $decodedBarcodeData['type'];

                                    // Save decoded barcode to session
                                    if (!isset($userState['decodedBarcodes'][$chatId])) {
                                        $userState['decodedBarcodes'][$chatId] = [];
                                    }
                                    $userState['decodedBarcodes'][$chatId][] = $decodedBarcodeData;
                                    $userState['imageType'][$chatId] = 'barcode';

                                    // Insert the barcode record into the database
                                    $decModel->addBarcode([
                                        'user_id' => $userId,
                                        'type' => $type,
                                        'code' => $code,
                                        'msg_id' => $messageId,
                                        'file_id' => $fileId,
                                        'file_unique_id' => $fileUniqueId,
                                        'decoded_status' => 1,
                                    ]);

                                    if (is_array($userState['decodedBarcodes'][$chatId]) && count($userState['decodedBarcodes'][$chatId]) == 1) {
                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $baseLanguage[$language]['decode_failed'], $token);
                                }
                            } else {
                                // Handle unsupported image type for decoding
                                sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($userState['currentCommand'][$chatId] !== 'decode' || $userState['currentCommand'][$chatId] !== 'ocr' || $userState['currentCommand'][$chatId] !== 'mrz') {
                            // Handle unsupported command
                            sendMessage($chatId, $baseLanguage[$language]['commands_suggestion'], $token);
                        }
                    }
                }
            }

            // Handling location data
            if (isset($update['message']['location'])) {
                if ($useModel->checkUserExists($userId) && $useModel->selectedLanguage($userId)) {
                    $userId = $update['message']['from']['id'];
                    $chatId = $update['message']['chat']['id'];
                    $latitude = $update['message']['location']['latitude'];
                    $longitude = $update['message']['location']['longitude'];
                    $date = date('Y-m-d H:i:s');

                    // Get previously decoded data based on image type
                    $decodedBarcodes = $userState['decodedBarcodes'][$chatId] ?? [];
                    $ocrData = $userState['extractedVatTin'][$chatId] ?? [];
                    $mrzData = $userState['extractedMrz'][$chatId] ?? [];
                    $imageType = $userState['imageType'][$chatId] ?? null;

                    // Format the current date and time
                    $formattedDate = formatDate($language);
                    $formattedTime = formatTime($language);
                    $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";

                    $responseList = '';
                    if ($imageType === 'barcode' && !empty($decodedBarcodes)) {
                        $responseList .= implode("\n", array_map(function ($barcode, $index) {
                            return ($index + 1) . ". <code><b>{$barcode['code']}</b></code>";
                        }, $decodedBarcodes, array_keys($decodedBarcodes))) . "\n";
                    }

                    // Handling OCR response
                    if (!empty($ocrData)) {
                        $counter = 1;
                        foreach ($ocrData as $identifiers) {
                            foreach ($identifiers as $identifier) {
                                $codeLength = strlen($identifier['code']);

                                // Only include VAT-TINs with code length >= 10
                                if ($codeLength >= 10) {
                                    $responseList .= sprintf(
                                        "%d. <code><b>%s:</b> %s</code>\n",
                                        $counter++,
                                        htmlspecialchars($identifier['prefix']),
                                        htmlspecialchars($identifier['code'])
                                    );
                                }
                            }
                        }
                    }


                    // Include MRZ data if available and format based on the number of lines
                    if ($imageType === 'mrz' && !empty($mrzData)) {
                        $responseList .= "MRZ UIC: <code><b>" . htmlspecialchars($mrzData) . "</b></code>\n";
                    }

                    $params = [
                        'user_id' => $userId,
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'location_url' => $locationUrl,
                        'date' => $date,
                    ];


                    if (isset($tin) && !empty($tin)) {
                        $params['tin'] = $tin;
                    }

                    // Handle saving location data and sending response based on image type
                    if ($imageType === 'barcode') {
                        $decModel->addLocationDecode($params);
                        $responseMessage = sprintf(
                            $baseLanguage[$language]['decoded_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    } elseif ($imageType === 'invoice') {
                        $ocrModel->addLocationOcr($params);
                        $responseMessage = sprintf(
                            $baseLanguage[$language]['extracted_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    } elseif ($imageType === 'mrz') {
                        $mrzModel->addLocationMrz($params);
                        $responseMessage = sprintf(
                            $baseLanguage[$language]['mrz_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    }

                    sendMessage($chatId, $responseMessage, $token);
                    sendMessage($chatId, $baseLanguage[$language]['thank_you'], $token);

                    unset($userState['currentCommand'][$chatId]);
                    unset($userState['decodedBarcodes'][$chatId]);
                    unset($userState['extractedVatTin'][$chatId]);
                    unset($userState['extractedMrz'][$chatId]);
                    unset($userState['imageType'][$chatId]);
                } else {
                    sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                }
            }
        }

        handleCallbackQuery($update, $baseLanguage, $userLanguages, $token); // to call the callback_query
    }
}


include __DIR__ . '/../includes/functions/IncFunction.php';
include __DIR__ . '/../includes/functions/polling.php';
