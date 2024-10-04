<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1682589696_alter_flows_map_table extends Migration {

    public $table = "flows_maps";

    public function up()
    {
        Schema::table($this->table, "ADD `order_item_id` INT(11) NULL DEFAULT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "order_item_id");
        //Schema::table($this->table, "");
    }

}