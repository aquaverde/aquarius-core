<?php

class Cache {
    static $entries = array();

    static function call($key, $calc) {
        if (!isset(self::$entries[$key])) {
            self::$entries[$key] = $calc();
        }
        return self::$entries[$key];
    }

    static function clean() {
        self::$entries = array();
    }
}