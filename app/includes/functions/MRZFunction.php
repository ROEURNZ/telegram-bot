<?php
include __DIR__ . '/../../Utils/MRZIdentifier.php';
function processMrzImage($filePath)
{
    // Check if the file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }

    // Command to extract MRZ data using Tesseract OCR
    $mrzCmd = "tesseract " . escapeshellarg($filePath) . ' stdout --psm 6 -c "tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<" -l khm+eng';
    $mrzOutput = @shell_exec($mrzCmd);

    // Check if Tesseract execution failed
    if ($mrzOutput === null) {
        return ['error' => 'MRZ execution failed', 'command' => $mrzCmd, 'output' => $mrzOutput];
    }

    // Trim the output to remove any extra whitespace
    $text = trim($mrzOutput);

    // If no text is found in the image, return an error
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    // Extract MRZ data from the text
    $mrzData = extractMrzData($text);

    // If MRZ data is valid, format it correctly
    if (is_array($mrzData)) {
        // Combine the MRZ data lines into a single string, separating by '<'
        $mrzCode = implode("\n", array_map(fn($line) => implode('<', $line), $mrzData));
    } else {
        // If MRZ data is not found, return the error message from extractMrzData
        return ['error' => $mrzData];
    }

    // Return the processed text and MRZ data
    return [
        'text' => $text,
        'mrzData' => $mrzCode,
    ];
}



