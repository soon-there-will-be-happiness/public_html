<?php


namespace Migrations;


class Schema implements Interfaces\Schema
{
    static $dumping = false;
    static $dump = [];
    static $migrationName = "";

    /**
     * Создать таблицу в бд
     */
    public static function create(string $tableName, string $columnsSql, string $tableComment = null) {
        if (self::$dumping) {
            return self::sqlDumper("create", [
                "tableName" => $tableName,
                "columnsSql" => $columnsSql,
                "tableComment" => $tableComment
            ]);
        }

        $sql = "CREATE TABLE IF NOT EXISTS `".PREFICS."$tableName` ( $columnsSql ) ENGINE = InnoDB";

        if ($tableComment) {
            $sql .= " COMMENT = '$tableComment'";
        }

        $sql .= ";";

        self::executeSql($sql);
    }

    /**
     * Изменить структуру таблицы
     */
    public static function table($tableName, $sql) {
        if (self::$dumping) {
            return self::sqlDumper("table", [
                "tableName" => $tableName,
                "sql" => $sql,
            ]);
        }
        $sql = "ALTER TABLE `".PREFICS."$tableName` $sql;";
        self::executeSql($sql);
    }

    /**
     * Выполнить сырой sql
     * Использовать для вставки/редактирования/удаления записей
     * @param $sql
     */
    public static function rawSql($tablename, $sql) {
        if (self::$dumping) {
            return self::sqlDumper("rawSql", [
                "tableName" => $tablename,
                "sql" => $sql,
            ]);
        }
        $sql = strtr($sql, [
            "[TABLE]" => PREFICS.$tablename,
        ]);
        self::executeSql($sql);
    }

    /** Удалить таблицу */
    public static function dropIfExists($table)
    {
        $sql = "DROP TABLE IF EXISTS `".PREFICS."$table`";

        self::executeSql($sql);
    }

    /**
     * Удалить колонку в таблице
     * Выбросит исключение если колонка не существует
     *
     * @param $table
     * @param $column
     *
     * @throws \Exception
     */
    public static function dropColumnIfExists($table, $column) {
        $sql = "ALTER TABLE `".PREFICS."$table` DROP `$column`";

        try {
            self::executeSql($sql);
        } catch (\Exception $e) {
            if (!strpos($e->getMessage(), "check that column/key exists")) {
                throw $e;
            } else {
                throw new \Exception("Колонка $column не существует");
            }
        }
    }

    /**
     * Выполнить sql запрос
     * @param $sql
     */
    private static function executeSql($sql) {

        $db = \Db::getConnection();
        $result = $db->prepare($sql);

        $result = $result->execute();
    }

    /**
     * Проверить, существует ли колонка в таблице
     * Если существует - вернет true
     * Если не существует - вернет false
     *
     * @param $table
     * @param $needColumn
     *
     * @return bool
     */
    private static function checkColumn($table, $needColumn) {
        $tableColumns = \Db::getConnection()->query("SHOW COLUMNS FROM `".PREFICS."$table`")->fetchAll(\PDO::FETCH_ASSOC);
        $finded = false;

        foreach ($tableColumns as $column) {
            if ($column['Field'] == $needColumn) {
                $finded = true;
                break;
            }
        }
        return $finded;
    }

    /**
     * Проверить не существует ли колонка в таблице
     * Если не существует - выполнится $callback, либо вернется true
     *
     * @param $table
     * @param $needColumn
     * @param callable|null $callback
     *
     * @return bool
     */
    public static function columnNotExists($table, $needColumn, callable $callback = null) {
        if (self::$dumping) {
            if ($callback) {
                return $callback();
            } else {
                return true;
            }
        }

        $columnExists = self::checkColumn($table, $needColumn);

        if ($columnExists) {
            return false;
        }

        if ($callback) {
            return $callback();
        }

        return true;
    }

    /**
     * Проверить существует ли колонка в таблице
     * Если существует - выполнится $callback, либо вернется true
     *
     * @param $table
     * @param $needColumn
     * @param callable|null $callback
     *
     * @return bool
     */
    public static function columnExists($table, $needColumn, callable $callback = null) {
        if (self::$dumping) {
            if ($callback) {
                return $callback();
            } else {
                return true;
            }
        }

        $columnExists = self::checkColumn($table, $needColumn);


        if (!$columnExists) {
            return false;
        }

        if ($callback) {
            return $callback();
        }

        return true;
    }


    private static function sqlDumper($method, $params) {
        $sql = "";
        switch ($method) {
            case "create":
                $tableName = $params['tableName'];
                $columnsSql = $params['columnsSql'];

                $sql = "CREATE TABLE IF NOT EXISTS `#PREFIX#$tableName` ( $columnsSql ) ENGINE = InnoDB";
                self::$dump[self::$migrationName][] = $sql;
                break;
            case "table":
                $tableName = $params['tableName'];
                $sqlData = $params['sql'];

                $sql = "ALTER TABLE `#PREFIX#$tableName` $sqlData;";
                self::$dump[self::$migrationName][] = $sql;
                break;
            case "rawSql":
                $tableName = $params['tableName'];
                $sqlData = $params['sql'];

                $sql = strtr($sqlData, [
                    "[TABLE]" => "#PREFIX#".$tableName,
                ]);
                self::$dump[self::$migrationName][] = $sql;
                break;
        }

    }

}