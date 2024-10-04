<?php


namespace Migrations\interfaces;


interface migration
{
    /** Метод выполняет действие при миграции */
    public function up();

    /** Метод выполняет действие при откате */
    public function down();

}