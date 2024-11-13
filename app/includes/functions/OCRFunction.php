<?php
include __DIR__ . '/../../Utils/OCRIdentifier.php';
function processInvoiceImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }

    $ocrCmd = "tesseract " . escapeshellarg($filePath) . " stdout -l khm+eng";
    $ocrOutput = shell_exec($ocrCmd);

    if ($ocrOutput === null) {
        return ['error' => 'OCR execution failed'];
    }

    $rawData = trim($ocrOutput);
    if (empty($rawData)) {
        return ['error' => 'No text found in image'];
    } 
    $taxData = extractTaxIdentifiers($rawData);

    $ocrtext = !empty($rawData) ? 1 : 0;
    return [
        'rawData' => $rawData,
        'taxIdentifiers' => $taxData['taxIdentifiers'],
        'ocrhasvat' => $taxData['ocrhasvat'], 
        'tin' => $taxData['tin'],  
    ];
}



