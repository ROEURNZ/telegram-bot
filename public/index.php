<?php 
// include __DIR__ . "/../app/Handlers/ComandHandlers.php";

// index.php


session_start(); // Start session to access response data
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bot Communication</title>
    <!-- <link rel="stylesheet" href="./css/styles.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">

    <div class="container bg-white p-8 rounded shadow-lg w-full max-w-lg">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Test Bot Communication</h1>

        <!-- Message Form -->
        <form id="messageForm" class="space-y-4" action="Commands/send_message.php" method="post">
            <textarea id="messageInput" name="message" class="w-full p-4 border border-gray-300 rounded-md" placeholder="Type your message here..." rows="4"></textarea>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition duration-200">Send</button>
        </form>

        <!-- Fetch All Data Button -->
        <form id="fetchForm" class="mt-4" action="Commands/fetch_data.php" method="post">
            <button type="submit" class="bg-green-500 text-white py-2 px-4 rounded-md hover:bg-green-600 transition duration-200">Fetch All Data</button>
        </form>

        <!-- File Upload Form -->
        <form id="fileUploadForm" class="mt-4" action="Commands/send_file.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file" class="mb-2">
            <button type="submit" class="bg-purple-500 text-white py-2 px-4 rounded-md hover:bg-purple-600 transition duration-200">Send File</button>
        </form>

        <!-- File Upload Form -->
        <form id="fileUploadForm" class="mt-4" action="Commands/send_multiplefiles.php" method="post" enctype="multipart/form-data">
            <input type="file" name="files[]" class="mb-2" multiple>
            <button type="submit" class="bg-purple-500 text-white py-2 px-4 rounded-md hover:bg-purple-600 transition duration-200">Send Files</button>
        </form>


        <!-- Response Display -->
        <div class="response mt-4 text-gray-700" id="response">
            <?php
            if (isset($_SESSION['response'])) {
                echo $_SESSION['response'];
                unset($_SESSION['response']);
            }
            ?>
        </div>
    </div>


    <!-- Upload Form for QR Code -->
    <form action="Commands/upload_qrcode.php" method="post" enctype="multipart/form-data" class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Upload QR Code Image</h2>
        <input type="file" name="images[]" accept="image/*" class="block w-full mb-4" multiple>
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Upload QR Code</button>
    </form>


    <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg mt-10">
    <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">Upload Images for Barcode or QR Code Decoding</h1>
    <form action="../app/Controllers/WebDecodeController.php" method="POST" enctype="multipart/form-data" class="text-center space-y-6">
        
        <!-- File Input -->
        <div>
            <label for="file-upload" class="block text-gray-700 font-medium mb-2">
                Select Images:
            </label>
            <input id="file-upload" type="file" name="images[]" accept="image/*" multiple required 
                   class="mb-4 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 transition duration-300">
            Upload and Decode
        </button>
    </form>
</div>

    <div class="max-w-4xl mx-auto p-6">
        <header class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-blue-600">Barcode and QR Code Reader</h1>
        </header>

        <!-- Form for Decoding -->
        <section class="mb-12">
            <h2 class="text-xl md:text-2xl font-semibold text-gray-800 mb-4">Decode QR Code or Barcode</h2>
            <form action="./Commands/decoder.php" method="get" class="bg-white p-6 rounded-lg shadow-md">
                <label for="img" class="block text-lg font-medium text-gray-700 mb-2">Image URL:</label>
                <input type="text" id="img" name="img" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                    class="mt-4 w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Decode
                </button>
            </form>
        </section>

        <!-- Data Collection Section -->
        <section class="mb-12">
            <h2 class="text-xl md:text-2xl font-semibold text-gray-800 mb-4">Collect Data from Bot</h2>
            <form action="./Commands/DataCollection.php" method="get" class="bg-white p-6 rounded-lg shadow-md">
                <input type="hidden" name="route" value="data_collection">
                <button type="submit" class="mt-4 w-full px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Fetch Data
                </button>

            </form>


            <div class="max-w-4xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">
                <h1 class="text-3xl font-bold text-center text-blue-600 mb-6">Telegram Bot Data Collection</h1>

                <?php if (isset($_SESSION['data']['result']) && is_array($_SESSION['data']['result'])): ?>
                    <table class="w-full table-auto">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border">Update ID</th>
                                <th class="px-4 py-2 border">Message ID</th>
                                <th class="px-4 py-2 border">Chat ID</th>
                                <th class="px-4 py-2 border">From ID</th>
                                <!-- <th class="px-4 py-2 border">Text</th> -->
                                <th class="px-4 py-2 border">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['data']['result'] as $update): ?>
                                <?php if (isset($update['message'])): ?>
                                    <tr>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($update['update_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($update['message']['message_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($update['message']['chat']['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($update['message']['from']['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars(date('Y-m-d H:i:s', $update['message']['date']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-lg font-medium text-gray-800">No updates found or error fetching data.</p>
                <?php endif; ?>

                <?php
                // Clear the data from the session after displaying it
                unset($_SESSION['data']);
                ?>
            </div>

        </section>

    </div>

</body>

</html>