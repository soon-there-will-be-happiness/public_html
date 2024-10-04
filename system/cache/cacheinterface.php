<?php

interface cacheinterface
{
    public static function get($key);
    public static function set($key, $value, $expiration = 0);
    public static function delete($key);
    public static function getMultiple($keys);
    public static function setMultiple($keyAndValues, $expiration = 0);
    public static function deleteMultiple($keys);
    public static function has($key);
}