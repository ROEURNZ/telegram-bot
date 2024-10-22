<?php
// backend/app/Handlers/handle_commands.php
// Load configuration
$config = require '../config/bot_config.php';
require_once '../Config/database.php';
require_once '../Models/EzzeModel.php';


// $profileModel = new Profile($pdo);
// $decodedModel = new Decoded($pdo);

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
    $data = ['chat_id' => $chatId, 'text' => htmlspecialchars($message), 'parse_mode' => 'HTML'];

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
    global $messages, $profileModel, $decodedModel; // Access the global messages array
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
                    

                    case '/change_language':
                        // Set a session flag to track the language change process
                        $_SESSION['is_changing_language'] = true;

                        // Send the language selection prompt
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
                        $_SESSION['user_language'] = 'en';

                        // Update language setting and respond
                        if (isset($_SESSION['is_changing_language']) && $_SESSION['is_changing_language']) {
                            sendMessage($chatId, "Language set to English", $token);
                            unset($_SESSION['is_changing_language']); // Reset the flag
                        } else {
                            sendMessage($chatId, "You have selected English", $token);
                        }

                        // Set language selection flag if not set
                        if (!isset($_SESSION['language_selected'])) {
                            $_SESSION['language_selected'] = true;
                        }

                        // Continue from the last session stage, updating the language of each prompt
                        if (isset($_SESSION['contact_shared']) && $_SESSION['contact_shared'] === true) {
                            if (!isset($_SESSION['image_uploaded'])) {
                                // Prompt for barcode or QR code image upload
                                sendMessage($chatId, $messages['en']['upload_barcode'], $token);
                            } elseif (!isset($_SESSION['location_shared'])) {
                                // Prompt for location sharing
                                $locationMarkup = json_encode([
                                    'resize_keyboard' => true,
                                    'one_time_keyboard' => true,
                                ]);
                                sendMessage($chatId, $messages['en']['share_location'], $token, $locationMarkup);
                            }
                        } else {
                            // Prompt to share contact if that stage hasn’t been completed
                            $contactMarkup = json_encode([
                                'keyboard' => [[['text' => '📞 Share My Contact', 'request_contact' => true]]],
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);
                            sendMessage($chatId, $messages['en']['contact_prompt'], $token, $contactMarkup);
                        }
                        break;

                    case '🇰🇭 ភាសាខ្មែរ':
                        $_SESSION['user_language'] = 'kh';

                        // Update language setting and respond
                        if (isset($_SESSION['is_changing_language']) && $_SESSION['is_changing_language']) {
                            sendMessage($chatId, "អ្នកបានកំណត់ជាភាសាខ្មែរ", $token);
                            unset($_SESSION['is_changing_language']); // Reset the flag
                        } else {
                            sendMessage($chatId, "អ្នកបានជ្រើសរើសភាសាខ្មែរ", $token);
                        }

                        // Set language selection flag if not set
                        if (!isset($_SESSION['language_selected'])) {
                            $_SESSION['language_selected'] = true;
                        }

                        // Continue from the last session stage, updating the language of each prompt
                        if (isset($_SESSION['contact_shared']) && $_SESSION['contact_shared'] === true) {
                            if (!isset($_SESSION['image_uploaded'])) {
                                // Prompt for barcode or QR code image upload
                                sendMessage($chatId, $messages['kh']['upload_barcode'], $token);
                            } elseif (!isset($_SESSION['location_shared'])) {
                                // Prompt for location sharing
                                $locationMarkup = json_encode([
                                    // 'keyboard' => [[['text' => 'ចែករំលែកទីតាំងរបស់ខ្ញុំ', 'request_location' => true]]],
                                    'resize_keyboard' => true,
                                    'one_time_keyboard' => true,
                                ]);
                                sendMessage($chatId, $messages['kh']['share_location'], $token, $locationMarkup);
                            }
                        } else {
                            // Prompt to share contact if that stage hasn’t been completed
                            $contactMarkup = json_encode([
                                'keyboard' => [[['text' => '📞 ចែករំលែកទំនាក់ទំនងរបស់ខ្ញុំ', 'request_contact' => true]]],
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);
                            sendMessage($chatId, $messages['kh']['contact_prompt'], $token, $contactMarkup);
                        }
                        break;

                    case '/help':
                        sendMessage($chatId, $messages[$userLanguage]['help'], $token);
                        break;

                    case '/menu':
                        sendMessage($chatId, $messages[$userLanguage]['menu'], $token);
                        break;

                    case '/decode':
                        // Check if the user has previously completed the session requirements
                        if (isset($_SESSION['session_completed']) && $_SESSION['session_completed'] === true) {
                            // Allow the user to continue decoding
                            sendMessage($chatId, $messages[$userLanguage]['upload_barcode'], $token);
                        } else {
                            // Check if the user has completed the language selection and contact sharing steps
                            if (isset($_SESSION['language_selected']) && $_SESSION['language_selected'] === true &&
                                isset($_SESSION['contact_shared']) && $_SESSION['contact_shared'] === true) {
                                
                                // Set session_completed to true since requirements are now met
                                $_SESSION['session_completed'] = true;

                                // Prompt the user to upload a barcode or QR code
                                sendMessage($chatId, $messages[$userLanguage]['upload_barcode'], $token);
                            } else {
                                // Inform the user of the session requirements
                                sendMessage($chatId, $messages[$userLanguage]['decode_requirements'], $token);
                            }
                        }
                        break;

                }
            }

            // Handle contact sharing
            if (isset($update['message']['contact'])) {
                $_SESSION['contact_shared'] = true;
                $contact = $update['message']['contact'];
                $phoneNumber = $contact['phone_number'];
                $firstName = $contact['first_name'];
                $lastName = $contact['last_name'] ?? '';
                $username = $update['message']['from']['username'] ? "https://t.me/{$update['message']['from']['username']}" : "No username available";


                // Get the user's selected language
                $language = $_SESSION['user_language'] ?? 'en'; // Default to English if not set
                $profileModel->upsertUserProfile($update['message']['from']['id'], $lastName, $firstName, $update['message']['from']['username'], $phoneNumber, $_SESSION['user_language'], $update['message']['message_id']);
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

                // Hide the "Share Contact" button by sending an empty keyboard
                $removeKeyboard = json_encode(['remove_keyboard' => true]);
                sendMessage($chatId, $messages[$userLanguage]['upload_barcode'], $token, $removeKeyboard);
            }


            // Handle image upload (barcode/QR code)
            if (isset($update['message']['photo'])) {
                $_SESSION['image_uploaded'] = true;
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
                    $decodedData = processBarcodeImage($localFilePath);
            
                    if ($decodedData) {
                        $type = $decodedData['type'];
                        $content = $decodedData['content'];
            
                        // Store the decoded data in the session as an array
                        if (!isset($_SESSION['decodedBarcodes'])) {
                            $_SESSION['decodedBarcodes'] = [];
                        }
                        $_SESSION['decodedBarcodes'][$chatId][] = [
                            'type' => $type,
                            'content' => $content
                        ];
            
                        // Ask for location sharing if this is the first barcode/QR code
                        if (count($_SESSION['decodedBarcodes'][$chatId]) == 1) {
                            $locationMarkup = json_encode([
                                'resize_keyboard' => true,
                                'one_time_keyboard' => true,
                            ]);
                            sendMessage($chatId, $messages[$userLanguage]['share_location'], $token, $locationMarkup);
                        }
                    } else {
                        sendMessage($chatId, $messages[$userLanguage]['barcode_error'], $token);
                    }
                }                    
            }
            

            // Handle location sharing
            if (isset($update['message']['location'])) {
                $_SESSION['location_shared'] = true;
                $location = $update['message']['location'];
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];
                $msgId = $update['message']['message_id'];
            
                // Get the user's selected language
                $language = $_SESSION['user_language'] ?? 'en'; // Default to English if not set
            
                // Retrieve the decoded barcodes from the session (as an array)
                $decodedBarcodes = $_SESSION['decodedBarcodes'][$chatId] ?? [['type' => 'N/A', 'content' => 'No barcode decoded.']];
            
                // Get the current date and time
                $date = date('M-d-Y');
                $time = date('H:i');
            
                // Prepare the location URL
                $locationUrl = "https://www.google.com/maps/dir/{$latitude},{$longitude}";
            
                // Format the barcode list for the response
                $barcodeList = implode("\n", array_map(function($barcode, $index) {
                    return ($index + 1) . ". {$barcode['content']}";
                }, $decodedBarcodes, array_keys($decodedBarcodes)));
            
                // Insert each decoded barcode into the database
                foreach ($decodedBarcodes as $barcode) {
                    $decodedModel->insertDecodedData(
                        $chatId,
                        $barcode['type'],
                        $barcode['content'],
                        $latitude,
                        $longitude,
                        $locationUrl,
                        $msgId
                    );
                }
            
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
function processBarcodeImage($imagePath) {
    // Decode the barcode or QR code using zbarimg or any preferred library
    $result = shell_exec("zbarimg --raw --quiet $imagePath");

    if ($result) {
        $type = (strpos($result, 'QR-Code:') === 0) ? 'QR Code' : 'Barcode';
        return [
            'type' => $type,
            'content' => trim($result),
        ];
    }
    return null; // Return null if no code is detected
}


// Main loop to fetch updates
$offset = 0;
while (true) {
    $updates = file_get_contents("https://api.telegram.org/bot{$config['bot_token']}/getUpdates?offset={$offset}");
    $updates = json_decode($updates, true);

    if (isset($updates['result'])) {
        processUpdates($updates['result'], $config['bot_token']);
        // Update the offset to the next update ID
        $offset = end($updates['result'])['update_id'] + 1;
    }
    
    // Sleep to prevent hitting the rate limit
    usleep(100000); // 100 ms delay
}
