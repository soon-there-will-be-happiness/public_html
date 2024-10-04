<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1679654662_alter_cond_actions_completed_table extends Migration {

    public $table = "cond_actions_completed";

    public function up()
    {
        Schema::table($this->table, "ADD `act_params` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}