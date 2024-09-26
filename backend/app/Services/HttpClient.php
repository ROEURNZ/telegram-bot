<?php
/* @ROEURNZ=> File name & directory
 * backend/app/Services/HttpClient.php
 * 
 */

namespace App\Services;

class HttpClient
{
    private string $botToken; 
    private string $chatId;   

    public function __construct(string $botToken, string $chatId)
    {
        $this->botToken = $botToken; 
        $this->chatId = $chatId;     
    }

    public function sendImage(string $filePath, string $message): bool
    {
        $sendPhotoUrl = "https://api.telegram.org/bot{$this->botToken}/sendPhoto"; 
        $postData = [
            'chat_id' => $this->chatId, 
            'caption' => $message,       
            'photo' => new \CURLFile(realpath($filePath)), 
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl); 
        curl_setopt($ch, CURLOPT_POST, true);          
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Enable SSL verification

        $response = curl_exec($ch);
        
        if ($response === false) {
            error_log('CURL Error: ' . curl_error($ch)); 
            curl_close($ch); 
            return false; 
        }
        
        curl_close($ch); 
        return true; 
    }

    public function sendMessage(string $message): bool
    {
        $apiUrl = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
        
        $payload = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'Markdown' // Use Markdown for links
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($payload)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($apiUrl, false, $context);

        if ($result === false) {
            error_log("Failed to send message to Telegram: " . print_r($http_response_header, true));
            return false; 
        }

        return true; 
    }
}

// Function to echo results and send to Telegram
function echoResults($fileName, $fullPath, $result, $timestamp, $httpClient) {
    // Get file size and type
    $fileSize = filesize($fullPath); 
    $fileType = mime_content_type($fullPath); 

    // Prepare the message to send to Telegram
    $message = "File Name: " . htmlspecialchars($fileName) . "\n" .
               "File Size: " . $fileSize . " bytes\n" .
               "File Type: " . htmlspecialchars($fileType) . "\n" .
               "Decoded Info: " . htmlspecialchars($result['code']) . "\n" .
               "Decode Image: [View Image](https://9bc6-175-100-6-46.ngrok-free.app/" . basename($fullPath) . ")\n" . // Replace with your public URL
               "Timestamp: " . htmlspecialchars($timestamp);

    // Send the message to Telegram
    $httpClient->sendMessage($message);
    
    // Optionally, echo the message for local visibility
    echo "<div class='result'>";
    echo "<h2>Decoded Result:</h2>";
    echo "<p>" . nl2br(htmlspecialchars($message)) . "</p>"; 
    echo "</div>";
}
