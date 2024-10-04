CREATE TABLE `lgc_flows` ( `flow_id` INT NOT NULL AUTO_INCREMENT , `flow_name` VARCHAR(255) NOT NULL , `flow_title` VARCHAR(255) NOT NULL , `start_flow` INT NOT NULL , `end_flow` INT NOT NULL , `public_start` INT NOT NULL , `public_end` INT NOT NULL , `status` TINYINT NOT NULL , `groups` VARCHAR(255) NULL DEFAULT NULL , `planes` VARCHAR(255) NULL DEFAULT NULL , `letter` TEXT NULL DEFAULT NULL , PRIMARY KEY (`flow_id`)) ENGINE = InnoDB;

CREATE TABLE `lgc_flows_products` ( `id` INT NOT NULL AUTO_INCREMENT , `flow_id` INT NOT NULL , `product_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX `flow_id` (`flow_id`), INDEX `product_id` (`product_id`)) ENGINE = InnoDB;

CREATE TABLE `lgc_flows_maps` ( `map_id` INT NOT NULL AUTO_INCREMENT , `user_id` INT NOT NULL , `flow_id` INT NOT NULL , `status` TINYINT NOT NULL , `start` INT NOT NULL , `end_date` INT NOT NULL , PRIMARY KEY (`map_id`), INDEX `user_id` (`user_id`), INDEX `flow_id` (`flow_id`), INDEX `status` (`status`), INDEX `end_date` (`end_date`)) ENGINE = InnoDB;

ALTER TABLE `lgc_flows` ADD `limit_users` INT NOT NULL DEFAULT '0' AFTER `letter`;
ALTER TABLE `lgc_flows` ADD `del_groups` TEXT NULL DEFAULT NULL AFTER `letter`;

ALTER TABLE `lgc_flows` ADD `show_period` TINYINT NOT NULL DEFAULT '1' COMMENT 'показывать период для потока' AFTER `end_flow`;
ALTER TABLE `lgc_flows_maps` CHANGE `status` `status` TINYINT(4) NOT NULL COMMENT '0 - не обработан, 1 - в процессе, 8 - завершён';
ALTER TABLE `lgc_flows` ADD `is_default` TINYINT NOT NULL DEFAULT '0' AFTER `limit_users`;