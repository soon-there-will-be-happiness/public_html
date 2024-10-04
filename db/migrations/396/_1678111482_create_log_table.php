<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1678111482_create_log_table extends Migration {

    public $table = "log";

    public function up()
    {
        Schema::create($this->table, "
            `id` INT NOT NULL AUTO_INCREMENT, 
            `message` VARCHAR(256) NOT NULL, 
            `date` VARCHAR(64) NOT NULL , 
            `context` TEXT NULL , 
            `level` TINYINT NOT NULL DEFAULT '0' , 
            `type` VARCHAR(128) NOT NULL , 
            `in_arhive` BOOLEAN NOT NULL DEFAULT FALSE , 
            PRIMARY KEY (`id`)
        ");
    }

    public function down() {
        Schema::dropIfExists($this->table);
    }

}