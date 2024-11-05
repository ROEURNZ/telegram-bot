<?php

function processInvoiceImage($filePath)
{

    $ocrCmd = "tesseract " . escapeshellarg($filePath) . " stdout";
    $ocrOutput = shell_exec($ocrCmd);

    if ($ocrOutput === null) {
        return ['error' => 'OCR execution failed'];
    }

    $text = trim($ocrOutput);
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    $vatTin = extractVatTin($text);

    return [
        'text' => $text,
        'vatTin' => $vatTin,
    ];
}

function extractVatTin($text)
{

    preg_match('/VAT[-\s]?TIN\s*:\s*([A-Z0-9\-]+)/i', $text, $matches);

    return isset($matches[1]) ? $matches[1] : 'VAT-TIN not found.';
}