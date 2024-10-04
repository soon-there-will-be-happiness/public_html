<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1687156685_alter_custom_fields_table extends Migration {

    public $table = "custom_fields";

    public function up()
    {
        Schema::table($this->table, "ADD `order_id` INT(11) NULL DEFAULT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "order_id");
        //Schema::table($this->table, "");
    }

}