<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1685969782_fix_connect_bag extends Migration {

    public $table = "telegram_log";

    public function up()
    {
        Schema::table("telegram_log", "CHANGE `user_id` `user_id` BIGINT NULL DEFAULT NULL COMMENT 'ID пользователя в TG'");
        Schema::table("telegram_users", "CHANGE `user_id` `user_id` BIGINT NULL DEFAULT NULL");
    }

    public function down() {
        //Schema::dropColumnIfExists($this->table, "");
        //Schema::table($this->table, "");
    }

}