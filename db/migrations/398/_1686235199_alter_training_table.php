<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1686235199_alter_training_table extends Migration {

    public $table = "training";

    public function up()
    {
        Schema::table($this->table, "ADD `params` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "params");
        //Schema::table($this->table, "");
    }

}