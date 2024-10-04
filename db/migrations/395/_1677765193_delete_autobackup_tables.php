<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1677765193_delete_autobackup_tables extends Migration {

    public $table = "backup_tasks";

    public function up() {
        Schema::dropIfExists("backup_buckets");
        Schema::dropIfExists("backup_copys");
        Schema::dropIfExists("backup_tasks");
    }

    public function down() {
        //Schema::rawSql($this->table, "");
    }

}