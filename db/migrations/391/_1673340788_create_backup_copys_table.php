<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1673340788_create_backup_copys_table extends Migration {

    public $table = "backup_copys";

    public function up()
    {
        Schema::create($this->table, "
        `id` INT NOT NULL AUTO_INCREMENT, 
        `date` INT NOT NULL, 
        `filename` VARCHAR(256) NOT NULL, 
        `params` TEXT NOT NULL, 
        PRIMARY KEY (`id`)", "");
    }

    public function down() {
        Schema::dropIfExists($this->table);
    }

}