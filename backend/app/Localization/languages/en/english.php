<?php


return $messages = [
    // Language and welcome flow
    'welcome_message' => 'Hello! Welcome to your bot operation.',
    'please_choose_language' => 'Please choose your language:',
    'language_selection' => 'You have selected English..',
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
    'contact_prompt' => "Please share your contact information.",
    'thanks_for_contact' => "Thanks for sharing your contact!\nFull Name: %s %s\nPhone Number: %s\nUsername: %s",
    
    // Location sharing
    'location_prompt' => "Please share your locationto continue. This works best on mobile devices.",
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
    'contact_request' => 'Please share your contact information.',
    'location_request' => 'Please share your current location to continue.',
    

    
    'help' => 'This is your help message. You can use /start to begin.',
    'menu' => 'Menu options: /start, /help',
    'barcode_error' => 'Could not decode the barcode. Please try again with a clearer image.',
    'image_error' => 'Failed to retrieve the image file. Please try again.',

    'restart_message' => 'You can select a language to restart',
];
