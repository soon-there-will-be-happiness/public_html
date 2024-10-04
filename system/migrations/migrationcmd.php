<?php

use cmd\colors;
use \Migrations\migrationHandler;

class migrationcmd
{

    public static function execute($args, $fresh = false, $installMigrationsInFresh = true)
    {
        echo colors::colors()->colorize("\n" . 'Запуск миграций!', 'green') . "\n";

        //Формирование задания исходя из аргументов консоли
        $needVersion = "all";
        $task = "migrate";

        $fileToMigrate = false;
        foreach ($args as $arg) {
            //Аргумент версии
            if (substr($arg, 0, 3) == "-v=") {
                $needVersion = substr($arg, 3);
            };
            //Конкретный файл
            if (substr($arg, 0, 6) == "-file=") {
                $fileToMigrate = substr($arg, 6);
            };
        }

        //показать только список миграций
        $migrationList = false;
        if ($key = array_search("--showList", $args)) {
            $migrationList = true;
            $task = "showList";
        }

        //Запуск down() методов у миграций - ролбек
        if ($key = array_search("--rollback", $args)) {
            $rollback = true;
            $task = "rollback";
            if ($migrationList) {
                $task = "rollbackShowList";
            }
        }

        //Полный откат Бд
        if ($fresh && !$installMigrationsInFresh) {
            $task = "fresh";
        }

        //Полный откат Бд и установка миграций
        if ($fresh && $installMigrationsInFresh) {
            $task = "refresh";
        }

        //Запуск проверки бд на актуальность
        if ($key = array_search("migrate:check", $args)) {
            $task = "check";
        }

        $handler = new migrationHandler($needVersion, $task, "cmd", $fileToMigrate);

        $result = $handler->runTasks();
    }

    public static function executeMake($args) {

        $version = intval($args[2]);

        if ($version == 0) {
            die("\nНе указана версия 2-м параметром\n");
        }
        if (!isset($args[3])) {
            die("Не указано имя файла");
        }

        $fileName = $args[3];
        $task = false;
        $params = [];

        foreach ($args as $arg) {
            if (substr($arg, 0, 9) == "--create=") {
                $task = "createMakeTableMigration";
                $params[] = substr($arg, 9);
                break;
            }
            if (substr($arg, 0, 8) == "--alter=") {
                $task = "createAlterTableMigration";
                $params[] = substr($arg, 8);
            }
            if (substr($arg, 0, 6) == "--raw=") {
                $task = "createRawSqlMigration";
                $params[] = substr($arg, 6);
            }
        }
        if (!$task) {
            die("Отсутствует один из аргументов: --create=<имя таблицы> || --alter=<имя таблицы>");
        }


        $migration = new \Migrations\createmigration($version, $fileName);

        $result = call_user_func_array([$migration, $task], $params);

        echo colors::colors()->colorize("\n" . 'Создан файл '.$result, 'green') . "\n";
    }

    public static function executeDumpMigrations($args) {
        $version = intval($args[2]);
        if ($version == 0) {
            die("\nНе указана версия 2-м параметром\n");
        }

        echo colors::colors()->colorize("\n" . "Запущено преобразование миграций в дамп sql", 'yellow') . "\n";

        $handler = new migrationHandler($version, "dumpMigrations", "cmd");

        $result = $handler->runTasks();

        echo colors::colors()->colorize("\n" . 'Создан файл с дампом: '.$result, 'green') . "\n";
    }



    public static function getHelp() {
        return [
            '' => 'Справка по модулю миграций.
            ',
            'migrate' => "Выполнить не установленные миграции",
            'migrate:rollback' => 'Откат к 0.sql(т.е версия 3.7.6)',
            'migrate:fresh' => 'Откат к 0.sql и запуск миграций до версии',
            'migrate:check' => 'Проверить бд на актуальность',
            'АРГУМЕНТЫ ПРИ ЗАПУСКЕ' => "(можно использовать все вместе)
                    -v=381 - до определенной версии.
                    --showList - получить список миграций на выполнение
                    --rollback - выполнить down() методы у миграций",
            "\n" => 'Создание миграций',
            "make:migration <версия> <имя_файла> <метод=имя_таблицы>" => " Создает миграцию в папке /db/migrations/<версия>
Примеры:
    make:migration 380 create_test_table --create=test  -  создать таблицу [prefix]test для версии 380
    make:migration 381 alter_test_table --alter=test  -  редактировать таблицу [prefix]test для версии 380
    make:migration 381 insert_data_to_test_table --raw=test  - использовать сырой запрос в таблицу [prefix]test для версии 380
",
            'migrate:dump <версия>' => "Сделать sql дамп из файлов миграций определенной версии. Пример: migrate:dump 380",
        ];

    }


}