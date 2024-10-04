<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1686209135_alter_training_users_completed_table extends Migration {

    public $table = "training_users_completed";

    public function up()
    {
        Schema::table($this->table, "ADD `date` INT(11) NULL DEFAULT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "date");
        //Schema::table($this->table, "");
    }

}