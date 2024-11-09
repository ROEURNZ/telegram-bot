<?php

function processBarcodeImage($filePath)
{
    require_once __DIR__ . "/../../Utils/DecodeTypes.php";
    $decodeCmd = @shell_exec(escapeshellcmd("zbarimg --raw " . escapeshellarg($filePath)));
    
    // Ensure $decodeCmd is always a string before calling trim
    $code = is_string($decodeCmd) ? trim($decodeCmd) : '';

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
