<?php

function processMrzImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }
    $mrzCmd = "tesseract " . escapeshellarg($filePath) . ' stdout --psm 6 -c "tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<" -l khm+eng';
    $mrzOutput = @shell_exec($mrzCmd);

    if ($mrzOutput === null) {
        return ['error' => 'MRZ execution failed', 'command' => $mrzCmd, 'output' => $mrzOutput];
    }

    $text = trim($mrzOutput);

    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    $mrzData = extractMrzData($text);

    if (is_array($mrzData)) {
        $mrzCode = implode("\n", array_map(fn($line) => implode('<', $line), $mrzData));
    } else {
        return ['error' => $mrzData];
    }

    return [
        'text' => $text,
        'mrzData' => $mrzCode,
    ];
}

function extractMrzData($text)
{
    $pattern = '/([A-Z0-9<]{44})|([A-Z0-9<]{30})|([A-Z0-9<]{36})/';
    preg_match_all($pattern, $text, $matches);

    if (empty($matches[0])) {
        return 'MRZ data not found.';
    }

    $mrzLines = array_map('trim', $matches[0]);
    $mrzInfo = [];
    foreach ($mrzLines as $line) {
        $mrzInfo[] = explode('<', $line);  
    }

    return $mrzInfo;
}

