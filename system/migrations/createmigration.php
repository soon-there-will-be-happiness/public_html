<?php


namespace Migrations;


use cmd\colors;

class createmigration {

    private $time;
    private $version;
    private $fileName;
    private $migrationDir = ROOT."/db/migrations/";

    public function __construct($version, $fileName) {
        $this->time = time();
        $this->version = $version;
        $this->fileName = $fileName;
    }

    private function getTemplate($template) {
        $template = file_get_contents(ROOT."/system/migrations/templates/".$template);
        return $template;
    }

    private function saveFile($version, $fileName, $fileContent) {
        if (!is_dir($this->migrationDir.$version)) {
            mkdir($this->migrationDir.$version);
        }

        if (is_file($this->migrationDir.$version."/".$fileName.".php")) {
            echo colors::colors()->colorize("\nФайл уже существует\n", 'red');
        }

        $path = $this->migrationDir.$version."/".$fileName.".php";
        $result = file_put_contents($path, $fileContent);

        if (!$result) {
            echo colors::colors()->colorize("\n" . 'Ошибка сохранения файла: '.$path, 'red') . "\n";
        }

        return $path;
    }

    public function createMakeTableMigration($table) {
        $tmp = $this->getTemplate("makeTable.stub");

        $migrationName = "_".$this->time."_".$this->fileName;

        $tmp = strtr($tmp, [
            "[MIGRATION_NAME]" => $migrationName,
            "[TABLE-NAME]" => $table
        ]);

        return $this->saveFile($this->version, $migrationName, $tmp);
    }

    public function createAlterTableMigration($table) {
        $tmp = $this->getTemplate("alterTable.stub");
        $migrationName = "_".$this->time."_".$this->fileName;

        $tmp = strtr($tmp, [
            "[MIGRATION_NAME]" => $migrationName,
            "[TABLE-NAME]" => $table
        ]);

        return $this->saveFile($this->version, $migrationName, $tmp);
    }

    public function createRawSqlMigration($table) {
        $tmp = $this->getTemplate("rawSql.stub");
        $migrationName = "_".$this->time."_".$this->fileName;

        $tmp = strtr($tmp, [
            "[MIGRATION_NAME]" => $migrationName,
            "[TABLE-NAME]" => $table
        ]);

        return $this->saveFile($this->version, $migrationName, $tmp);
    }


}