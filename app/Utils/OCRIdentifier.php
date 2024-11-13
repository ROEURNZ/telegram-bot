<?php


function extractTaxIdentifiers($text)
{
    // Regular expression to match tax identifiers
    $pattern = '/\b(VAT[-\s]?TIN|VATTIN|GSTIN|TAX[-\s]?TIN|TAXTIN|TAX[-\s]?ID|TAXID)[\s:]*([A-Z0-9\-]+)/i';
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

    $results = [];
    $hasValidLength = false;
    $tin = null;

    foreach ($matches as $match) {
        $code = $match[2];
        $codeLength = strlen($code);

        // Store the code and its length
        $results[] = [
            'prefix' => $match[1],
            'code' => $code,
            'length' => $codeLength,
        ];

        // Check if the code length is 10 or more, and set `tin`
        if ($codeLength >= 10) {
            $hasValidLength = true;
            $tin = $code;  // Store the code only if its length is >= 10
        }
    }

    // Return the results with the updated tin and ocrhasvat status
    return [
        'taxIdentifiers' => $results,
        'ocrhasvat' => $hasValidLength ? 1 : 0,  // Set ocrhasvat based on code length
        'tin' => $tin,  // Return tin or null
    ];
}
