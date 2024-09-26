<?php
session_start(); // Start the session

// Check if decoded results are available in the session
if (!isset($_SESSION['decodedResults']) || empty($_SESSION['decodedResults'])) {
    die('No decoded results found.');
}

$decodedResults = $_SESSION['decodedResults']; // Retrieve the decoded results from the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decoded Barcodes</title>
    <!-- <link rel="stylesheet" href="../../public/css/tailwind.css"> -->
    <!-- TailwindCSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <style>
        /* Custom styles for card hover effect */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden; /* Hide overflow */
        }

        .card:hover {
            transform: scale(1.05); /* Scale up on hover */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Enhance shadow on hover */
        }

        /* Ensure long text is hidden */
        .overflow-hidden {
            overflow: hidden;
            text-overflow: ellipsis; /* Show ellipsis for overflowing text */
            white-space: nowrap; /* Prevent text wrapping */
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">

    <!-- Main container with padding -->
    <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-10 bg-white shadow-md rounded-lg mt-10">
        
        <!-- Sticky page title -->
        <h1 class="text-3xl font-bold text-center text-blue-600 mb-10 sticky-title">
            Decoded Barcodes or QR Codes
        </h1>

        <!-- Grid for the barcode results -->
        <div class="grid gap-8 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            
            <!-- Loop through decoded results and display each one -->
            <?php foreach ($decodedResults as $result): ?>
                <div class="card bg-gray-50 p-6 rounded-lg shadow-lg border">
                    
                    <!-- Image container -->
                    <div class="w-full max-w-full mb-4 flex justify-center">
                        <img src="../../storage/app/public/images/decoded/<?php echo htmlspecialchars(basename($result['file']), ENT_QUOTES, 'UTF-8'); ?>" 
                             class="w-full h-auto max-h-64 object-contain" 
                             alt="Uploaded Image">
                    </div>

                    <!-- File details and barcode information -->
                    <div class="space-y-2">
                        <p class="overflow-hidden"><strong class="text-blue-600">File Name:</strong> <?php echo htmlspecialchars($result['name']); ?></p>
                        <p class="overflow-hidden"><strong class="text-blue-600">File Size:</strong> <?php echo round($result['size'] / 1024, 2); ?> KB</p>
                        <p class="overflow-hidden"><strong class="text-blue-600">File Type:</strong> <?php echo htmlspecialchars($result['type']); ?></p>
                        <p class="text-lg font-semibold text-gray-800 overflow-hidden">
                            <strong class="text-blue-600">Decoded Info:</strong> <?php echo htmlspecialchars($result['code'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <p class="text-sm text-gray-600 overflow-hidden">
                            <strong class="text-blue-600">Decode Image Type:</strong> <?php echo htmlspecialchars($result['barcode_type']); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</body>
</html>

<?php
/** @access Clear the session after displaying the results */
unset($_SESSION['decodedResults']);
