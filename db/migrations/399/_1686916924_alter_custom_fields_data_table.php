<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1686916924_alter_custom_fields_data_table extends Migration {

    public $table = "custom_fields_data";

    public function up()
    {
        Schema::table($this->table, "ADD `is_show2order` TINYINT(1) NOT NULL DEFAULT 0");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}