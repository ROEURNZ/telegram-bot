<?php

// Function to identify barcode type
function identifyBarcodeType($code)
{
    $trimmedCode = trim($code);

    // Check for known barcode and QR Code formats
    if (strpos($trimmedCode, 'BEGIN:VCARD') !== false) return 'QR Code (vCard)';
    if (strpos($trimmedCode, 'WIFI:') !== false) return 'QR Code (Wi-Fi)';
    if (strpos($trimmedCode, 'mailto:') !== false) return 'QR Code (Email)';
    if (strpos($trimmedCode, 'tel:') !== false) return 'QR Code (Phone Number)';
    if (filter_var($trimmedCode, FILTER_VALIDATE_URL)) return 'QR Code (URL)';

    // Barcode formats
    if (preg_match('/^[0-9]{8}$/', $trimmedCode)) return 'EAN-8 Barcode';
    if (preg_match('/^[0-9]{13}$/', $trimmedCode)) return 'EAN-13 Barcode';
    if (preg_match('/^978[0-9]{10}$/', $trimmedCode)) return 'ISBN Barcode';
    if (preg_match('/^979[0-9]{10}$/', $trimmedCode)) return 'ISBN Barcode';
    if (preg_match('/^[0-9]{12}$/', $trimmedCode)) return 'UPC Barcode';
    if (preg_match('/^\d{14}$/', $trimmedCode)) return 'GS1 Barcode';

    // Alphanumeric barcodes
    if (preg_match('/^[0-9A-Z\-]{2,30}$/', $trimmedCode)) return 'Code 39 Barcode';
    if (preg_match('/^[0-9A-Z\-]{1,30}$/', $trimmedCode)) return 'Code 93 Barcode';
    if (preg_match('/^([0-9]{1,12}|[0-9]{1,5}(\s|[0-9]{1,5}){0,1})$/', $trimmedCode)) return 'Code 128 Barcode';

    // Other barcode formats
    if (preg_match('/^[A-B0-9]+$/', $trimmedCode)) return 'Codabar Barcode';
    if (preg_match('/^[0-9]{2,10}$/', $trimmedCode)) return 'ITF Barcode';
    if (preg_match('/^[0-9]{1,10}$/', $trimmedCode)) return 'MSI Barcode';
    if (preg_match('/^[0-9]{1,10}$/', $trimmedCode)) return 'Pharmacode Barcode';

    // 2D barcodes
    if (preg_match('/^[A-Za-z0-9+\/]{8,}$/', $trimmedCode)) return 'PDF417 Barcode';
    if (preg_match('/^[A-Z0-9]{10,}$/', $trimmedCode)) return 'Data Matrix Barcode';

    // Handle QR Code with plain text
    if (strlen($trimmedCode) > 0 && strpos($trimmedCode, ' ') !== false) {
        return 'QR Code (Text)';
    }

    return 'Unknown Barcode Type';
}
