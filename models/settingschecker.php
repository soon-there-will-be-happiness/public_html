<?php


class SettingsChecker {

    //Методы для проверки настроек
    use checksettings;

    public static function run() {

        self::$settings = System::getSetting();

        $methods = self::getCheckMethods();

        $result = [];
        foreach ($methods as $method) {
            $result[$method] = self::resultHandler(call_user_func([SettingsChecker::class, $method]), $method);
        }

        return $result;
    }

    private static function resultHandler($result, $method) {

        if (!isset($result['name']) || !isset($result['status']) || !isset($result['message'])) {
            $class = __CLASS__;
            throw new Exception("Результат работы метода $class::$method() должен возвращать массив с ключами: (str)name, (bool)status, (str)message");
        }

        return $result;
    }

    private static function getCheckMethods() {

        $reflection = new ReflectionClass(checksettings::class);
        $reflectionMethods = $reflection->getMethods();
        $methods = [];

        foreach ($reflectionMethods as $method) {
            $methods[] = $method->name;
        }

        return $methods;
    }

}