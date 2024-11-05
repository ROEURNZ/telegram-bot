<?php

function loadLanguages() {

    $baseLanguage = array(
        'en' => include __DIR__ . '/../../Localization/languages/en/english.php',
        'kh' => include __DIR__ . '/../../Localization/languages/kh/khmer.php',
    );

    return $baseLanguage;
}
