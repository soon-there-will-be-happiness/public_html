<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1679564258_alter_cond_queue_table extends Migration {

    public $table = "cond_queue";

    public function up()
    {
        Schema::table($this->table, "ADD `create_date` INT(11)");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}