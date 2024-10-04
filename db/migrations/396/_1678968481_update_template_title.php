<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1678968481_update_template_title extends Migration {

    public $table = "extensions";

    public function up() {
        Schema::rawSql($this->table, "UPDATE `[TABLE]` SET `title` = 'Стандартный шаблон' WHERE `name` = 'new_simple'");
    }

    public function down() {
        Schema::rawSql($this->table, "UPDATE `[TABLE]` SET `title` = 'New simple' WHERE `name` = 'new_simple'");
    }

}