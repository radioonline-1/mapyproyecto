CREATE DATABASE IF NOT EXISTS stripe_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stripe_shop;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` VARCHAR(50) UNIQUE NOT NULL,
  `stripe_session_id` VARCHAR(255) DEFAULT NULL,
  `stripe_payment_intent` VARCHAR(255) DEFAULT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `items_json` TEXT NOT NULL,
  `status` ENUM('pending', 'paid', 'cancelled', 'refunded') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_order_id` (`order_id`),
  INDEX `idx_stripe_session_id` (`stripe_session_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;