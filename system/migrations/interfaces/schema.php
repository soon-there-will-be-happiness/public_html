<?php


namespace Migrations\interfaces;


interface schema
{
    /** Создать таблицу */
    public static function create(string $tableName, string $columnsSql, string $tableComment = null);//Для использования запросов с CREATE TABLE

    /** Изменить структуру таблицы */
    public static function table($tableName, $sql);//Для использования запросов с ALTER TABLE

    /**
     * Выполнить сырой sql
     * Использовать для вставки/редактирования/удаления записей
     */
    public static function rawSql($tableName, $sql);

    /** Удалить таблицу */
    public static function dropIfExists($table);
}