<?php

// backend/app/include/function/DecodeFunctiom.php
// Function to process code images for barcode decoding
function processBarcodeImage($filePath)
{
    require_once __DIR__ . "/../../Utils/Decode/DecodeTypes.php";
    $decodeCmd = @shell_exec(escapeshellcmd("zbarimg --raw " . escapeshellarg($filePath)));
    $code = trim($decodeCmd);
    if ($decodeCmd === null || $code === '') {
        return ["error" => "Decoding failed: " . htmlspecialchars(basename($filePath))];
    }
    $file = $filePath;
    $type = identifyBarcodeType($code);
    return [
        'file' => $file,
        'code' => $code,
        'type' => $type,
    ];
}



