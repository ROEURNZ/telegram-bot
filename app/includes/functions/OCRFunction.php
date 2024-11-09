<?php
include __DIR__ . "/../../Utils/OCRIdentifier.php";

function processInvoiceImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }

    $ocrCmd = "tesseract " . escapeshellarg($filePath) . " stdout";
    $ocrOutput = shell_exec($ocrCmd);

    if ($ocrOutput === null) {
        return ['error' => 'OCR execution failed'];
    }

    $rawData = trim($ocrOutput);
    if (empty($rawData)) {
        return ['error' => 'No text found in image'];
    }

    $taxIdentifiers = extractTaxIdentifiers($rawData);

        // Tax identifiers found, update ochrasvat to 1
        return [
            'rawData' => $rawData,
            'taxIdentifiers' => $taxIdentifiers,

        ];

}

