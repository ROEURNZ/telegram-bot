<?php

require_once __DIR__ . '/LocationModel.php';
require_once __DIR__ . '/DecodeModel.php';
require_once __DIR__ . '/MrzExtractModel.php';
require_once __DIR__ . '/OcrExtractModel.php';
require_once __DIR__ . '/UserProfiles.php';

// $admModel = new Admin();
$decModel = new DecodeModel();
$locModel = new LocationModel();
$mrzModel = new MrzExtractModel();
$ocrModel = new OcrExtractModel();
$useModel = new UserProfiles();