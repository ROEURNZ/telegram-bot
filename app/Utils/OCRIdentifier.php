<?php

function extractTaxIdentifiers($text)
{
    // Regular expression to match tax identifiers followed by their codes
    $pattern = '/\b(VAT[-\s]?TIN|VATTIN|GSTIN|TAX[-\s]?TIN|TAXTIN|TAX[-\s]?ID|TAXID)[\s:]*([A-Z0-9\-]+)/i';
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

    $results = [];
    foreach ($matches as $match) {
        $results[] = [
            'prefix' => $match[1],
            'code' => $match[2]
        ];
    }
    return $results;
}