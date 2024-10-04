<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1674554921_alter_training_test_results_table extends Migration {

    public $table = "training_test_results";

    public function up()
    {
        Schema::table($this->table, "CHANGE `result` `result` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}