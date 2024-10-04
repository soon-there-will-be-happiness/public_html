<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1672121454_create_autobackup_task_table extends Migration {

    public $table = "backup_tasks";

    public function up()
    {
        Schema::create($this->table, "
            `id` INT NOT NULL AUTO_INCREMENT, 
            `name` VARCHAR(128) NOT NULL , 
            `desc` VARCHAR(256) NOT NULL ,
            `period` TEXT NOT NULL , 
            `next_action` INT NOT NULL , 
            `bd_enable` TINYINT NOT NULL DEFAULT '1' , 
            `files_enable` TINYINT NOT NULL DEFAULT '1' ,
            `folders_include` TEXT NOT NULL,
            `folders_exclude` TEXT NOT NULL , 
            `files_exclude` TEXT NOT NULL , 
            `send_notif` TEXT NOT NULL , 
            PRIMARY KEY (`id`)
        ", "");
    }

    public function down() {
        Schema::dropIfExists($this->table);
    }

}