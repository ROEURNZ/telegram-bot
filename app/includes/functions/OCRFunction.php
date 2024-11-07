<?php

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

    $patterns = [
        '/\bVAT[-\s]?TIN\s*:\s*([A-Z0-9\-]+)/i',
        '/\bVAT[-\s]?ID\s*:\s*([A-Z0-9\-]+)/i',
        '/\bTIN[-\s]?NUMBER\s*:\s*([A-Z0-9\-]+)/i',
        '/\bTAX[-\s]?ID\s*:\s*([A-Z0-9\-]+)/i',
        '/\bGST[-\s]?IN\s*:\s*([A-Z0-9\-]+)/i',
        '/\bVAT[-\s]?NO\s*:\s*([A-Z0-9\-]+)/i',
        '/\bTIN\s*ID\s*:\s*([A-Z0-9\-]+)/i',
        '/\bBUSINESS[-\s]?ID\s*:\s*([A-Z0-9\-]+)/i',
        '/\bCOMPANY[-\s]?ID\s*:\s*([A-Z0-9\-]+)/i',
        '/\bTAX[-\s]?NUMBER\s*:\s*([A-Z0-9\-]+)/i',
        '/\bGST[-\s]?NUMBER\s*:\s*([A-Z0-9\-]+)/i'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return $matches[1];
        }
    }

    return 'VAT-TIN not found.';
}
