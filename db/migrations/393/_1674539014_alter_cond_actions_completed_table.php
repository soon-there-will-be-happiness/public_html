<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1674539014_alter_cond_actions_completed_table extends Migration {

    public $table = "cond_actions_completed";

    public function up()
    {
        Schema::table($this->table, "ADD `condition_id` INT(11) NOT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "condition_id");
        //Schema::table($this->table, "");
    }

}