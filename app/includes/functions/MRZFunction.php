<?php
include __DIR__ . "/../../Utils/MRZIdentifier.php";

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

    // Format mrzData directly
    $mrzCode = is_array($mrzData) 
        ? implode("\n", array_map(fn($line) => implode('<', $line), $mrzData))
        : $mrzData;

    return [
        'text' => $text,
        'mrzData' =>   $mrzCode,
    ];
}


