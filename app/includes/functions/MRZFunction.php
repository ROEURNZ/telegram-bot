<?php

function processMrzImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }
    $mrzCmd = "tesseract " . escapeshellarg($filePath) . " stdout";
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
    preg_match_all('/([A-Z0-9<]{44})|([A-Z0-9<]{30})/', $text, $matches);
    if (empty($matches[0])) {
        return 'MRZ data not found.';
    }
    $mrzLines = array_map('trim', $matches[0]);
    return $mrzLines;
}
