<?php

namespace App\Controllers;

class BarcodeController
{
    private $imagesPath;
    private $botToken;
    private $chatId;

    public function __construct($imagesPath, $botToken, $chatId)
    {
        $this->imagesPath = $imagesPath;
        $this->botToken = $botToken;
        $this->chatId = $chatId;
    }

    public function processUploadedImage($image)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($image['tmp_name']);

        if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
            return ["error" => "Invalid file type: " . htmlspecialchars($image['name'])];
        }

        if ($image['size'] > 5 * 1024 * 1024) {
            return ["error" => "File too large: " . htmlspecialchars($image['name'])];
        }

        $fileName = basename($image['name']);
        $filePath = $this->imagesPath . $fileName;

        $fileCounter = 1;
        while (file_exists($filePath)) {
            $fileName = pathinfo($image['name'], PATHINFO_FILENAME) . '-' . $fileCounter . '.' . $fileExtension;
            $filePath = $this->imagesPath . $fileName;
            $fileCounter++;
        }

        if (!move_uploaded_file($image['tmp_name'], $filePath)) {
            return ["error" => "Failed to save the uploaded image: " . htmlspecialchars($fileName)];
        }

        return ["path" => $filePath, "name" => $fileName, "size" => $image['size'], "type" => $mimeType];
    }

    public function decodeBarcode($filePath)
    {
        $escapedFilePath = escapeshellarg($filePath);
        $decodedCode = @shell_exec("zbarimg --raw " . $escapedFilePath);

        return $decodedCode;
    }

    public function identifyBarcodeType($decodedCode)
    {
        if (strpos($decodedCode, 'BEGIN:VCARD') !== false) {
            return 'QR Code';
        } elseif (filter_var($decodedCode, FILTER_VALIDATE_URL)) {
            return 'QR Code (URL)';
        } elseif (preg_match('/^[0-9]{12,13}$/', $decodedCode)) {
            return 'EAN/UPC Barcode';
        } elseif (preg_match('/^[0-9]{14}$/', $decodedCode)) {
            return 'ITF-14 Barcode';
        } else {
            if (preg_match('/^[0-9A-Za-z]+$/', $decodedCode)) {
                return 'Unknown QR Code Type';
            } elseif (preg_match('/^.+$/', $decodedCode)) {
                return 'Unknown Barcode Type';
            } else {
                return 'Invalid Code Format';
            }
        }
    }

    public function sendDecodedImageToTelegram($result, $decodedCode, $barcodeType)
    {
        $filePath = $result['path'];
        $fileSizeInKb = round($result['size'] / 1024, 2); // Convert size to KB

        $message = "Uploaded Image\n" .
            "File Name: " . $result['name'] . "\n" .
            "File Size: " . $fileSizeInKb . " KB\n" .
            "File Type: " . $result['type'] . "\n" .
            "Decoded Info: " . trim($decodedCode) . "\n" .
            "Decode Image Type: " . $barcodeType;

        $sendPhotoUrl = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";
        $postData = [
            'chat_id' => $this->chatId,
            'caption' => $message,
            'photo' => new \CURLFile(realpath($filePath))
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendPhotoUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response !== false;
    }
}
