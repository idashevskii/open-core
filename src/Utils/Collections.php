<?php

namespace OpenCore\Utils;

use Closure;

class Collections {

    public static function toList($collecton) {
        $ret = [];
        foreach ($collecton as $value) {
            $ret[] = $value;
        }
        return $ret;
    }

    public static function every($collecton, Closure $callback): bool {
        $ret = true;
        if ($collecton) {
            foreach ($collecton as $key => $value) {
                if (!$callback($value, $key)) {
                    $ret = false;
                    break;
                }
            }
        }
        return $ret;
    }

    public static function some($collecton, Closure $callback): bool {
        $ret = false;
        if ($collecton) {
            foreach ($collecton as $key => $value) {
                if ($callback($value, $key)) {
                    $ret = true;
                    break;
                }
            }
        }
        return $ret;
    }

    public static function map($collecton, Closure $callback): array {
        $ret = [];
        if ($collecton) {
            foreach ($collecton as $key => $value) {
                $ret[] = $callback($value, $key);
            }
        }
        return $ret;
    }

    public static function equals($collecton1, $collecton2) {
        $ret = (count($collecton1) === count($collecton2));
        if ($ret) {
            foreach ($collecton1 as $key => $value) {
                if (!array_key_exists($key, $collecton2) || $value !== $collecton2[$key]) {
                    $ret = false;
                    break;
                }
            }
        }
        return $ret;
    }

    public static function indexBy($collecton, Closure $callback): array {
        $ret = [];
        foreach ($collecton as $item) {
            $ret[$callback($item)] = $item;
        }
        return $ret;
    }

    public static function groupBy($collecton, Closure $callback): array {
        $ret = [];
        foreach ($collecton as $item) {
            $ret[$callback($item)][] = $item;
        }
        return $ret;
    }

    public static function find($collecton, Closure $callback) {
        $ret = null;
        foreach ($collecton as $item) {
            if ($callback($item)) {
                $ret = $item;
                break;
            }
        }
        return $ret;
    }

    public static function without($collecton, $item): array {
        return self::filter($collecton, function($value)use($item) {
                    return !$value->equals($item);
                });
    }

    public static function filter($collecton, Closure $callback): array {
        $ret = [];
        if ($collecton) {
            foreach ($collecton as $key => $value) {
                if ($callback($value, $key)) {
                    $ret[] = $value;
                }
            }
        }
        return $ret;
    }

    public static function chain($collecton) {
        return new FuncCollectionChain($collecton);
    }

}
