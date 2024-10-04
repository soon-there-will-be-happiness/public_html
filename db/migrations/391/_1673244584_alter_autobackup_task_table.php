<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1673244584_alter_autobackup_task_table extends Migration {

    public $table = "backup_tasks";

    public function up()
    {
        Schema::table($this->table, "ADD `clients_enable` TINYINT NOT NULL DEFAULT '0' AFTER `files_enable`");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "clients_enable");
    }

}