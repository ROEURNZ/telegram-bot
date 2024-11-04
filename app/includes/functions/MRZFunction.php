<?php

// backend/app/include/function/MRZFunction.php

/**
 * Processes an ID/passport image to extract MRZ (Machine Readable Zone) data using mrz.
 *
 * @param string $filePath The file path to the image.
 * @return array Contains extracted MRZ data or an error message if extraction fails.
 */
function processMrzImage($filePath)
{
    // Check if the file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }

    // Use Tesseract mrz to extract text from the image
    $mrzCmd = "tesseract " . escapeshellarg($filePath) . " stdout";
    $mrzOutput = @shell_exec($mrzCmd);

    if ($mrzOutput === null) {
        return ['error' => 'mrz execution failed'];
    }

    $text = trim($mrzOutput);
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    // Extract MRZ lines using regex
    $mrzData = extractMrzData($text);

    return [
        'text' => $text,
        'mrzData' => $mrzData,
    ];
}

/**
 * Extracts MRZ (Machine Readable Zone) data from text using regex.
 *
 * @param string $text The text obtained from mrz processing.
 * @return array Extracted MRZ information or a message if MRZ not found.
 */
function extractMrzData($text)
{
    // Define a regex pattern for MRZ lines
    // The MRZ format typically has two or three lines with specific lengths and character patterns:
    // - Passport MRZ format (2 lines): Line 1 with 44 characters, Line 2 with 44 characters
    // - ID card MRZ format (3 lines): Line 1 with 30 characters, Lines 2 and 3 with 30 characters
    preg_match_all('/([A-Z0-9<]{44})|([A-Z0-9<]{30})/', $text, $matches);

    // Check if we have MRZ lines
    if (empty($matches[0])) {
        return 'MRZ data not found.';
    }

    // Combine matched lines into a single MRZ string or array for further processing
    $mrzLines = array_map('trim', $matches[0]);
    return $mrzLines;
}

// sample

$filePath = '../../images/cb819975-7cd1-4d90-8826-fc765b703cbf.png';
$result = processMrzImage($filePath);

if (array_key_exists('error', $result)) {
    echo "Error: " . $result['error'];
} else {
    echo "MRZ data:\n";
    foreach ($result['mrzData'] as $line) {
        echo $line . "\n";
    }
}