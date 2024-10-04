<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1674623942_update_login extends Migration {

    public $table = "users";

    public function up()
    {
        Schema::table($this->table, "CHANGE `login` `login` VARCHAR(30) NULL DEFAULT NULL");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}