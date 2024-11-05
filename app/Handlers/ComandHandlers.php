<?php

include __DIR__ . '/../includes/IncludeCommands.php';

function processUpdates($updates, $token)
{
    global $currentMessages;
    static $userLanguages = [];
    global $baseLanguage;

    $decModel = new DecodeModel();
    $locModel = new LocationModel();
    $mrzModel = new MrzExtractModel();
    $ocrModel = new OcrExtractModel();
    $useModel = new UserProfiles();
    setCommands($token, $currentMessages);

    foreach ($updates as $update) {
        if (isset($update['message'])) {
            setCommands($token, $currentMessages);
            $chatId = $update['message']['chat']['id'];
            $userId = $update['message']['from']['id'];
            $firstName = $update['message']['from']['first_name'] ?? '';
            $lastName = $update['message']['from']['last_name'] ?? '';
            $username = $update['message']['from']['username'] ?? '';
            $messageId = $update['message']['message_id'];

            $language = $userLanguages[$chatId] ?? $useModel->getUserLanguage($chatId) ?? 'en';

            $currentMessages = $baseLanguage[$language];

            if (isset($update['message']['text'])) {
                $userCommand = $update['message']['text'];

                if ($userCommand === '/start') {

                    if ($useModel->checkUserExists($userId)) {

                        $welcomeMessage = sprintf($baseLanguage[$language]['welcome_message'], "<b>$firstName</b>", "<b>$lastName</b>");
                        sendMessage($chatId, $welcomeMessage, $token, ['parse_mode' => 'HTML']);
                    } else {

                        sendMessage($chatId, $baseLanguage[$language]['new_user_message'], $token);
                    }

                    $replyMarkup = json_encode([
                        'keyboard' => [
                            [
                                ['text' => $baseLanguage['en']['language_option']],
                                ['text' => $baseLanguage['kh']['language_option']]
                            ]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);

                    sendMessage($chatId, $baseLanguage[$language]['please_choose_language'], $token, $replyMarkup);
                }

                if (in_array($userCommand, [$baseLanguage['en']['language_option'], $baseLanguage['kh']['language_option']])) {

                    $language = $userCommand === $baseLanguage['en']['language_option'] ? 'en' : 'kh';

                    $userLanguages[$chatId] = $language;
                    $_SESSION['userLanguages'][$chatId] = $language;

                    $useModel->updateUserLanguage($chatId, $language);

                    if (isset($_SESSION['is_changing_language']) && $_SESSION['is_changing_language']) {


                        sendMessage($chatId, $baseLanguage[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));
                        unset($_SESSION['is_changing_language']);
                    } else {

                        // $responseText = $language === 'en' ? "You have selected English" : "អ្នកបានជ្រើសរើសភាសាខ្មែរ";
                        sendMessage($chatId, $baseLanguage[$language]['language_selection'], $token, json_encode(['remove_keyboard' => true]));

                        if ($useModel->tgUsername($userId) === null) {

                            showContactSharing($chatId, $token, $language);
                        } else {

                            sendMessage($chatId, $baseLanguage[$language]['upload_barcode'], $token);
                        }
                    }

                    if (!isset($_SESSION['language_selected'])) {
                        $_SESSION['language_selected'] = true;
                    }

                    setCommands($token, $currentMessages);

                    continue;
                }

                if ($userCommand === '/change_language') {

                    $_SESSION['is_changing_language'] = true;

                    if (!$useModel->checkUserExists($userId)) {
                        showContactSharing($chatId, $token, $language);
                    } else {
                        $replyMarkup = json_encode([
                            'keyboard' => [
                                [
                                    ['text' => $baseLanguage['en']['language_option']],
                                    ['text' => $baseLanguage['kh']['language_option']]
                                ]
                            ],
                            'resize_keyboard' => true,
                            'one_time_keyboard' => true,
                        ]);

                        sendMessage($chatId, $baseLanguage[$language]['please_choose_language'], $token, $replyMarkup);
                    }
                    continue;
                }


                if ($userCommand === '/share_contact') {
                    if (!$useModel->checkUserExists($userId)) {
                        showContactSharing($chatId, $token, $language);
                        setCommands($token, $currentMessages);
                    } else {
                        if (!$useModel->hasSelectedLanguage($userId)) {
                            sendMessage($chatId, $baseLanguage['en']['please_select_language'], $token);
                        } else {
                            $params = [
                                'chat_id' => $chatId,
                                'msg_id' => $messageId,
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'username' => $username,
                                'phone_number' => $update['message']['contact']['phone_number'],
                                'date' => date('Y-m-d H:i:s'),
                                'language' => $language
                            ];

                            if ($useModel->getUserLanguage($chatId) !== $language) {
                                $useModel->updateUserLanguage($userId, $language);
                            }
                            $useModel->updateUser($params);
                        }
                    }
                }

                // Handle /decode command only if the user's contact is registered
                if ($userCommand === '/decode') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['upload_barcode'], $token);
                        $_SESSION['currentCommand'][$chatId] = 'decode';
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                        sendMessage($chatId, $baseLanguage[$language]['share_contact_prompt'], $token);
                    }
                }

                if ($userCommand === '/ocr') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['upload_invoice'], $token);
                        $_SESSION['currentCommand'][$chatId] = 'ocr';
                    } else {

                        sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                    }
                }

                if ($userCommand === '/mrz') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['upload_mrz'], $token);
                        $_SESSION['currentCommand'][$chatId] = 'mrz';
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                    }
                }

                if ($userCommand === '/share_location') {
                    if ($useModel->checkUserExists($userId)) {
                        if ($decModel->hasCompletedDecode($userId)) {
                            setCommands($token, $currentMessages);
                            sendMessage($chatId, $baseLanguage[$language]['location_prompt'], $token);
                        } else {
                            sendMessage($chatId, $baseLanguage[$language]['decode_not_completed'], $token);
                        }
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                    }
                }

                if ($userCommand === '/menu') {
                    if ($useModel->checkUserExists($userId)) {
                        sendMessage($chatId, $baseLanguage[$language]['menu'], $token);
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                    }
                }
            }

            if (isset($update['message']['contact'])) {
                if (!$useModel->checkUserExists($userId) && !$useModel->hasSelectedLanguage($userId)) {
                    setCommands($token, $currentMessages);

                    $userId = $update['message']['from']['id'];
                    $messageId = $update['message']['message_id'];
                    $chatId = $update['message']['chat']['id'];
                    $language = $userLanguages[$chatId] ?? 'en';
                    $contact = $update['message']['contact'];
                    $phoneNumber = $contact['phone_number'];
                    $firstName = $contact['first_name'];
                    $lastName = $contact['last_name'] ?? '';
                    $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";

                    $response = $useModel->registerUsers([
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

                    $responseMessage = sprintf(
                        $baseLanguage[$language]['thanks_for_contact'],
                        $firstName,
                        $lastName,
                        $phoneNumber,
                        $username
                    );
                    sendMessage($chatId, $responseMessage, $token);
                    sendMessage($chatId, $baseLanguage[$language]['upload_barcode'], $token, json_encode(['remove_keyboard' => true]));

                    $_SESSION['contact_shared'][$chatId] = true;
                    $_SESSION['currentChatId'] = $chatId;
                    setCommands($token, $currentMessages);
                    continue;
                } else {
                    sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                }
            }

            // Handle image upload (Barcode / QR code or Invoice)
            if (isset($update['message']['photo'])) {
                if ($useModel->checkUserExists($userId) && $useModel->hasSelectedLanguage($userId)) {
                    setCommands($token, $currentMessages);

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

                        if ($_SESSION['currentCommand'][$chatId] === 'ocr') {
                            if (isInvoiceImage($localFilePath)) {
                                require_once __DIR__ . '/../includes/functions/OCRFunction.php';
                                $ocrResult = processInvoiceImage($localFilePath);
                                $_SESSION['imageType'][$chatId] = 'invoice';
                                $rawText = $ocrResult['text'];

                                if (isset($ocrResult['vatTin']) && $ocrResult['vatTin'] !== 'VAT-TIN not found.') {
                                    if (!isset($_SESSION['extractedVatTin'][$chatId])) {
                                        $_SESSION['extractedVatTin'][$chatId] = $ocrResult['vatTin'];

                                        $ocrData = [
                                            'user_id' => $userId,
                                            'vat_tin' => $ocrResult['vatTin'],
                                            'msg_id' => $messageId,
                                            'raw_data' => $rawText,
                                            'file_id' => $fileId,
                                            'status' => 1,
                                            'date' => date('Y-m-d H:i:s')
                                        ];

                                        // Save OCR data to database
                                        $ocrModel->addOcrData($ocrData);
                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $baseLanguage[$language]['require_invoice_image'], $token);
                                }
                            } else {
                                sendMessage($chatId, $baseLanguage[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($_SESSION['currentCommand'][$chatId] === 'mrz') {
                            if (isMrzImage($localFilePath)) {
                                require_once __DIR__ . '/../includes/functions/MRZFunction.php';
                                $mrzResult = processMrzImage($localFilePath);
                                $_SESSION['imageType'][$chatId] = 'mrz';


                                if (isset($mrzResult['mrzData']) && !empty($mrzResult['mrzData'])) {
                                    if (!isset($_SESSION['extractedMrz'][$chatId])) {
                                        $_SESSION['extractedMrz'][$chatId] = $mrzResult['mrzData'];

                                        $mrzModel->addMRZData([
                                            'user_id' => $userId,
                                            'mrz_line1' => $mrzResult['mrzData'][0] ?? '',
                                            'mrz_line2' => $mrzResult['mrzData'][1] ?? '',
                                            'mrz_line3' => $mrzResult['mrzData'][2] ?? '',
                                            'msg_id' => $messageId,
                                            'file_id' => $fileId,
                                            'decoded_status' => 1,
                                            'date' => date('Y-m-d H:i:s')
                                        ]);

                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $baseLanguage[$language]['require_mrz_image'], $token);
                                }
                            } else {
                                sendMessage($chatId, $baseLanguage[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($_SESSION['currentCommand'][$chatId] === 'decode') {
                            if (isBarcodeImage($localFilePath)) {
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
                                    $decModel->addBarcode([
                                        'user_id' => $userId,
                                        'type' => $type,
                                        'code' => $code,
                                        'msg_id' => $messageId,
                                        'file_id' => $fileId,
                                        'file_unique_id' => $fileUniqueId,
                                        'decoded_status' => 1,
                                    ]);

                                    if (count($_SESSION['decodedBarcodes'][$chatId]) == 1) {
                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $baseLanguage[$language]['require_barcode_image'], $token);
                                }
                            } else {
                                sendMessage($chatId, $baseLanguage[$language]['unsupported_image_type'], $token);
                            }
                        } elseif ($_SESSION['currentCommand'][$chatId] !== 'decode' || $_SESSION['currentCommand'][$chatId] !== 'ocr') {
                            if (isBarcodeImage($localFilePath)) {

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


                                    $decModel->addBarcode([
                                        'user_id' => $userId,
                                        'type' => $type,
                                        'code' => $code,
                                        'msg_id' => $messageId,
                                        'file_id' => $fileId,
                                        'file_unique_id' => $fileUniqueId,
                                        'decoded_status' => 1,
                                    ]);


                                    if (count($_SESSION['decodedBarcodes'][$chatId]) == 1) {
                                        json_encode([
                                            'resize_keyboard' => true,
                                            'one_time_keyboard' => true,
                                        ]);

                                        sendMessage($chatId, $baseLanguage[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    }
                                } else {
                                    sendMessage($chatId, $baseLanguage[$language]['require_barcode_image'], $token);
                                }
                            }
                        } else {
                            sendMessage($chatId, $baseLanguage[$language]['unsupported_image_type'], $token);
                        }
                    } else {
                        sendMessage($chatId, $baseLanguage[$language]['file_retrieval_failed'], $token);
                    }
                }
            }

            if (isset($update['message']['location'])) {
                if ($useModel->checkUserExists($userId) && $useModel->hasSelectedLanguage($userId)) {
                    $userId = $update['message']['from']['id'];
                    $chatId = $update['message']['chat']['id'];
                    $latitude = $update['message']['location']['latitude'];
                    $longitude = $update['message']['location']['longitude'];
                    $date = date('Y-m-d H:i:s');

                    // Retrieve the decoded barcodes or VAT-TIN stored in session
                    $decodedBarcodes = $_SESSION['decodedBarcodes'][$chatId] ?? [];
                    $vatTin = $_SESSION['extractedVatTin'][$chatId] ?? [];
                    $mrzData = $_SESSION['extractedMrz'][$chatId] ?? [];
                    $imageType = $_SESSION['imageType'][$chatId] ?? null;

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

                    if ($imageType === 'invoice' && !empty($vatTin)) {
                        if (is_array($vatTin)) {
                            foreach ($vatTin as $index => $tin) {
                                $responseList .= ($index + 1) . ". <code><b>{$tin}</b></code>\n";
                            }
                        } else {
                            $responseList .= "<code><b>{$vatTin}</b></code>\n";
                        }
                    }
                    // Include MRZ data if available and format based on the number of lines
                    if ($imageType === 'mrz' && !empty($mrzData)) {
                        $lineCount = count($mrzData);
                        $responseList .= "MRZ:\n";
                        foreach ($mrzData as $index => $line) {
                            $responseList .= sprintf("line%d. <code><b>%s</b></code>\n", $index + 1, htmlspecialchars($line));
                        }
                    }

                    $params = [
                        'user_id' => $userId,
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'location_url' => $locationUrl,
                        'date' => $date,
                        'share_status' => 1,
                    ];
                    $response = $locModel->addLocation($params);
                    echo $response;

                    if ($imageType === 'barcode') {
                        $responseMessage = sprintf(
                            $baseLanguage[$language]['decoded_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    } elseif ($imageType === 'invoice') {
                        $responseMessage = sprintf(
                            $baseLanguage[$language]['extracted_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    } elseif ($imageType === 'mrz') {
                        $responseMessage = sprintf(
                            $baseLanguage[$language]['mrz_location_shared'],
                            $formattedDate,
                            $formattedTime,
                            $responseList,
                            $locationUrl
                        );
                    }

                    sendMessage($chatId, $responseMessage, $token);

                    unset($_SESSION['currentCommand'][$chatId]);
                    unset($_SESSION['decodedBarcodes'][$chatId]);
                    unset($_SESSION['extractedVatTin'][$chatId]);
                    unset($_SESSION['extractedMrz'][$chatId]);
                    unset($_SESSION['imageType'][$chatId]);
                } else {
                    sendMessage($chatId, $baseLanguage[$language]['contact_not_registered'], $token);
                }
            }
        }
    }
}


include __DIR__ . '/../includes/functions/IncFunction.php';
include __DIR__ . '/../includes/functions/polling.php';
