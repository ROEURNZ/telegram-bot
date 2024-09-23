<?php

namespace App\Commands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use App\Controllers\BarcodeController;

class DecodeBarcodeCommand extends UserCommand
{
    protected $name = 'decodebarcode';
    protected $description = 'Decode a barcode from an uploaded image.';
    protected $usage = '/decodebarcode';
    protected $version = '1.0.0';

    private $barcodeController;

    public function __construct($telegram, BarcodeController $barcodeController)
    {
        parent::__construct($telegram);
        $this->barcodeController = $barcodeController;
    }

    public function execute()
    {
        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $images = $this->getUploadedImages();

        if (empty($images)) {
            return Request::sendMessage([
                'chat_id' => $chatId,
                'text'    => 'Please upload an image to decode the barcode.'
            ]);
        }

        $decodedResults = [];

        foreach ($images as $image) {
            $result = $this->barcodeController->processUploadedImage($image);
            if (isset($result['error'])) {
                Request::sendMessage([
                    'chat_id' => $chatId,
                    'text'    => $result['error'],
                ]);
                continue;
            }

            $decodedCode = $this->barcodeController->decodeBarcode($result['path']);
            if (empty(trim($decodedCode))) {
                Request::sendMessage([
                    'chat_id' => $chatId,
                    'text'    => "Decoding failed for image: " . htmlspecialchars($result['name']),
                ]);
                continue;
            }

            $barcodeType = $this->barcodeController->identifyBarcodeType(trim($decodedCode));

            $this->barcodeController->sendDecodedImageToTelegram($result, trim($decodedCode), $barcodeType);

            $decodedResults[] = [
                'file' => $result['path'],
                'code' => trim($decodedCode),
                'name' => $result['name'],
                'size' => $result['size'],
                'type' => $result['type'],
                'barcode_type' => $barcodeType,
            ];
        }

        $_SESSION['decodedResults'] = $decodedResults;

        header('Location: ../../public/views/decode_view.php');
        exit;
    }

    private function getUploadedImages()
    {
        if ($_FILES['images']['error'][0] !== UPLOAD_ERR_OK) {
            return [];
        }

        $uploadedImages = $_FILES['images'];
        $images = [];

        foreach ($uploadedImages['tmp_name'] as $key => $tmpName) {
            $images[] = [
                'name' => $uploadedImages['name'][$key],
                'tmp_name' => $tmpName,
                'error' => $uploadedImages['error'][$key],
                'size' => $uploadedImages['size'][$key]
            ];
        }

        return $images;
    }
}
