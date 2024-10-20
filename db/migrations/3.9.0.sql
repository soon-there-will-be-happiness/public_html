ALTER TABLE `#PREFIX#widgets`  ADD `showByGroup` TINYINT(0) NOT NULL DEFAULT '0' COMMENT '0 - показывать выбранным группам 1 - показывать всем, кроме группы' AFTER `width`, ADD `showGroups` TEXT NULL COMMENT 'группы для показа' AFTER `showByGroup`;

ALTER TABLE `#PREFIX#menu_items` ADD `show_in_order_pages` TINYINT NOT NULL DEFAULT '1' AFTER `visible`;

ALTER TABLE `#PREFIX#menu_items` ADD `showByGroup` TINYINT NOT NULL DEFAULT '0' AFTER `show_in_order_pages`,  ADD `showGroups` TEXT NULL AFTER `showByGroup`;

ALTER TABLE `#PREFIX#users` ADD ok_id BIGINT NULL DEFAULT '0' AFTER tg_id, ADD INDEX ok_id (ok_id);


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


DROP TABLE IF EXISTS `[PREFIX]telegram_product`;
CREATE TABLE `[PREFIX]telegram_product` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `telegram` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `[PREFIX]telegram_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;