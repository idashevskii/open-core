<?php

namespace OpenCore\Utils;

use DateTime;

class Dates {

    /**
     * @param int $timestamp
     * @return DateTime
     */
    public static function fromTimestamp(int $timestamp) {
        $ret = new DateTime();
        $ret->setTimestamp($timestamp);
        return $ret;
    }

    public static function timestampToDb(int $timestamp) {
        return self::fromTimestamp($timestamp)->format('Y-m-d H:i:s');
    }

}
