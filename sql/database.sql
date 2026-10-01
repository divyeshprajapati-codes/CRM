-- ==========================================================
-- IndustrialCRM Complete Database Schema
-- Compatible with MySQL 5.6+ / MySQL 8.x / MariaDB
-- Default Database: industrial_crm
-- Charset: utf8mb4 / utf8mb4_unicode_ci
-- ==========================================================
-- NOTE FOR SHARED HOSTING (cPanel / phpMyAdmin):
-- 1. Create your database in your hosting control panel.
-- 2. Click on that database name in phpMyAdmin.
-- 3. Go to the "Import" tab and select this file.
-- ==========================================================

-- CREATE DATABASE IF NOT EXISTS `industrial_crm` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `industrial_crm`;


-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin','Manager','Sales') NOT NULL DEFAULT 'Sales',
  `status` ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `customers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) DEFAULT NULL,
  `company` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(150) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `unit` VARCHAR(30) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `quotations`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `quotations` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quotation_no` VARCHAR(20) DEFAULT NULL,
  `customer_name` VARCHAR(100) DEFAULT NULL,
  `product` VARCHAR(150) DEFAULT NULL,
  `quantity` INT(11) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `total` DECIMAL(10,2) DEFAULT NULL,
  `status` VARCHAR(50) DEFAULT 'Pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_quotation_no` (`quotation_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `quotation_no` VARCHAR(20) NOT NULL,
  `order_no` VARCHAR(20) NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `order_status` ENUM('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_order_no` (`order_no`),
  INDEX `idx_orders_quotation_no` (`quotation_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `invoices`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` VARCHAR(20) DEFAULT NULL,
  `order_no` VARCHAR(20) DEFAULT NULL,
  `customer_name` VARCHAR(100) DEFAULT NULL,
  `product_name` VARCHAR(150) DEFAULT NULL,
  `quantity` INT(11) DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `total` DECIMAL(10,2) DEFAULT NULL,
  `payment_status` VARCHAR(50) DEFAULT 'Unpaid',
  `invoice_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_invoice_no` (`invoice_no`),
  INDEX `idx_invoices_order_no` (`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Default Administrator Account (password: admin123)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `status`)
VALUES (1, 'Administrator', 'admin', '$2y$10$eE618Ww1mK2L4GvWz2Z2y.Gq64gCslmS/O3BqVf2hY4e0Zf7aA28e', 'Admin', 'Active')
ON DUPLICATE KEY UPDATE `id`=`id`;
