<?php


return $messages = [
    // Language and welcome flow
    'welcome_message' => 'Welcome to the bot! You can interact with me now.',
    'please_choose_language' => 'Please choose your language to continue',
    'language_selection' => 'You selected English.',
    // Help and guidance
    'help_message' => "This is your help message. You can use /restart to restart.",
    'please_start' => 'Please start the bot by sending /start.',
    /*** ! Image upload and barcode decoding ****/
    'upload_barcode' => "Please upload an image containing a barcode or QR code.",
    'contact_required' => "Please share your contact information first.",
    'download_prompt' => "Please wait for a few seconds, the bot is downloading images.",
    'decode_prompt_sent' => "The decode prompt has been sent. Please upload an image containing a barcode or QR code.",
    'waiting_for_image' => "Waiting for your image. Please upload an image containing a barcode or QR code.",
    'complete_previous_steps' => "Please complete the previous steps before decoding.",
    'restart' => '',
    // Contact sharing
    'contact_prompt' => "Please share your contact information using the button below.",
    'thanks_for_contact' => "Thanks for sharing your contact!\nFull Name: %s %s\nPhone Number: %s\nUsername: %s",
    // 'thanks_for_contact' => "Thanks for sharing your contact!\nTelegram User ID: %s\nTelegram Chat ID: %s\nFull Name: %s %s\nPhone Number: %s\nUsername: %s",

    // Location sharing
    'location_prompt' => "Please share your location using the button below. This works best on mobile devices.",
    'thanks_for_location' => " Date: %s, %s\nDecoded Codes:\n%s\nLocation: %s",
    'thank_you_location' => "Thank you for sharing your location!",

    // Decoding results
    'decode_success' => "Successfully decoded the barcode!\nType: %s\nData: %s",
    'decode_failure' => "Failed to decode the barcode. Please try again or upload a clearer image.",

    // Error messages
    'image_download_failed' => "Failed to download the image. Please try again.",
    'file_not_found' => "File not found. Please upload a valid image.",

    // Button texts
    'language_option' => '🇺🇸 English',
    'share_contact' => '📞 Share Contact',
    'share_location' => '📍 Share Location',

    'contact_request' => 'Please share your contact information using the button below',
    'location_request' => 'Please share your current location.',



    'help' => 'This is your help message. You can use /start to begin.',
    'menu' => 'Menu options: /start, /help',
    'barcode_error' => 'Could not decode the barcode. Please try again with a clearer image.',
    'image_error' => 'Failed to retrieve the image file. Please try again.',

    'restart_message' => 'You can select a language to restart',



    
    'please_select_language' => 'Please select a language first.',
    'language_prompt' => 'Please choose your language.',
    'contact_not_registered' => 'Your contact is not registered. Please share your contact first.',
    'share_contact_prompt' => 'You can share your contact by using the /share_contact command.',
    'location_prompt' => 'Please share your location.',
    'decode_not_completed' => 'You have not completed the decode process yet.',
    'upload_barcode_prompt' => 'Please upload a barcode image to decode.',
    'menu' => 'Here are the available commands: /share_contact, /decode, /share_location, /change_language.',
    'location_not_shared' => 'You have not shared your location yet.',



    // Commands Descriptions

    'start_description' => 'Start the bot.',
    'stop_description' => 'Stop the bot.',
    'help_description' => 'Get help on using the bot.',
    'menu_description' => 'Display the command menu.',
    'share_contact_description' => 'Share your contact information.',
    'share_location_description' => 'Share your current location.',
    'decode_description' => 'Decode a barcode or QR code.',
    'change_language_description' => 'Change the language of the bot.',
    'command_menu_header' => 'Here are the available commands:',
];
