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

USE th;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Обновляем таблицу бенефициаров
DROP TABLE IF EXISTS `dgq_cyclop_beneficiaries`;
CREATE TABLE `dgq_cyclop_beneficiaries` (
  `id` varchar(255) NOT NULL,
  `user_id` int DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT NULL,
  `legal_type` varchar(255) DEFAULT NULL,
  `is_added_to_ms` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Обновляем таблицу виртуальных счетов
DROP TABLE IF EXISTS `dgq_cyclop_virtual_accounts`;
CREATE TABLE `dgq_cyclop_virtual_accounts` (
  `id` varchar(255) NOT NULL,
  `balance` varchar(255) DEFAULT NULL,
  `beneficiary_id` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT 'стандарт',
  `blocked_cash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Обновляем таблицу сделок
DROP TABLE IF EXISTS `dgq_cyclop_deals`;
CREATE TABLE `dgq_cyclop_deals` (
  `id` varchar(255) NOT NULL,
  `ext_key` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `payer_id` varchar(255) DEFAULT NULL,
  `recipient_id` varchar(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `recipients` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Обновляем таблицу документов
DROP TABLE IF EXISTS `dgq_cyclop_documents`;
CREATE TABLE `dgq_cyclop_documents` (
  `number` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `deal_id` varchar(255) DEFAULT NULL,
  `beneficiary_id` varchar(255) DEFAULT NULL,
  `date` varchar(255) DEFAULT NULL,
  `file_data` BLOB DEFAULT NULL,
  `document_id` varchar(255) DEFAULT NULL,
  `success_added` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица платежей
DROP TABLE IF EXISTS `dgq_cyclop_payments`;
CREATE TABLE `dgq_cyclop_payments` (
  `id` varchar(255) NOT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `virtual_account_id` varchar(255) DEFAULT NULL,
  `deal_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
