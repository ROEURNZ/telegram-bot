<?php

function processMrzImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }
    $mrzCmd = "tesseract " . escapeshellarg($filePath) . " stdout -l khm+eng";
    $mrzExtractedData = @shell_exec($mrzCmd);

    if ($mrzExtractedData === null) {
        return ['error' => 'mrz execution failed'];
    }

    $text = trim($mrzExtractedData);
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    $mrzData = extractMrzData($text);

    return [
        'text' => $text,
        'mrzData' => $mrzData,
    ];
}

function extractMrzData($text)
{
    $patterns = [
        // Passport MRZ (TD3) - 2 lines of 44 characters each
        '/([A-Z0-9<]{44})\n([A-Z0-9<]{44})/',

        // ID card MRZ (TD1) - 3 lines of 30 characters each
        '/([A-Z0-9<]{30})\n([A-Z0-9<]{30})\n([A-Z0-9<]{30})/',

        // Visa MRZ (TD2) - 2 lines of 36 characters each
        '/([A-Z0-9<]{36})\n([A-Z0-9<]{36})/',

        // Generic - Matches a single line of 44, 36, or 30 characters
        '/([A-Z0-9<]{44})/',
        '/([A-Z0-9<]{36})/',
        '/([A-Z0-9<]{30})/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $text, $matches)) {
            $mrzLines = array_map('trim', $matches[0]);
            return $mrzLines;
        }
    }

    return 'MRZ data not found.';
}
