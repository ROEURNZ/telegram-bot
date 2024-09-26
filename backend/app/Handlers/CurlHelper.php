<?php
// backend/app/Handlers/CurlHelper.php
namespace App\Handlers;

class CurlHelper
{
    public static function execute(string $url, array $postData): ?string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        if ($response === false) {
            error_log('CURL Error: ' . curl_error($ch));
            return null;
        }

        curl_close($ch);
        return $response;
    }
}
