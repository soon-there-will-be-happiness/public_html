<?php


class memcacheServer implements cacheinterface
{
    /** @var memcacheServer объект */
    private static $memcacheobj;

    public static $memcachesettings;

    private static function getMemcache(){
        if (!isset(self::$memcacheobj)) {
            $obj = new Memcached();
            $obj->addServer('127.0.0.1', self::$memcachesettings['port']);
            self::$memcacheobj = $obj;
        }
        //TODO: логирование подключения
        return self::$memcacheobj;
    }

    public static function get($key)
    {
        return self::getMemcache()->get($key);
    }

    public static function set($key, $value, $expiration = 0)
    {
        return self::getMemcache()->set($key, $value, $expiration);
    }


    public static function delete($key)
    {
        return self::getMemcache()->delete($key);
    }

    public static function clear()
    {
        return self::getMemcache()->flush();
    }

    public static function getMultiple($keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }
        return $result;
    }

    public static function setMultiple($keyAndValues, $expiration = 0)
    {
        $result = [];
        foreach ($keyAndValues as $key=>$value){
            $result[$key] = self::set($key, $value, $expiration);
        }
        return $result;
    }

    public static function deleteMultiple($keys)
    {
        $result = [];
        foreach ($keys as $key){
            $result[$key] = self::delete($key);
        }
        return $result;
    }

    public static function has($key)
    {
        return self::getMemcache()->get($key) ? true : false;
    }

    public static function getAllKeys() {
        return array_values(self::getMemcache()->getAllKeys());
    }

    public static function getStats() {
        return self::getMemcache()->getStats();
    }
}