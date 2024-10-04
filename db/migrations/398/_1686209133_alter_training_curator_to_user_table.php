<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1686209133_alter_training_curator_to_user_table extends Migration {

    public $table = "training_curator_to_user";

    public function up()
    {
        Schema::table($this->table, "ADD `date` INT(11) NULL DEFAULT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "date");
        //Schema::table($this->table, "");
    }

}