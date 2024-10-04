<?php

class cache implements cacheinterface
{
    static $extensTime = 400;
    static $adminTime1 = 400;


    /** @var string через что кешироварать: "file"/"memcached" TODO добавить redis */
    static $driver;

    static $cacheconfig;

    public static function getConfig() {
        if (empty(self::$cacheconfig)) {
            if (file_exists( ROOT . '/config/cache.php')) {
                self::$cacheconfig = require ROOT . '/config/cache.php';//получаем настройки
                self::$driver = self::$cacheconfig['type'];//тип драйвера
            } else {
                self::$cacheconfig['enable'] = 0;
                self::$cacheconfig['type'] = 'file';
                self::$driver = self::$cacheconfig['type'];
            }
            if (self::$driver == 'memcached') {
               memcacheServer::$memcachesettings = self::$cacheconfig['memcached'];//передаем настройки в memcached
            }
        }
        return self::$cacheconfig;
    }


    /**
     * Получить кешированное значение
     *
     * @param $key
     * @param null $default
     *
     * @return false|string|null
     */
    public static function get($key)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::get($key);
                break;
            default:
                return json_decode(filecache::get($key), true);
                break;
        }
    }

    /**
     * Закешировать значение
     *
     * @param $key
     * @param $value
     * @param int $expiration
     *
     * @return bool
     */
    public static function set($key, $value, $expiration = 0)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::set($key, $value, $expiration);
                break;
            default:
                return filecache::set($key, json_encode($value), $expiration);
                break;
        }
    }

    /**
     * Удалить значение
     *
     * @param $key
     *
     * @return bool
     */
    public static function delete($key)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::delete($key);
                break;
            default:
                return filecache::delete($key);
                break;
        }
    }

    /**
     * очистить кеш
     */
    public static function clear($driver)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch ($driver) {
            case 'memcached':
                return memcacheServer::clear();
                break;
            default:
                return filecache::clear();
                break;
        }
    }

    public static function clearCurrentDriver() {
        return self::clear(self::$driver);
    }

    /**
     * Получить несколько значений из кеша
     *
     * @param $keys
     * @param null $default
     *
     * @return array
     */
    public static function getMultiple($keys, $default = null)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::getMultiple($keys);
                break;
            default:
                return filecache::getMultiple($keys);
                break;
        }
    }

    /**
     * Закешировать несколько значений
     *
     * @param $keyAndvalues
     * @param null $expiration
     *
     * @return array
     */
    public static function setMultiple($keyAndvalues, $expiration = null)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::setMultiple($keyAndvalues, $expiration);
                break;
            default:
                return filecache::setMultiple($keyAndvalues);
                break;
        }
    }

    /**
     * Удалить несколько значений
     *
     * @param $keys
     *
     * @return array
     */
    public static function deleteMultiple($keys)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::deleteMultiple($keys);
                break;
            default:
                return filecache::deleteMultiple($keys);
                break;
        }
    }

    /**
     * Проверить на существование
     *
     * @param $key
     *
     * @return bool
     */
    public static function has($key)
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::has($key);
                break;
            default:
                return filecache::has($key);
                break;
        }
    }

    public static function getAllKeys()
    {
        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch (self::$driver) {
            case 'memcached':
                return memcacheServer::getAllKeys();
                break;
            default:
                return filecache::getAllKeys();
                break;
        }
    }

    public static function getStats($driver) {

        if (self::getConfig()['enable'] == 0) {
            return null;
        }

        switch ($driver) {
            case 'memcached':
                if (self::getConfig()['type'] == 'memcached') {
                    return memcacheServer::getStats();
                }
                return null;
                break;
            default:
                return filecache::getStats();
                break;
        }
    }
}