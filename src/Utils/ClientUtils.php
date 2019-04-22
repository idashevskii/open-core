<?php

namespace OpenCore\Utils;

class ClientUtils {

    private static $clientIpVariables = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    public static function getClientIp() {
        $ret = null;
        foreach (self::$clientIpVariables as $key) {
            if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key]) {
                $ret = $_SERVER[$key];
                break;
            }
        }
        if (!$ret) {
            $ret = 'unknown';
        }
        return $ret;
    }

    public static function getClientUserAgent() {
        $key = 'HTTP_USER_AGENT';
        return isset($_SERVER[$key]) && is_string($_SERVER[$key]) ? $_SERVER[$key] : '';
    }

}
