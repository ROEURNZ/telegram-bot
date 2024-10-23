<?php


/**
 * Processes an invoice image using OCR to extract text and identify VAT-TIN.
 *
 * @param string $filePath The file path to the image.
 * @return array Contains extracted text and VAT-TIN, or error message if OCR fails.
 */
use thiagoalessio\TesseractOCR\TesseractOCR;

function processInvoiceImage($filePath)
{
    // Using Tesseract OCR through the PHP wrapper
    try {
        $ocr = (new TesseractOCR($filePath))->run();
    } catch (Exception $e) {
        return ['error' => 'OCR execution failed: ' . $e->getMessage()];
    }

    $text = trim($ocr);
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    // Example of extracting VAT-TIN using regex
    $vatTin = extractVatTin($text);

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
