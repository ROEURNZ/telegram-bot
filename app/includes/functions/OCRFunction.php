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
