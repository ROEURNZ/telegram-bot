<?php

// backend/app/include/function/OCRFunction.php

/**
 * Processes an invoice image using OCR to extract text and identify VAT-TIN.
 *
 * @param string $filePath The file path to the image.
 * @return array Contains extracted text and VAT-TIN, or error message if OCR fails.
 */
function processInvoiceImage($filePath)
{
    // Use Tesseract OCR to process the image
    $ocrCmd = escapeshellcmd("tesseract " . escapeshellarg($filePath) . " stdout");
    $ocrOutput = shell_exec($ocrCmd);
    
    // Check for command execution errors
    if ($ocrOutput === null) {
        error_log("Tesseract OCR command failed for file: " . $filePath);
        return ["error" => "OCR failed: Could not execute Tesseract."];
    }

    // Trim and check if OCR succeeded
    $text = trim($ocrOutput);
    if ($text === '') {
        return ["error" => "OCR failed: Could not extract text from image."];
    }

    // Extract VAT-TIN from the OCR text output
    $vatTin = extractVatTin($text);

    // Return OCR results
    return [
        'text' => $text,
        'vatTin' => $vatTin,
    ];
}

/**
 * Extracts VAT-TIN information from text using regex.
 *
 * @param string $text The text obtained from OCR processing.
 * @return string Extracted VAT-TIN or a message if VAT-TIN not found.
 */
function extractVatTin($text)
{
    // Regex to capture VAT-TIN pattern with letters, numbers, and optional dashes
    preg_match('/VAT[-\s]?TIN\s*:\s*([A-Z0-9\-]+)/i', $text, $matches);
    
    // Return VAT-TIN if found; otherwise, return a 'not found' message
    return isset($matches[1]) ? $matches[1] : 'VAT-TIN not found.';
}
