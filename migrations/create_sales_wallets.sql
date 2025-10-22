-- Migration: create_sales_wallets.sql
-- Run this on your MySQL database

CREATE TABLE IF NOT EXISTS `sales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `buyer_id` INT UNSIGNED NOT NULL,
  `farmer_id` INT UNSIGNED NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL,
  `total_amount` DECIMAL(14,2) NOT NULL,
  `status` ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `mpesa_ref` VARCHAR(128) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `wallets` (
  `farmer_id` INT UNSIGNED NOT NULL PRIMARY KEY,
  `balance` DECIMAL(14,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `farmer_id` INT UNSIGNED NOT NULL,
  `type` ENUM('credit','debit') NOT NULL,
  `amount` DECIMAL(14,2) NOT NULL,
  `source` VARCHAR(64) NULL,
  `reference` VARCHAR(128) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
