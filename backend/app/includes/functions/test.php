<?php

// Include the OCRFunction.php file
require_once '../../includes/functions/OCRFunction.php';

// Path to the invoice image (make sure this image exists)
$imagePath = '../../images/photo_2024-10-17_08-50-37.jpg'; // Update with your image path

// Process the invoice image
$result = processInvoiceImage($imagePath);

// Output the result
if (isset($result['error'])) {
    echo "Error: " . $result['error'];
} else {
    echo "VAT-TIN: " . $result['vatTin'] . "\n";
}

?>
