<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1677856464_alter_table_telegram_users extends Migration {

    public $table = "telegram_users";

    public function up()
    {
        Schema::table($this->table, "CHANGE `sm_user_id` `sm_user_id` INT(11) NULL DEFAULT '0'");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}