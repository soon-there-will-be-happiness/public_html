<?php

use \Migrations\Schema;
use \Migrations\Migration;

class _1678351709_add_sberbank_payments extends Migration {

    public $table = "payments";

    public function up() {
        Schema::rawSql($this->table, "INSERT IGNORE INTO `[TABLE]`(`name`, `title`, `payment_desc`, `status`, `sort`, `params`) VALUES ('sberbank','Сбербанк','<p>Оплата через сбербанк эквайринг.</p>','0','0','')");
    }

    public function down() {
        Schema::rawSql($this->table, "DELETE FROM `[TABLE]` WHERE `name` = 'sberbank'");
    }

}