<?php

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

function extractTaxIdentifiers($text)
{
    // Regular expression to match tax identifiers followed by their codes
    $pattern = '/\b(VAT[-\s]?TIN|VATTIN|GSTIN|TAX[-\s]?TIN|TAXTIN|TAX[-\s]?ID|TAXID)[\s:]*([A-Z0-9\-]+)/i';
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

    $results = [];
    foreach ($matches as $match) {

        $results[] = [
            'identifier' => $match[1],
            'code' => $match[2]
        ];
    }
    return $results;
}