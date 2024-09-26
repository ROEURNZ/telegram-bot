<?php

namespace App\Handlers;

use App\Services\HttpClient;
use Session;

class BarcodeHandler {
    private $imagesPath;
    private $validationMessages;
    private $httpClient;

    public function __construct($botToken, $chatId) {
        $this->imagesPath = __DIR__ . "/../../../storage/app/public/images/decoded/";
        $this->initializeImageDirectory();
        
        $userLanguage = $_SESSION['language'] ?? 'en';
        $localizationPath = __DIR__ . "/../../Localization/{$userLanguage}/validation.php";
        
        if (!file_exists($localizationPath)) {
            die('Error: Localization file not found.');
        }

        $this->validationMessages = include($localizationPath);
        $this->httpClient = new HttpClient($botToken, $chatId);
    }

    private function initializeImageDirectory() {
        if (!is_dir($this->imagesPath)) {
            mkdir($this->imagesPath, 0777, true);
        }
    }

    public function processUploadedImage($image) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        $fileExtension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        $mimeType = mime_content_type($image['tmp_name']);

        if (!in_array($fileExtension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
            return ["error" => $this->validationMessages['invalid_file_type'] . ": " . htmlspecialchars($image['name'])];
        }

        if ($image['size'] > 5 * 1024 * 1024) { // 5MB limit
            return ["error" => $this->validationMessages['file_too_large'] . ": " . htmlspecialchars($image['name'])];
        }

        $fileName = pathinfo($image['name'], PATHINFO_FILENAME);
        $filePath = $this->imagesPath . $fileName . '.' . $fileExtension;

        $fileCounter = 1;
        while (file_exists($filePath)) {
            $filePath = $this->imagesPath . $fileName . '-' . $fileCounter++ . '.' . $fileExtension;
        }

        if (!move_uploaded_file($image['tmp_name'], $filePath)) {
            return ["error" => $this->validationMessages['failed_to_save_image'] . ": " . htmlspecialchars($fileName)];
        }

        return ["path" => $filePath, "name" => basename($filePath), "size" => $image['size'], "type" => $mimeType];
    }

    public function identifyBarcodeType($decodedCode) {
        if (strpos($decodedCode, 'BEGIN:VCARD') !== false) {
            return 'QR Code';
        } elseif (filter_var($decodedCode, FILTER_VALIDATE_URL)) {
            return 'QR Code (URL)';
        } elseif (preg_match('/^[0-9]{12,13}$/', $decodedCode)) {
            return 'EAN/UPC Barcode';
        } elseif (preg_match('/^[0-9]{14}$/', $decodedCode)) {
            return 'ITF-14 Barcode';
        } else {
            return 'Unknown Barcode Type';
        }
    }

    public function handleImageUpload($uploadedImages) {
        if ($uploadedImages['error'][0] !== UPLOAD_ERR_OK) {
            die($this->validationMessages['image_upload_failed'] ?: 'Error: Image upload failed.');
        }

        $decodedResults = [];

        foreach ($uploadedImages['tmp_name'] as $key => $tmpName) {
            $image = [
                'name' => $uploadedImages['name'][$key],
                'tmp_name' => $tmpName,
                'error' => $uploadedImages['error'][$key],
                'size' => $uploadedImages['size'][$key],
            ];

            $result = $this->processUploadedImage($image);
            if (isset($result['error'])) {
                echo "<p class='text-red-600'>" . htmlspecialchars($result['error']) . "</p>";
                continue;
            }

            // Decode the barcode from the image
            $decodedCode = @shell_exec("zbarimg --raw " . escapeshellarg($result['path']));
            if ($decodedCode === null || trim($decodedCode) === '') {
                echo "<p class='text-red-600'>" . $this->validationMessages['decoding_failed'] . " " . htmlspecialchars($result['name']) . "</p>";
                continue;
            }

            $barcodeType = $this->identifyBarcodeType(trim($decodedCode));
            $decodedResults[] = [
                'file' => $result['path'],
                'code' => trim($decodedCode),
                'name' => $result['name'],
                'size' => $result['size'],
                'type' => $result['type'],
                'barcode_type' => $barcodeType,
            ];

            $fileSizeInKb = round($result['size'] / 1024, 2);
            $message = "Uploaded Image\n" .
                "File Name: " . $result['name'] . "\n" .
                "File Size: " . $fileSizeInKb . " KB\n" .
                "File Type: " . $result['type'] . "\n" .
                "Decoded Info: " . trim($decodedCode) . "\n" .
                "Decode Image Type: " . $barcodeType;

            if (!$this->httpClient->sendImage($result['path'], $message)) {
                echo "<p class='text-red-600'>" . $this->validationMessages['failed_to_send_image'] . "</p>";
            } else {
                echo "<p class='text-green-600'>Image sent successfully!</p>";
            }
        }

        // Store decoded results in session
        $_SESSION['decodedResults'] = $decodedResults;
        header('Location: ../../../public/views/decode_view.php');
        exit;
    }
}
