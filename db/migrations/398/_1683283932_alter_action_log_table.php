<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1683283932_alter_action_log_table extends Migration {

    public $table = "action_log";

    public function up()
    {
        Schema::table($this->table, "CHANGE `data` `data` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}