

corrections and additions to enhance clarity and functionality:

# Commands and Buttons Processes

1. **Button to Start the Bot**
   - **Processes of Start Bot Button:**
     + Users must click on the "Start" button to begin interacting with the bot.
     + Users cannot skip this step. If users don't start, they cannot proceed to the next step. This is a restricted step.

2. **Select Language Buttons (English and Khmer)**
   - **Processes of Language Selection Buttons:**
     + "US English" and "KH ភាសាខ្មែរ" buttons allow users to continue in their preferred language.
     + This step is only accessible after users have clicked the "Start" button in the previous step.
     + This step also follows the restricted step approach.

3. **Share Contact**
   - **Processes of Share Contact:**
     + This step follows the language selection step and remains a restricted step. Without completing the language selection, users cannot proceed.
     + Users can share their contact in two ways: by clicking the "Share Contact" button (the button's language changes dynamically based on the previous step) or by using the command `/share_contact`.
     + The command `/share_contact` will send the user's contact to the Telegram bot with one click.

4. **Upload QR Code or Barcode Images (One or Many)**
   - **Processes of Uploading Barcode Images:**
     + Users can upload barcode images or take photos of barcodes to send/upload one or more images (QR Code is optional).
     + This step is only accessible if the prior steps are completed successfully, maintaining the restricted step approach.
     + When this step is reached, the bot sends a message: `'upload_prompt' => "Please upload images containing barcodes or QR codes."`
     + While images are uploading, the bot sends a message: `'decode_prompt' => "Please wait for a few seconds, the bot is processing the images."` Users can proceed by clicking the `/decode` command or the "Decoding" button.
     + The decoded results are sent to the Telegram bot immediately.

5. **Share Current Location**
   - **Processes of Sharing Location:**
     + Sharing location follows the restricted step approach. If the prior step fails, this step will not be accessible.
     + Users can share their location in two ways: by clicking the `/sharelocation` command or the "Share Location" button, which will directly get the user's current location.

## Additional Features and Functions

### Commands
- `/stop`: To end the processes forcefully.
- `/help`: To get help and information about the bot's commands and functionalities.
- `/restart`: To restart the bot from the beginning.

Would you like to add any specific functionalities or commands?