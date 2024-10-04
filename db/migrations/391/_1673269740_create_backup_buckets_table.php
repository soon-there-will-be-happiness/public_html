<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1673269740_create_backup_buckets_table extends Migration {

    public $table = "backup_buckets";

    public function up()
    {
        Schema::create($this->table, "
            `id` INT NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(256) NOT NULL, 
            `type` INT NOT NULL, 
            `params` TEXT NOT NULL,
            `status` TINYINT NOT NULL DEFAULT '1',
            PRIMARY KEY (`id`)
        ", "");
    }

    public function down() {
        Schema::dropIfExists($this->table);
    }

}