<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1673259576_alter_autobackup_task_table extends Migration {

    public $table = "backup_tasks";

    public function up()
    {
        Schema::table($this->table, "ADD `last_run` INT NULL AFTER `send_notif`");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "last_run");
        //Schema::table($this->table, "");
    }

}