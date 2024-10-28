<?php

// backend/app/include/function/OCRFunction.php

/**
 * Processes an invoice image using OCR to extract text and identify VAT-TIN, supporting Khmer and other languages.
 *
 * @param string $filePath The file path to the image.
 * @return array Contains extracted text and VAT-TIN, or error message if OCR fails.
 */
function processInvoiceImage($filePath)
{
    // Specify Khmer and English languages in Tesseract
    $ocrCmd = "tesseract " . escapeshellarg($filePath) . " stdout -l khm+eng";
    $ocrOutput = shell_exec($ocrCmd);

    if ($ocrOutput === null) {
        return ['error' => 'OCR execution failed'];
    }

    // Convert output to UTF-8 if it's not already
    $text = mb_convert_encoding(trim($ocrOutput), 'UTF-8', 'auto');
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    // Extract VAT-TIN using dynamic case identification
    list($vatTin, $caseFormat) = extractVatTin($text);

    return [
        'text' => $text,
        'vatTin' => $vatTin,
        'caseFormat' => $caseFormat,
    ];
}

/**
 * Extracts VAT-TIN information from text using regex with dynamic case format detection.
 *
 * @param string $text The text obtained from OCR processing.
 * @return array Contains extracted VAT-TIN and detected case format, or a message if not found.
 */
function extractVatTin($text)
{
    // Patterns for different case formats
    $patterns = [
        'uppercase' => '/VAT[-\s]?TIN\s*:\s*([A-Z0-9\-]+)/',
        'lowercase' => '/vat[-\s]?tin\s*:\s*([a-z0-9\-]+)/',
        'capitalcase' => '/Vat[-\s]?Tin\s*:\s*([A-Z0-9\-]+)/',
        'any_case' => '/VAT[-\s]?TIN\s*:\s*([A-Z0-9\-]+)/i'
    ];

    // Check each pattern and determine case format
    foreach ($patterns as $caseFormat => $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return [$matches[1], ucfirst($caseFormat)];
        }
    }

    // Return 'not found' if no pattern matched
    return ['VAT-TIN not found.', null];
}
