<?php 
// file name: load.php 

error_log(0);
date_default_timezone_set("Asia/Phnom_Penh");

include "vendor/autoload.php";
include __DIR__ . "/database/connection.php";
include __DIR__ . "/app/Config/api_key.php";

define('MAX_REMINDER', 5);
define('MAX_NOT_REPLY_REMINDER', 5);

// Load models
$path = __DIR__ . "/includes/models";
$files = array_diff(scandir($path), array('.', '..'));
foreach ($files as $file) {
    $m = explode('.', $file);
    $x = end($m);
    if ($x == 'php') {
        include $path."/".$file;
    }
}
// Load functions
include __DIR__ . "/includes/functions/functions.php";
$langActive = ['kh', 'en'];

$adminCommands = json_encode(array(
));

// $ez = new EzzeTeamsModel();
$ezzeTeamsModel = new EzzeModels();
// $ezzeTeamsModel = $ez;
// $botSettings = $ezzeTeamsModel->getSettings();
$botSettings['lang_active'] = $langActive;
$botSettings['max_reminder'] = MAX_REMINDER;
$botSettings['max_not_reply_reminder'] = MAX_NOT_REPLY_REMINDER;

$lang_allow = array('🇰🇭 ភាសាខ្មែរ', '🇺🇸 English');

// $admins = $ezzeTeamsModel->getAllAdmin();
$admin_id = [];
$adminDetails = [];
foreach($admins as $adm) {
    array_push($admin_id, $adm['user_id']);
    $adminDetails[$adm['user_id']] = $adm;
}

