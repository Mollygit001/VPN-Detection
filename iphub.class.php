<?php
namespace IPHub;

class Lookup
{
    public static function isBadIP(string $ip, string $key, bool $strict = false)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "http://v2.api.iphub.info/ip/{$ip}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["X-Key: {$key}"]
        ]);

        try {
            $ce = json_decode(curl_exec($ch));
            if (isset($ce->block)) {
                $block = $ce->block;
            } else {
                header('HTTP/1.1 503 Service Unavailable');
                die('<style>* { color: #444; background-color: #0fb; }</style><pre><h1>The API limit has been exceeded or is currently unavailable.</h1></pre>');
            }
        } catch (Exception $e) {
            throw $e;
        }

        if ($block) {
            return $strict ? true : $block === 1;
        }
        return false;
    }

    public static function getIPInfo(string $ip, string $key)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "http://v2.api.iphub.info/ip/{$ip}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["X-Key: {$key}"]
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        $ipInfo = json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        return $ipInfo;
    }
}