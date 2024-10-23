<?php

// function sendMessage($params, $method = null, $log = true)
// {
//     global $api_key, $ezzeTeamsModel, $adminDetils, $logID;

//     $method = isset($method) ? $method : 'sendMessage';
//     $url = 'https://api.telegram.org/bot' . $api_key . '/' . $method;

//     $handle = curl_init($url);
//     curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
//     curl_setopt($handle, CURLOPT_TIMEOUT, 60);

//     if ($method == 'sendDocument') {
//         $finfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $params['document']);
//         $cFile = new CURLFile(realpath($params['document']), $finfo);

//         // Add CURLFile to CURL request
//         $params['document'] = $cFile;
//         curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
//     } else {
//         curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params));
//     }

//     $response = curl_exec($handle);

//     if ($response === false) {
//         curl_close($handle);
//         return false;
//     }

//     // Decode the response into an associative array
//     $response = json_decode($response, true);

//     if (isset($response['result']['text'])) {
//         $messageText = $response['result']['text'];
//         $messageId = $response['result']['message_id'];
//         $chatId = $response['result']['chat']['id'];

//         if ($messageText == getReceivedNewApplicationNotificationTxt()) {
//             $ezzeTeamsModel->updateUserNotificationNewUserMSGID($messageId, $chatId);
//         } else if (strpos($messageText, 'List of Employee') !== false) {
//             $ezzeTeamsModel->updateUserListEmpMSGID($messageId, $chatId);
//         } else if (strpos($messageText, 'List of Active Employee') !== false) {
//             $ezzeTeamsModel->updateUserListEmpMSGID($messageId, $chatId);
//         } else if (strpos($messageText, 'Day selected') !== false) {
//             $ezzeTeamsModel->updateUserDaySelectedMSGID($messageId, $chatId);
//         } else if (strpos($messageText, 'Working Time For') !== false) {
//             $ezzeTeamsModel->updateUserStartTimeMSGID($messageId, $chatId);
//         } else if (strpos($messageText, 'Please set end time') !== false) {
//             $ezzeTeamsModel->updateUserEndTimeMSGID($messageId, $chatId);
//         } else if (strpos($messageText, 'Scheduled Message Repeat Configurations') !== false) {
//             $adminUser = $ezzeTeamsModel->getAdminStep($chatId);
//             $tempData = json_decode($adminUser['temp'], true);
//             $tempData['msgEditId'] = $messageId;
//             $ezzeTeamsModel->setAdminStep($chatId, $adminUser['step'], json_encode($tempData));
//         } else if (strpos($messageText, 'List Scheduled Messages') !== false) {
//             $ezzeTeamsModel->updateUserListEmpMSGID($messageId, $chatId);
//         }
//     }

//     curl_close($handle);
//     return $response;
// }



$config = include __DIR__ .  '/../Config/api_key.php';
$token = $api_key;
include __DIR__ . '/../Config/dynDirs.php';

set_time_limit(0);
session_start();

date_default_timezone_set("Asia/Phnom_Penh");
$offset = 0;

$botApiUr = "https://api.telegram.org/bot{$token}/";

// Load localization files
$messages = [
    'en' => include __DIR__ . $n1 . '/Localization/languages/en/english.php',
    'kh' => include __DIR__ . $n1 . '/Localization/languages/kh/khmer.php',
];

include __DIR__ . $n1 . '/Localization/dateformat/dateFormat.php';

include __DIR__ . '/../Models/EzzeModel.php';



// Function to send messages

// function sendMessage($chatId, $message, $token, $replyMarkup = null)
// {
//     $url = "https://api.telegram.org/bot{$token}/sendMessage";
//     $data = [
//         'chat_id' => $chatId,
//         'text' => $message,
//         'parse_mode' => 'HTML',
//         'disable_web_page_preview' => false,
//     ];

//     if ($replyMarkup) {
//         $data['reply_markup'] = $replyMarkup;
//     }

//     $handle = curl_init($url);
//     curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
//     curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($handle, CURLOPT_POST, true);
//     curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
//     curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
//     curl_setopt($handle, CURLOPT_TIMEOUT, 60);

//     $response = curl_exec($handle);

//     if ($response === false) {
//         echo "Error sending message: " . curl_error($handle);
//     } else {
        // Optional: Debug response for logging or further error handling
//         $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
//         if ($http_code !== 200) {
//             echo "Error: Telegram API returned HTTP code $http_code";
//             echo " Response: " . $response;
//         }
//     }

//     curl_close($handle);
// }





function prepareMessage(
    $keyboard = null,
    $msg = null,
    $photo_url = null,
    $method = null,
    $chat_id = null,
    $keyboard_config = array('resize' => true, 'one_time' => false, 'force_reply' => true),
    $inline_keyboard = false,
    $inline_keyboard_config = null,
    $edit_msg_id = null,
    $multiple_inline = false,

) {
    global $userId, $ezzeTeamsModel;

    $keyboard_settings = [
        'keyboard' => $keyboard,
        'resize_keyboard' => (isset($keyboard_config['resize'])) ? $keyboard_config['resize'] : true,
        'one_time_keyboard' => (isset($keyboard_config['one_time'])) ? $keyboard_config['one_time'] : false,
        'force_reply_keyboard' => (isset($keyboard_config['force_reply'])) ? $keyboard_config['force_reply'] : true,
    ];

    if ($inline_keyboard) {
        $keyboard_settings = ["inline_keyboard" => [$inline_keyboard_config]];
        if ($multiple_inline) {
            $keyboard_settings = ["inline_keyboard" => $inline_keyboard_config];
        }
    }

    $text = str_replace(array('_'), chr(10), $msg);
    $text = str_replace(array('###'), array('_'), $text);

    $params = [
        'chat_id' => $userId,
        'parse_mode' => 'HTML',
    ];

    if ($method == 'editMessageText') {
        $params['message_id'] = $edit_msg_id;
    }

    if (isset($msg) && !isset($photo_url)) {
        $params['text'] = $text;
    }

    if (isset($photo_url)) {
        $params['caption'] = $text;
        $params['photo'] = $photo_url;
    }

    if (isset($keyboard) || $inline_keyboard) {
        $params['reply_markup'] = json_encode($keyboard_settings);
    }

    if (isset($document)) {
        $params['caption'] = $text;
        $params['document'] = $document;
    }

    if (isset($video)) {
        $params['caption'] = $text;
        $params['video'] = $video;
    }
    $params['disable_message_delete'] = true;

    if (isset($chat_id)) {
        foreach ($chat_id as $id) {
            $params['chat_id'] = $id;
            sendMessage($params, $method);
        }
    } else {
        sendMessage($params, $method);
    }
}

function sendMessage($params, $method = null, $log = true)
{
    global $api_key, $ezzeTeamsModel, $adminDetils, $logID;

    $method = isset($method) ? $method : 'sendMessage';
    $url = 'https://api.telegram.org/bot' . $api_key . '/' . $method;

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    //curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params));
    if ($method == 'sendDocument') {
        $finfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $params['document']);
        $cFile = new CURLFile(realpath($params['document']), $finfo);

        // Add CURLFile to CURL request
        $params['document'] = $cFile;
        curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
    } else {
        curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    $response = curl_exec($handle);

    if ($response === false) {
        curl_close($handle);
        return false;
    }

    $response = json_decode($response);

    return $response;
}