<?php
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

