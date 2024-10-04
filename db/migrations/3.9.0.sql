ALTER TABLE `#PREFIX#widgets`  ADD `showByGroup` TINYINT(0) NOT NULL DEFAULT '0' COMMENT '0 - показывать выбранным группам 1 - показывать всем, кроме группы' AFTER `width`, ADD `showGroups` TEXT NULL COMMENT 'группы для показа' AFTER `showByGroup`;

ALTER TABLE `#PREFIX#menu_items` ADD `show_in_order_pages` TINYINT NOT NULL DEFAULT '1' AFTER `visible`;

ALTER TABLE `#PREFIX#menu_items` ADD `showByGroup` TINYINT NOT NULL DEFAULT '0' AFTER `show_in_order_pages`,  ADD `showGroups` TEXT NULL AFTER `showByGroup`;

ALTER TABLE `#PREFIX#users` ADD ok_id BIGINT NULL DEFAULT '0' AFTER tg_id, ADD INDEX ok_id (ok_id);

