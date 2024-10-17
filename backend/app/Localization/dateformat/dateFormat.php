<?php

// Localization/dateformat/dateFormat.php
date_default_timezone_set("Asia/Phnom_Penh");
// Khmer month names
$khmerMonths = [
    '01' => 'មករា',
    '02' => 'កុម្ភៈ',
    '03' => 'មិនា',
    '04' => 'មេសា',
    '05' => 'ឧសភា',
    '06' => 'មិថុនា',
    '07' => 'កក្កដា',
    '08' => 'សីហា',
    '09' => 'កញ្ញា',
    '10' => 'តុលា',
    '11' => 'វិច្ឆិកា',
    '12' => 'ធ្នូ',
];

// English month names
$englishMonths = [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    '04' => 'April',
    '05' => 'May',
    '06' => 'June',
    '07' => 'July',
    '08' => 'August',
    '09' => 'September',
    '10' => 'October',
    '11' => 'November',
    '12' => 'December',
];

// Arabic to Khmer number mapping
$arabicToKhmer = [
    '0' => '០',
    '1' => '១',
    '2' => '២',
    '3' => '៣',
    '4' => '៤',
    '5' => '៥',
    '6' => '៦',
    '7' => '៧',
    '8' => '៨',
    '9' => '៩',
];

// Function to format the date based on language
function formatDate($language) {
    global $khmerMonths, $englishMonths, $arabicToKhmer;

    // Get current date components
    $currentDate = new DateTime();
    $day = $currentDate->format('d');  // Keep the leading zero
    $month = $currentDate->format('m');
    $year = $currentDate->format('Y');

    // Convert day and year to Khmer if the language is Khmer
    if ($language === 'kh') {
        // Convert day and year to Khmer numerals
        $day = implode('', array_map(fn($digit) => $arabicToKhmer[$digit], str_split($day)));
        $monthName = $khmerMonths[$month];
        $year = implode('', array_map(fn($digit) => $arabicToKhmer[$digit], str_split($year)));
        return sprintf('ថ្ងៃទី %s ខែ %s ឆ្នាំ %s', $day, $monthName, $year);
    } else {
        // For English, use Month Day, Year format without leading zero on day
        $monthName = $englishMonths[$month];
        return sprintf('%s %d, %s', $monthName, (int)$day, $year);  // (int)$day removes the leading zero
    }
}

// Function to format time based on language
function formatTime($language) {
    global $arabicToKhmer;

    // Get current time components
    $currentTime = new DateTime();
    $hour = $currentTime->format('h');
    $minute = $currentTime->format('i');
    $second = $currentTime->format('s');
    $period = $currentTime->format('A'); // AM/PM

    // Convert time components to Khmer if the language is Khmer
    if ($language === 'kh') {
        $hour = implode('', array_map(fn($digit) => $arabicToKhmer[$digit], str_split($hour)));
        $minute = implode('', array_map(fn($digit) => $arabicToKhmer[$digit], str_split($minute)));
        $second = implode('', array_map(fn($digit) => $arabicToKhmer[$digit], str_split($second)));
        $period = $period === 'AM' ? 'ព្រឹក' : 'ល្ងាច'; // AM/PM in Khmer
        return sprintf('ម៉ោង៖ %s:%s:%s %s', $hour, $minute, $second, $period);
    } else {
        // Return time in English format with leading zero on hour
        return sprintf('%s:%s:%s %s', $hour, $minute, $second, $period);
    }
}


// Assuming formatDate() and formatTime() functions are already defined as previously shown.

// Function to get the current date and time based on language
function getDateTime($language) {
    // Get formatted date and time
    $formattedDate = formatDate($language);
    $formattedTime = formatTime($language);
    
    // Dynamically change the output format based on language
    if ($language === 'kh') {
        // Khmer: Time first, then date
        return sprintf('%s, %s', $formattedTime, $formattedDate);
    } else {
        // English: Date first, then time
        return sprintf('%s, %s', $formattedDate, $formattedTime);
    }
} 


