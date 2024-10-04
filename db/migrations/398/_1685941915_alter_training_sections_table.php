<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1685941915_alter_training_sections_table extends Migration {

    public $table = "training_sections";

    public function up()
    {
        Schema::table($this->table, "ADD `is_show_not_access` TINYINT(1) NULL DEFAULT 1");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "is_show_not_access");
        //Schema::table($this->table, "");
    }

}