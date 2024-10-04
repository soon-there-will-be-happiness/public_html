<?php


class filecache implements cacheinterface
{
    static $cachedir = ROOT.'/system/cache/cachefiles/';


    public static function get($key, $default = null)
    {
        if (file_exists(self::$cachedir.$key)) {
            $result = file_get_contents(self::$cachedir . $key);

            $date = explode('_', $result, 2);
            $filetime = filemtime(self::$cachedir.$key);
            if ($date[0] != 0 && $filetime + $date[0] < time() ) {
                self::delete($key);
                return null;
            }
            $result = substr($result, strlen($date[0]) + 1);
        }
        return $result ?? null;
    }

    public static function set($key, $value, $expiration = null)
    {
        if (!is_dir(self::$cachedir)) {
            mkdir(self::$cachedir);
        }
        $result = file_put_contents(self::$cachedir . $key, $expiration.'_'.$value);
        return $result ? true : false;
    }

    public static function delete($key)
    {
        if (file_exists(self::$cachedir.$key)) {
            return unlink(self::$cachedir . $key);
        }
        return false;
    }

    public static function clear()
    {
        if (file_exists(self::$cachedir)) {
            foreach (glob(self::$cachedir.'/*') as $file) {
                unlink($file);
            }
        }
    }

    public static function getMultiple($keys, $default = null)
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
        if (file_exists(self::$cachedir.$key)) {
            return true;
        }
        return false;
    }

    public static function getAllKeys() {
        return array_values(scandir(self::$cachedir));
    }

    public static function getStats() {

        $x = 0;
        if($r = opendir(self::$cachedir)){// открываем папку
            while(false !== ($c = readdir($r))){
                //  readdir($r)) - читает по очереди имена файлов и папок и засовывает в $c
                //  в момент когда все файлы и папки прочитаны в $c команда readdir засунет в $c значение false
                //  Сработает false !== false и цикл while остановится. Т.е. закончит читать папку
                if ($c != "." && $c != ".." ){ //в любой папке любой ОС есть элементы ссылки
                    //на текущий каталог . (одна точка) и на родителя .. (две точки)
                    // они скрыты, но есть - нам они не нужны - откидываем этой командой

                    // непосредственно находящийся выше текущего справочника в иерархии файловой системы.
                    // Справочник, представленный двумя точками, называется родительским для справочника,
                    //  обозначенного одной точкой (вашего текущего справочника).

                    $x += filesize(self::$cachedir.$c);//путь к файоу, имя папки имя файла - байты
                }
            }
            closedir($r);//закрыли диреткорию
        }
        return [
            'count' => count(self::getAllKeys()) - 2,
            'bytes' => $x,
        ];
    }


}