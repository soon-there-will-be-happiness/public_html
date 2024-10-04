<?php


namespace Migrations\interfaces;


interface create
{
    /** Создать файл миграции */
    function createFile();

    /** Получить темплейт файла миграции */
    function getTemplate();
}