<?php


namespace Migrations;


class MigrationsTable {

    /**
     * Создать таблицу выполненных миграций если она не существует
     * @return bool
     * @throws \Exception
     */
    public static function createTableIfNotExists () {
        $db = \Db::getConnection();

        $sql = "CREATE TABLE IF NOT EXISTS `" . PREFICS . "migrations` ( `id` INT NOT NULL AUTO_INCREMENT , `migration` VARCHAR(255) NOT NULL , `version` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;";
        $result = $db->prepare($sql);
        $result = $result->execute();
        if (!$result) {
            throw new \Exception('Не удалось создать таблицу migrations');
        }
        return $result;
    }

    /**
     * Добавить выполненную миграцию в таблицу
     *
     * @param $migrationName
     * @param $version
     *
     * @return bool
     */
    public static function addMigrateRow($migrationName, $version) {
        $db = \Db::getConnection();

        $sql = "INSERT INTO `" . PREFICS . "migrations` (`migration`, `version`) VALUES ('$migrationName', '$version')";
        $result = $db->prepare($sql);

        return $result->execute();
    }

    /**
     * Удалить миграцию из таблицы
     *
     * @param $migrationName
     * @param $version
     *
     * @return bool
     */
    public static function removeMigrateRow($migrationName, $version) {
        $db = \Db::getConnection();

        $sql = "DELETE FROM `" . PREFICS . "migrations` WHERE `migration` = '$migrationName' AND `version` = '$version'";
        $result = $db->prepare($sql);

        return $result->execute();
    }

    /**
     * Получить массив выполенных миграций
     *
     * @return array
     */
    public static function getCompleted() {
        $db = \Db::getConnection();

        $sql = "SELECT * FROM `" . PREFICS . "migrations`";
        $result = $db->query($sql);

        $result = $result->fetchAll(\PDO::FETCH_ASSOC);
        $migrations = [];
        foreach ($result as $migration) {
            $migrations[$migration['version']][] = $migration['migration'];
        }
        return $migrations;
    }

    public static function getCount() {
        $db = \Db::getConnection();
        $sql = "SELECT COUNT(*) as count FROM `" . PREFICS . "migrations`";
        $result = $db->query($sql);

        return $result->fetch(\PDO::FETCH_ASSOC)['count'];
    }


}