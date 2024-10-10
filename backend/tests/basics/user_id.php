<?php
// Your bot's API token
$config = require __DIR__ . "/../../app/Config/botconfig.php";
$token = $api_key;
set_time_limit(120); // Allow the script to run for an extended period

// Set initial offset to fetch updates starting from the first one
$lastUpdateId = 0;

while (true) {
    // Construct the URL with the offset to get new updates
    $url = "https://api.telegram.org/bot$token/getUpdates?offset=" . ($lastUpdateId + 1);

    // Initialize cURL to fetch updates
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a 30-second timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL verification (not recommended in production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Execute the cURL request
    $updates = curl_exec($ch);

    // Check if there was an error
    if ($updates === false) {
        echo "Failed to fetch updates. cURL error: " . curl_error($ch) . "\n";
        curl_close($ch);
        break; // Exit the loop if the request fails
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the response from JSON
    $updatesArray = json_decode($updates, true);

    // Check if there are new updates in the result
    if (isset($updatesArray['result']) && !empty($updatesArray['result'])) {
        foreach ($updatesArray['result'] as $update) {
            // Check if the update contains a message
            if (isset($update['message'])) {
                $message = $update['message'];
                $userId = $message['from']['id'];
                echo "User ID: " . $userId . "\n";

                // Update the last processed update ID
                $lastUpdateId = $update['update_id'];
            }
        }
    } else {
        // No updates received
        echo "No new updates.\n";
    }

    // Sleep for 2 seconds to avoid hitting Telegram's rate limits
    sleep(2);
}



// // Your bot's API token
// $config = require __DIR__ . "/../app/Config/botconfig.php";
// $token = $config["bot_token"];
// set_time_limit(120); // Allow the script to run indefinitely

// // Initial offset to fetch only new updates
// $lastUpdateId = 0;

// while (true) {
//     // Get updates with offset to fetch only new updates
//     $url = "https://api.telegram.org/bot$token/getUpdates?offset=" . ($lastUpdateId + 1);

    // // Use cURL to fetch updates
    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout in 30 seconds
    
    // // Bypass SSL verification (not recommended for production)
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // $updates = curl_exec($ch);

//     // Check for errors
//     if (curl_errno($ch)) {
//         echo 'Curl error: ' . curl_error($ch) . "\n";
//         break;
//     }

//     curl_close($ch);

//     // Decode the response
//     $updatesArray = json_decode($updates, true);

//     // Check for updates
//     if (isset($updatesArray['result']) && !empty($updatesArray['result'])) {
//         foreach ($updatesArray['result'] as $update) {
//             // Extract message and user ID
//             if (isset($update['message'])) {
//                 $message = $update['message'];
//                 $userId = $message['from']['id'];
//                 echo "User ID: " . $userId . "\n";

//                 // Update the last processed update ID
//                 $lastUpdateId = $update['update_id'];
//             }
//         }
//     }

//     // Sleep to prevent hitting API limits
//     sleep(2);
// }
// $url = "https://api.telegram.org/bot$token/getUpdates?offset=0";
// $updates = file_get_contents($url);
// echo $updates; // Check what previous updates look like