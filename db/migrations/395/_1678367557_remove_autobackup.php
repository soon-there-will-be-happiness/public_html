<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1678367557_remove_autobackup extends Migration {

    public $table = "extensions";

    public function up() {
        Schema::rawSql($this->table, "DELETE FROM `[TABLE]` WHERE `name` = 'autobackup'");
    }

    public function down() {
        //Schema::rawSql($this->table, "");
    }

}