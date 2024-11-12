<?php

include __DIR__ . '/loadLanguage.php';
$baseLanguage = loadLanguages();



// Retrieve user language settings or default to English
$chatId = isset($userState['currentChatId']) ? $userState['currentChatId'] : null;
$language = isset($userState['userLanguages'][$chatId]) ? $userState['userLanguages'][$chatId] : 'en';
$activeLanguage = $baseLanguage[$language];

// $currentMessages = $baseLanguage[$language];

function setCommands($token, $baseLanguage)
{

    $userCommands = json_encode(
        array(
            array(
                'command' => 'start',
                'description' => $baseLanguage['start_desc'] 
            ),
    
            array(
                'command' => 'decode',
                'description' => $baseLanguage['decode_desc']
            ),
            array(
                'command' => 'ocr',
                'description' => $baseLanguage['ocr_desc']
            ),
            array(
                'command' => 'mrz',
                'description' => $baseLanguage['mrz_desc']
            ),
            array(
                'command' => 'change_language',
                'description' => $baseLanguage['change_language_desc']
            ),
            array(
                'command' => 'share_contact',
                'description' => $baseLanguage['share_contact_desc']
            ),
            array(
                'command' => 'help',
                'description' => $baseLanguage['help_desc']
            ),
  
        )
    );
    

    $data = array('commands' => $userCommands);

    $url = "https://api.telegram.org/bot{$token}/setMyCommands";

    // Initialize cURL for sending the request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 

    curl_close($ch);
}

     


