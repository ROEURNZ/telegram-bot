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
            $userState['currentCommand'][$chatId] = $userState['currentCommand'][$chatId] ?? '';

            $language = $userLanguages[$chatId] ?? $useModel->getUserLanguage($chatId) ?? 'en';
            $activeLanguage = $baseLanguage[$language];
            if (isset($update['message']['text'])) {
                $userCommand = $update['message']['text'];
                if ($userCommand === '/start') {

                    if ($useModel->checkUserExists($userId)) {
                        $welcomeMessage = sprintf($baseLanguage[$language]['welcome_message'], "<b>$firstName</b>", "<b>$lastName</b>");
                        sendMessage($chatId, $welcomeMessage, $token, ['parse_mode' => 'HTML']);
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['new_user_message'], $token);
                    }
 
                    selectLanguage($chatId, $language, $baseLanguage, $token);
                }

                if (in_array($userCommand, [$baseLanguage['en']['language_option'], $baseLanguage['kh']['language_option']])) {
                    $language = $userCommand === $baseLanguage['en']['language_option'] ? 'en' : 'kh';
                    $userLanguages[$chatId] = $language;

                    $useModel->updateUserLanguage($chatId, $language);

                    // Use $userState to manage language change status
                    if (isset($userState['is_changing_language'][$chatId]) && $userState['is_changing_language'][$chatId]) {
                        sendMessage($chatId, $baseLanguage[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));
                        if ($useModel->getUsername($userId) === null) {
                            showContactSharing($chatId, $language, $baseLanguage, $token);
                        } else {
                            if ($useModel->checkUserExists($userId)) {
                                setCommands($token, $activeLanguage);
                                sendMessage($chatId, $baseLanguage[$language]['upload_barcode'], $token);
                            }
                        }
                    }
                    continue;
                }

                if ($userCommand === '/change_language') {
                    $userState['is_changing_language'][$chatId] = true;

                    if (!$useModel->checkUserExists($userId)) {
                        showContactSharing($chatId, $language, $baseLanguage, $token);
                    } 
                    else {
                        selectLanguage($chatId, $language, $baseLanguage, $token);
                        setCommands($token, $activeLanguage);
                    }

                    continue;
                }

                if ($userCommand === '/share_contact') {
                    if (!$useModel->checkUserExists($userId)) {
                        showContactSharing($chatId, $language, $baseLanguage, $token);
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
                                'date' => date('Y-m-d H:i:s'),
                                'language' => $language ?? 'en',
                            ];
                
                            if ($useModel->getUserLanguage($chatId) !== $language) {
                                $useModel->updateUserLanguage($userId, $language);
                                sendMessage($chatId, $baseLanguage[$language]['language_changed'], $token);
                            }
                
                            $useModel->updateUser($params);
                
                            showContactSharing($chatId, $language, $baseLanguage, $token);
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
                            showLocationSharing($chatId, $language, $baseLanguage, $token);
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
                if (!$useModel->checkUserExists($userId) && !$useModel->selectedLanguage($userId)) {

                    setCommands($token, $activeLanguage);
                    $userState['contact_shared'][$chatId] = true;
                    $userState['currentChatId'] = $chatId;

                    $userId = $update['message']['from']['id'];
                    $messageId = $update['message']['message_id'];
                    $chatId = $update['message']['chat']['id'];
                    $language = $userLanguages[$chatId] ?? 'en';
                    $contact = $update['message']['contact'];
                    $phoneNumber = $contact['phone_number'];
                    $firstName = $contact['first_name'];
                    $lastName = $contact['last_name'] ?? '';
                    $username = $update['message']['from']['username'];
                    $userUrl = "https://t.me/{$username}";

                    $useModel->registerUser([
                        'user_id' => $userId,
                        'chat_id' => $chatId,
                        'msg_id' => $messageId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $update['message']['from']['username'],
                        'phone_number' => $phoneNumber,
                        'date' => date('Y-m-d H:i:s'),
                        'language' => $language ?? 'en',
                    ]);

                    $responseMessage = sprintf(
                        $baseLanguage[$language]['thanks_for_contact'],
                        $firstName,
                        $lastName,
                        $phoneNumber,
                        $userUrl
                    );
                    sendMessage($chatId, $responseMessage, $token);
                    sendMessage($chatId, $baseLanguage[$language]['upload_barcode'], $token, json_encode(['remove_keyboard' => true]));

                    continue;
                } else {
                    sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                }
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

                        if ($userState['currentCommand'][$chatId] === 'ocr' || ($userState['currentCommand'][$chatId] !== 'decode') || ($userState['currentCommand'][$chatId] !== 'mrz') || ($userState['currentCommand'][$chatId] !== '')) {
                            if (isAllowedImage($localFilePath)) {
                                require_once __DIR__ . '/../includes/functions/OCRFunction.php';
                                $ocrResult = processInvoiceImage($localFilePath);
                                $userState['imageType'][$chatId] = 'invoice';
                                $rawText = $ocrResult['rawData'];

                                // If VAT-TIN found, process and store OCR results
                                if (isset($ocrResult['taxIdentifiers']) && !empty($ocrResult['taxIdentifiers'])) {
                                    if (!isset($userState['extractedVatTin'][$chatId])) {
                                        $userState['extractedVatTin'][$chatId] = $ocrResult['taxIdentifiers'];
                                        $tin = implode(", ", array_column($ocrResult['taxIdentifiers'], 'code'));

                                        $ocrModel->addOcrData([
                                            'user_id' => $userId,
                                            'tin' => $tin,
                                            'msg_id' => $messageId,
                                            'raw_data' => $rawText,
                                            'file_id' => $fileId,
                                            'ocrtext' => 1,
                                            'ocrhasvat' => 1,
                                            'taxincluded' => 1,
                                            'date' => date('Y-m-d H:i:s')
                                        ]);

                                        if (is_array($userState['extractedVatTin'][$chatId]) && count($userState['extractedVatTin'][$chatId]) == 1) {
                                            sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                        }
                                    }
                                }
                            }
                        }
                        // Assuming you have an array $userState to hold user-specific information
                        if (($userState['currentCommand'][$chatId] === 'mrz')) {
                            if (isAllowedImage($localFilePath)) {
                                require_once __DIR__ . '/../includes/functions/MRZFunction.php';
                                $mrzResult = processMrzImage($localFilePath);
                                $userState['imageType'][$chatId] = 'mrz';

                                if (isset($mrzResult['mrzData']) && !empty($mrzResult['mrzData'])) {
                                    if (!isset($userState['extractedMrz'][$chatId])) {
                                        $userState['extractedMrz'][$chatId] = $mrzResult['mrzData'];
                                        $mrzCode = $mrzResult['mrzData'];
                                        $rawMrzData = $mrzResult['text'];

                                        // Save MRZ data to database
                                        $mrzModel->addMRZData([
                                            'user_id' => $userId,
                                            'mrz_raw' => $rawMrzData,
                                            'uic_data' => $mrzCode,
                                            'msg_id' => $messageId,
                                            'file_id' => $fileId,
                                            'mrz_status' => 1,
                                            'date' => date('Y-m-d H:i:s')
                                        ]);

                                        // if (is_array($userState['extractedMrz'][$chatId]) && count($userState['extractedMrz'][$chatId]) == 1) {
                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                        // }
                                    }
                                }
                            }
                        }
                        if ($userState['currentCommand'][$chatId] === 'decode' || ($userState['currentCommand'][$chatId] !== 'ocr') || ($userState['currentCommand'][$chatId] !== 'mrz') || ($userState['currentCommand'][$chatId] !== '')) {
                            if (isAllowedImage($localFilePath)) {
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
                                }
                            }
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
                    if ($imageType === 'invoice' && !empty($ocrData)) {
                        foreach ($ocrData as $index => $identifier) {
                            $responseList .= sprintf(
                                "%d. <code><b>%s:</b> %s</code>\n",
                                $index + 1,
                                htmlspecialchars($identifier['prefix']),
                                htmlspecialchars($identifier['code'])
                            );
                        }
                    }

                    // Include MRZ data if available and format based on the number of lines
                    if ($imageType === 'mrz' && !empty($mrzData)) {
                        $responseList .= "MRZ UIC:\n";
                        $responseList .= "<code><b>" . htmlspecialchars($mrzData) . "</b></code>\n";
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
    }
}


include __DIR__ . '/../includes/functions/IncFunction.php';
include __DIR__ . '/../includes/functions/polling.php';
