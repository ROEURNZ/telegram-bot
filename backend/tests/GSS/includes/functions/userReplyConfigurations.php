<?php

/**
 * Translates a given string into the desired language.
 *
 * This function looks up the translation for the provided key and
 * can also format the translation with parameters.
 *
 * @param mixed $key The key representing the text to be translated. 
 *                   It can be a string representing the translation key 
 *                   or an array of keys for multiple translations.
 * @param array $params Optional. An associative array of parameters to 
 *                      format the translated string. For example, 
 *                      if the translation contains placeholders, 
 *                      you can pass values for those placeholders here.
 *
 * @return mixed The translated string or an array of translated strings, 
 *               depending on the input key.
 */
/**
function language($key, $params = array()) { }

*/


/**
 * Array of response configurations for various application states.
 * Each key represents a specific state or action, containing information
 * about live location requests, image requirements, freetext needs,
 * and buttons that will be displayed to the user.
 *
 * @var array $arr
 */

$arr = [
    'select_lang' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_lang_english'),
            language('button_lang_khmer')
        ]
    ],
    'register_req_photo' => [
        'live_location' => false,
        'location' => false,
        'image' => true,
        'freetext' => false,
        'button' => false,
    ],
    'register_req_id' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => true,
        'button' => [
            language('skip_and_send')
        ]
    ],
    'clock_out_done' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_clock_in')
        ]
    ],
    'approved' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_clock_in')
        ]
    ],
    'clock_in_done' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_start_break'),
            language('button_start_visit'),
            language('button_clock_out'),
            language('button_yes'),
            language('button_remind_later')
        ]
    ],
    'clock_out_yes_no' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_yes'), 
            language('button_no')
        ]
    ],
    'clock_out_live_location' => [
        'live_location' => true,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => false
    ],
    'clock_out_share_selfie' => [
        'live_location' => false,
        'location' => false,
        'image' => true,
        'freetext' => false,
        'button' => false
    ],
    'clock_in_live_location' => [
        'live_location' => true,
        'image' => false,
        'freetext' => false,
        'button' => false
    ],
    'clock_in_req_selfie' => [
        'live_location' => false,
        'location' => false,
        'image' => true,
        'freetext' => false,
        'button' => false
    ],
    'start_break_req_location' => [
        'live_location' => true,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => false
    ],
    'start_break_req_selfie' => [
        'live_location' => false,
        'location' => false,
        'image' => true,
        'freetext' => false,
        'button' => false
    ],
    'on_break' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_end_break'),
            language('button_clock_out'),
            language('button_yes'),
            language('button_remind_later')
        ]
    ],
    'end_break_req_location' => [
        'live_location' => true,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => false
    ],
    'end_break_req_selfie' => [
        'live_location' => false,
        'location' => false,
        'image' => true,
        'freetext' => false,
        'button' => false
    ],
    'start_visit_req_location' => [
        'live_location' => true,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => false
    ],
    'start_visit_req_selfie' => [
        'live_location' => false,
        'location' => false,
        'image' => true,
        'freetext' => false,
        'button' => false
    ],
    'start_visit_req_note' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => true,
        'button' => false
    ],
    'on_visit' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => [
            language('button_end_visit'),
            language('button_clock_out'),
            language('button_yes'),
            language('button_remind_later')
        ]
    ],
    'end_visit_req_location' => [
        'live_location' => true,
        'location' => false,
        'image' => false,
        'freetext' => false,
        'button' => false
    ],
    'end_visit_req_note' => [
        'live_location' => false,
        'location' => false,
        'image' => false,
        'freetext' => true,
        'button' => false
    ]
];

/**
 * Constant defining acceptable replies in the application. 
 */
define('ACCEPTABLE_REPLY', $arr);

