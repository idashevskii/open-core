<?php

namespace OpenCore\Log;

class LogUtils {

    public static function traceToLines($trace) {
//        print_r($trace);


        $caller = array_shift($trace);
        $function_name = $caller['function'];
        $lines = [];
        $lines[] = sprintf('%s: Called from %s:%s', $function_name, isset($caller['file']) ? $caller['file'] : null, isset($caller['line']) ? $caller['line'] : null);
        foreach ($trace as $entry) {
            $entry['file'] = isset($entry['file']) ? $entry['file'] : '-';
            $entry['line'] = isset($entry['line']) ? $entry['line'] : '-';
            if (empty($entry['class'])) {
                $lines[] = sprintf('%s() %s:%s', $entry['function'], $entry['file'], $entry['line']);
            } else {
                $lines[] = sprintf('%s->%s() %s:%s', $entry['class'], $entry['function'], $entry['file'], $entry['line']);
            }
        }


        return $lines;
    }

}
