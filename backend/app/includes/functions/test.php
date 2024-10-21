<?php

// Include the OCRFunction.php file
require_once '../../includes/functions/OCRFunction.php';

// Path to the invoice image (make sure this image exists)
$imagePath = '../../images/photo_2024-10-17_08-50-37.jpg'; // Update with your image path

// Process the invoice image
$ocrResult = processInvoiceImage($imagePath);

// Extract the VAT-TIN and raw text from the result
$vatTin = $ocrResult['vatTin'];
$rawText = $ocrResult['text'];

// Output the results (for testing purposes)
echo "Extracted VAT-TIN: " . $vatTin . "\n";
echo "Raw OCR Text: " . $rawText . "\n";