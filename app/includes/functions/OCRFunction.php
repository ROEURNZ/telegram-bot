<?php

function processInvoiceImage($filePath)
{
    // Run Tesseract OCR on the image
    $ocrCmd = "tesseract " . escapeshellarg($filePath) . " stdout";
    $ocrOutput = shell_exec($ocrCmd);

    // Check if OCR output is null or empty
    if ($ocrOutput === null || trim($ocrOutput) === '') {
        // End process, set ocrtext, ochrasvat, and taxincluded to 0
        return [
            'ocrtext' => 0,
            'ochrasvat' => 0,
            'taxincluded' => 0
        ];
    }

    // If OCR output is not empty, update ocrtext to 1 and store raw data
    $ocrtext = 1;
    $rawData = trim($ocrOutput);

    // Extract all tax identifiers with their codes if present
    $taxIdentifiers = extractTaxIdentifiers($rawData);

    if (!empty($taxIdentifiers)) {
        // Tax identifiers found, update ochrasvat to 1
        return [
            'ocrtext' => $ocrtext,
            'rawData' => $rawData,
            'taxIdentifiers' => $taxIdentifiers,
            'ochrasvat' => 1,
            'taxincluded' => 1 // Assume tax is included if identifiers are found
        ];
    } else {
        // No tax identifiers found, update ochrasvat to 0
        return [
            'ocrtext' => $ocrtext,
            'rawData' => $rawData,
            'ochrasvat' => 0,
            'taxincluded' => 0,
            'message' => 'COMING SOON: AI PROCESSING'
        ];
    }
}

function extractTaxIdentifiers($text)
{
    // Regular expression to match tax identifiers followed by their codes
    $pattern = '/\b(VAT[-\s]?TIN|VATTIN|GSTIN|TAX[-\s]?TIN|TAXTIN|TAX[-\s]?ID|TAXID)[\s:]*([A-Z0-9\-]+)/i';
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

    $results = [];
    foreach ($matches as $match) {
        // $match[1] contains the identifier (e.g., "VAT-TIN")
        // $match[2] contains the code (e.g., "12345")
        $results[] = [
            'identifier' => $match[1],
            'code' => $match[2]
        ];
    }

    // Return an array of tax identifiers and their codes
    return $results;
}


//usage example

$filePath = '../../images/photo_2024-11-06_12-47-58.jpg';
$result = processInvoiceImage($filePath);

if ($result['ocrtext']) {

    if (!empty($result['taxIdentifiers'])) {
        foreach ($result['taxIdentifiers'] as $identifier) {
            echo $identifier['identifier'] . ": " . $identifier['code'] . "\n";
        }
    }
} else {
    echo "OCR output not found.\n";
}