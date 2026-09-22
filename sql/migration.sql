-- ==========================================================
-- IndustrialCRM Database Migration Script
-- Compatible with MySQL 5.6+ / MySQL 8.x
-- ==========================================================

USE `industrial_crm`;

-- 1. Modify users password column to accommodate secure password_hash (bcrypt/argon2)
ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NOT NULL;

-- 2. Add indexes for performance and search optimization
ALTER TABLE `quotations` ADD INDEX `idx_quotation_no` (`quotation_no`);
ALTER TABLE `orders` ADD INDEX `idx_order_no` (`order_no`);
ALTER TABLE `orders` ADD INDEX `idx_orders_quotation_no` (`quotation_no`);
ALTER TABLE `invoices` ADD INDEX `idx_invoice_no` (`invoice_no`);
ALTER TABLE `invoices` ADD INDEX `idx_invoices_order_no` (`order_no`);
