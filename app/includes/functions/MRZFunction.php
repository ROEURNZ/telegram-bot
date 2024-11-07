<?php

// backend/app/include/function/MRZFunction.php

/**
 * Process MRZ image and extract text.
 *
 * @param string $filePath The file path to the image.
 * @return array Contains extracted MRZ data or an error message if extraction fails.
 */
function processMrzImage($filePath)
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['error' => 'File not accessible: ' . htmlspecialchars(basename($filePath))];
    }

    // Updated tesseract command with whitelist and language option
    $mrzCmd = '"C:\\Program Files\\Tesseract-OCR\\tesseract.exe" ' . escapeshellarg($filePath) . ' stdout --psm 6 -c "tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789<" -l eng';
    $mrzOutput = @shell_exec($mrzCmd);

    if ($mrzOutput === null) {
        return ['error' => 'MRZ execution failed', 'command' => $mrzCmd, 'output' => $mrzOutput];
    }

    $text = trim($mrzOutput);
    if (empty($text)) {
        return ['error' => 'No text found in image'];
    }

    // Extract MRZ data
    $mrzData = extractMrzData($text);

    if ($mrzData === 'MRZ data not found.') {
        return ['error' => 'Could not resolve this image, please try again.'];
    }

    return [
        'text' => $text,
        'mrzData' => $mrzData,
    ];
}

/**
 * Extract MRZ data from text.
 *
 * @param string $text The text obtained from MRZ processing.
 * @return array Extracted MRZ information or a message if MRZ not found.
 */
function extractMrzData($text)
{
    // The pattern for MRZ lines
    preg_match_all('/([A-Z0-9<]{44})|([A-Z0-9<]{30})|([A-Z0-9<]{36})/', $text, $matches);

    // Check if we have MRZ lines
    if (empty($matches[0])) {
        return 'MRZ data not found.';
    }

    // Combine matched lines into a single MRZ string or array for further processing
    $mrzLines = array_map('trim', $matches[0]);

    // MRZ data formatting for easier extraction
    $mrzInfo = [];
    foreach ($mrzLines as $line) {
        // Split the MRZ line by < symbol and clean up extra spaces
        $mrzInfo[] = explode('<', $line);
    }

    return $mrzInfo;
}

// Usage example
$filePath = 'C:\\Users\\PC\\Development\\Projects\\PHP\\Bots\\telegram-bot\\app\\images\\cb819975-7cd1-4d90-8826-fc765b703cbf.png';
$result = processMrzImage($filePath);

if (isset($result['error'])) {
    echo "Error: ". $result['error'];
} else {
    // Process the extracted MRZ data here
    echo "MRZ Data: \n";
    foreach ($result['mrzData'] as $line) {
        echo implode(' ', $line) . "\n"; // Print each line of MRZ data
    }
}
