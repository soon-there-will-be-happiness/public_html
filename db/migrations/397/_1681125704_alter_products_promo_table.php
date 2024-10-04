<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1681125704_alter_products_promo_table extends Migration {

    public $table = "products_promo";

    public function up()
    {
        Schema::table($this->table, "ADD `count_uses` INT(11) NULL DEFAULT NULL");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}