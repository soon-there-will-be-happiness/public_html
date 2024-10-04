<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1686898554_alter_products_table extends Migration {

    public $table = "products";

    public function up()
    {
        Schema::table($this->table, "ADD `params` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL");
    }

    public function down() {
        Schema::dropColumnIfExists($this->table, "params");
        //Schema::table($this->table, "");
    }

}