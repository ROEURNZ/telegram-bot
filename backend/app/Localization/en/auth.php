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
                                sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                            } else {
                                // Handle barcode decoding failure
                                sendMessage($chatId, "Couldn't resolve this image, please try again.", $token);
                                unset($_SESSION['decodedBarcodes'][$chatId]);
                            }
                        }elseif ($_SESSION['currentCommand'][$chatId]) {
                            if ($_SESSION['currentCommand'][$chatId] === 'decode') {
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
                                        sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    } else {
                                        // Handle barcode decoding failure
                                        sendMessage($chatId, "Couldn't resolve this image, please try again.", $token);
                                        unset($_SESSION['decodedBarcodes'][$chatId]);
                                    }
                                } else {
                                    // Handle unsupported image type for decoding
                                    sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                                }
                            }elseif ($_SESSION['currentCommand'][$chatId] === 'ocr') {
                                // Check if the uploaded image is an invoice
                                if (isInvoiceImage($localFilePath)) {
                                    // Process the invoice image
                                    require_once __DIR__ . '/../includes/functions/OCRFunction.php';
                                    $ocrResult = processInvoiceImage($localFilePath);
                                    $_SESSION['imageType'][$chatId] = 'invoice';

                                    // Check if VAT-TIN was extracted
                                    if (isset($ocrResult['vatTin']) && $ocrResult['vatTin'] !== 'VAT-TIN not found.') {
                                        // Save the extracted VAT-TIN to session
                                        $_SESSION['extractedVatTin'][$chatId] = $ocrResult['vatTin'];

                                        $ocrData = [
                                            'user_id' => $userId,
                                            'vat_tin' => $ocrResult['vatTin'],
                                            'msg_id' => $messageId,
                                            'raw_data' => isset($ocrResult['rawData']) ? $ocrResult['rawData'] : null,
                                            'file_id' => $fileId,
                                            'status' => 1, // Set initial status to 0 (e.g., pending location sharing)
                                            'date' => date('Y-m-d H:i:s')
                                        ];

                                        // Save OCR data to database
                                        $ezzeModel->addOcrData($ocrData);

                                        // Ask for location sharing after extracting VAT-TIN
                                        sendMessage($chatId, $messages[$language]['location_request'], $token, json_encode(['remove_keyboard' => true]));
                                    } else {
                                        // Handle the case where VAT-TIN could not be extracted
                                        sendMessage($chatId, "Couldn't resolve this image, please try again.", $token);
                                        unset($_SESSION['extractedVatTin'][$chatId]); // Clear the VAT-TIN from session
                                    }
                                } else {
                                    // Handle unsupported image type for OCR
                                    sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                                }

                            } else {
                                // Handle unsupported image type for decoding
                                sendMessage($chatId, $messages[$language]['unsupported_image_type'], $token);
                            }

                        }