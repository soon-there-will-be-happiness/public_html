<?php


namespace Migrations\interfaces;


interface migrateHandler
{
    /** Получить список всех миграций */
    function getMigrations($needVersion);

    /** Запуск действия миграции */
    function runMigration($migration, $version, $rollback);

}