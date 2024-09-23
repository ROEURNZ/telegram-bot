<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Bot Communication</title>
    <link rel="stylesheet" href="./css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

</head>

<body class="bg-gray-200 flex justify-center items-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-3xl font-semibold text-gray-900 mb-6 text-center">Test Bot Communication</h1>

        <!-- Message Form -->
        <form id="messageForm" class="space-y-4" action="#" method="post">
            <textarea id="messageInput" name="message" class="w-full p-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your message here..." rows="4"></textarea>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition duration-200">Send</button>
        </form>

        <!-- Fetch All Data Button -->
        <button id="fetchButton" class="mt-4 w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition duration-200">Fetch All Data</button>

        <!-- File Upload Form -->
        <form id="fileUploadForm" class="mt-4" action="#" method="post" enctype="multipart/form-data">
            <input type="file" name="files[]" class="mb-2 w-full text-gray-500 file:py-2 file:px-4 file:rounded-md file:border file:border-gray-300 file:text-gray-700 file:bg-gray-100 hover:file:bg-gray-200" multiple>
            <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-md hover:bg-purple-700 transition duration-200">Send Files</button>
        </form>
    </div>

</body>

</html>
