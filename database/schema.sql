-- ==============================================================================
-- Schema: E-Commerce & Digital Marketing Operations Dashboard
-- Engine: MySQL 8.x / TiDB Cloud Serverless Compatible
-- ==============================================================================

-- Pastikan database dibuat dan dipilih terlebih dahulu
CREATE DATABASE IF NOT EXISTS `ecommerce_ops` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_ops`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `sales_orders`;
DROP TABLE IF EXISTS `marketing_metrics`;
DROP TABLE IF EXISTS `products`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------
-- Table 1: Products & Inventory
-- ------------------------------------------------------------------------------
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `cost_price` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `selling_price` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `stock_physical` INT NOT NULL DEFAULT 0,
  `stock_reserved` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_products_category` (`category`),
  INDEX `idx_products_sku` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 2: Marketing Performance Metrics
-- ------------------------------------------------------------------------------
CREATE TABLE `marketing_metrics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `platform` ENUM('Shopee Ads', 'Meta Ads', 'TikTok Ads') NOT NULL,
  `date` DATE NOT NULL,
  `ad_spend` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `impressions` INT NOT NULL DEFAULT 0,
  `clicks` INT NOT NULL DEFAULT 0,
  `orders` INT NOT NULL DEFAULT 0,
  `revenue` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `ctr` DECIMAL(6, 2) NOT NULL DEFAULT 0.00,
  `roas` DECIMAL(6, 2) NOT NULL DEFAULT 0.00,
  INDEX `idx_metrics_platform_date` (`platform`, `date`),
  INDEX `idx_metrics_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 3: Sales Orders
-- ------------------------------------------------------------------------------
CREATE TABLE `sales_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(64) NOT NULL UNIQUE,
  `channel` ENUM('Shopee', 'TikTok', 'Direct') NOT NULL,
  `customer_name` VARCHAR(150) NOT NULL,
  `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `profit_margin` DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Completed', 'Processing', 'Cancelled', 'Shipped') NOT NULL DEFAULT 'Completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_orders_channel` (`channel`),
  INDEX `idx_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- Table 4: Admins & Authenticated Users
-- ------------------------------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'Super Admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- Seed Data: Products (Inventory SKU Tracker)
-- ==============================================================================
INSERT INTO `products` (`sku`, `name`, `category`, `cost_price`, `selling_price`, `stock_physical`, `stock_reserved`) VALUES
('SKU-ELC-001', 'Wireless Noise-Canceling Earbuds Pro v2', 'Electronics', 145000.00, 289000.00, 150, 24),
('SKU-ELC-002', 'Magnetic Power Bank 10,000mAh Fast Charging', 'Electronics', 110000.00, 219000.00, 42, 38), -- Low Stock
('SKU-FSH-001', 'Oversized Streetwear Boxy Heavyweight Tee', 'Fashion', 55000.00, 135000.00, 280, 45),
('SKU-FSH-002', 'Cargo Parachute Pants Urban Techwear Black', 'Fashion', 95000.00, 225000.00, 85, 12),
('SKU-BEA-001', 'Hyaluronic Acid Hydrating Glow Serum 30ml', 'Beauty & Care', 42000.00, 99000.00, 310, 68),
('SKU-BEA-002', 'Sunscreen Gel SPF 50+ PA++++ Anti-Pollution', 'Beauty & Care', 38000.00, 89000.00, 12, 12), -- Out of Stock (physical - reserved = 0)
('SKU-HOM-001', 'Smart Aroma Diffuser 500ml with Ambient Light', 'Home & Living', 88000.00, 179000.00, 64, 15),
('SKU-HOM-002', 'Ergonomic Memory Foam Lumbar Support Cushion', 'Home & Living', 75000.00, 169000.00, 18, 14), -- Low Stock
('SKU-ELC-003', 'Braided Nylon Type-C to Lightning Fast Cable', 'Electronics', 18000.00, 49000.00, 450, 52),
('SKU-FSH-003', 'Waterproof Utility Crossbody Sling Bag', 'Fashion', 62000.00, 149000.00, 110, 18);

-- ==============================================================================
-- Seed Data: Marketing Metrics (Last 30 Days Across Shopee, Meta, & TikTok Ads)
-- Realistic E-Commerce performance for SEA Market
-- ==============================================================================
INSERT INTO `marketing_metrics` (`platform`, `date`, `ad_spend`, `impressions`, `clicks`, `orders`, `revenue`, `ctr`, `roas`) VALUES
-- Shopee Ads (High intent, high conversion)
('Shopee Ads', '2026-08-19', 1250000.00, 85000, 2980, 84, 6150000.00, 3.51, 4.92),
('Shopee Ads', '2026-08-20', 1320000.00, 92000, 3150, 91, 6740000.00, 3.42, 5.11),
('Shopee Ads', '2026-08-21', 1400000.00, 99000, 3420, 98, 7250000.00, 3.45, 5.18),
('Shopee Ads', '2026-08-22', 1550000.00, 110000, 3850, 112, 8320000.00, 3.50, 5.37),
('Shopee Ads', '2026-08-23', 1500000.00, 106000, 3710, 108, 7950000.00, 3.50, 5.30),
('Shopee Ads', '2026-08-24', 1200000.00, 82000, 2810, 79, 5820000.00, 3.43, 4.85),
('Shopee Ads', '2026-08-25', 1280000.00, 89000, 3050, 86, 6380000.00, 3.43, 4.98),
('Shopee Ads', '2026-08-26', 1350000.00, 94000, 3210, 93, 6910000.00, 3.41, 5.12),
('Shopee Ads', '2026-08-27', 1420000.00, 97000, 3350, 96, 7150000.00, 3.45, 5.04),
('Shopee Ads', '2026-08-28', 1600000.00, 115000, 4010, 115, 8560000.00, 3.49, 5.35),
('Shopee Ads', '2026-08-29', 1580000.00, 112000, 3920, 110, 8240000.00, 3.50, 5.22),
('Shopee Ads', '2026-08-30', 1300000.00, 88000, 3020, 85, 6310000.00, 3.43, 4.85),
('Shopee Ads', '2026-08-31', 1390000.00, 95000, 3290, 94, 7020000.00, 3.46, 5.05),
('Shopee Ads', '2026-09-01', 1480000.00, 102000, 3550, 102, 7620000.00, 3.48, 5.15),
('Shopee Ads', '2026-09-02', 1510000.00, 105000, 3640, 104, 7810000.00, 3.47, 5.17),
('Shopee Ads', '2026-09-03', 1420000.00, 98000, 3380, 97, 7290000.00, 3.45, 5.13),
('Shopee Ads', '2026-09-04', 1650000.00, 120000, 4200, 121, 9150000.00, 3.50, 5.55),
('Shopee Ads', '2026-09-05', 1700000.00, 125000, 4350, 126, 9480000.00, 3.48, 5.58),
('Shopee Ads', '2026-09-06', 1590000.00, 114000, 3960, 112, 8420000.00, 3.47, 5.30),
('Shopee Ads', '2026-09-07', 1410000.00, 96000, 3310, 95, 7120000.00, 3.45, 5.05),
('Shopee Ads', '2026-09-08', 1750000.00, 130000, 4600, 134, 10120000.00, 3.54, 5.78),
('Shopee Ads', '2026-09-09', 2400000.00, 185000, 6800, 195, 14850000.00, 3.68, 6.19), -- 9.9 Mega Campaign
('Shopee Ads', '2026-09-10', 1680000.00, 119000, 4120, 118, 8920000.00, 3.46, 5.31),
('Shopee Ads', '2026-09-11', 1520000.00, 106000, 3680, 105, 7920000.00, 3.47, 5.21),
('Shopee Ads', '2026-09-12', 1590000.00, 111000, 3860, 111, 8360000.00, 3.48, 5.26),
('Shopee Ads', '2026-09-13', 1480000.00, 103000, 3570, 101, 7650000.00, 3.47, 5.17),
('Shopee Ads', '2026-09-14', 1420000.00, 99000, 3420, 98, 7310000.00, 3.45, 5.15),
('Shopee Ads', '2026-09-15', 1450000.00, 101000, 3490, 100, 7520000.00, 3.46, 5.19),
('Shopee Ads', '2026-09-16', 1490000.00, 104000, 3610, 103, 7780000.00, 3.47, 5.22),
('Shopee Ads', '2026-09-17', 1540000.00, 108000, 3750, 107, 8100000.00, 3.47, 5.26),

-- Meta Ads (Catalog Sales & Retargeting)
('Meta Ads', '2026-08-19', 950000.00, 74000, 1680, 44, 3850000.00, 2.27, 4.05),
('Meta Ads', '2026-08-20', 980000.00, 76000, 1720, 46, 4010000.00, 2.26, 4.09),
('Meta Ads', '2026-08-21', 1050000.00, 81000, 1850, 50, 4350000.00, 2.28, 4.14),
('Meta Ads', '2026-08-22', 1150000.00, 88000, 2040, 56, 4920000.00, 2.32, 4.28),
('Meta Ads', '2026-08-23', 1120000.00, 86000, 1980, 54, 4750000.00, 2.30, 4.24),
('Meta Ads', '2026-08-24', 920000.00, 71000, 1610, 42, 3680000.00, 2.27, 4.00),
('Meta Ads', '2026-08-25', 960000.00, 75000, 1710, 45, 3940000.00, 2.28, 4.10),
('Meta Ads', '2026-08-26', 1020000.00, 79000, 1810, 48, 4210000.00, 2.29, 4.13),
('Meta Ads', '2026-08-27', 1080000.00, 83000, 1910, 51, 4520000.00, 2.30, 4.19),
('Meta Ads', '2026-08-28', 1200000.00, 92000, 2150, 58, 5180000.00, 2.34, 4.32),
('Meta Ads', '2026-08-29', 1180000.00, 90000, 2090, 56, 5020000.00, 2.32, 4.25),
('Meta Ads', '2026-08-30', 980000.00, 76000, 1730, 45, 3990000.00, 2.28, 4.07),
('Meta Ads', '2026-08-31', 1040000.00, 80000, 1840, 49, 4310000.00, 2.30, 4.14),
('Meta Ads', '2026-09-01', 1110000.00, 85000, 1960, 53, 4680000.00, 2.31, 4.22),
('Meta Ads', '2026-09-02', 1130000.00, 87000, 2010, 54, 4790000.00, 2.31, 4.24),
('Meta Ads', '2026-09-03', 1060000.00, 82000, 1890, 50, 4450000.00, 2.30, 4.20),
('Meta Ads', '2026-09-04', 1240000.00, 95000, 2220, 61, 5450000.00, 2.34, 4.40),
('Meta Ads', '2026-09-05', 1280000.00, 98000, 2300, 64, 5710000.00, 2.35, 4.46),
('Meta Ads', '2026-09-06', 1190000.00, 91000, 2110, 57, 5100000.00, 2.32, 4.29),
('Meta Ads', '2026-09-07', 1050000.00, 81000, 1860, 49, 4380000.00, 2.30, 4.17),
('Meta Ads', '2026-09-08', 1320000.00, 101000, 2380, 66, 5950000.00, 2.36, 4.51),
('Meta Ads', '2026-09-09', 1850000.00, 142000, 3450, 98, 8820000.00, 2.43, 4.77), -- 9.9 Mega Campaign
('Meta Ads', '2026-09-10', 1260000.00, 97000, 2260, 62, 5520000.00, 2.33, 4.38),
('Meta Ads', '2026-09-11', 1140000.00, 88000, 2030, 54, 4820000.00, 2.31, 4.23),
('Meta Ads', '2026-09-12', 1200000.00, 92000, 2140, 58, 5150000.00, 2.33, 4.29),
('Meta Ads', '2026-09-13', 1120000.00, 86000, 1990, 53, 4740000.00, 2.31, 4.23),
('Meta Ads', '2026-09-14', 1070000.00, 82000, 1890, 50, 4510000.00, 2.30, 4.21),
('Meta Ads', '2026-09-15', 1090000.00, 84000, 1940, 52, 4650000.00, 2.31, 4.27),
('Meta Ads', '2026-09-16', 1120000.00, 86000, 1990, 53, 4780000.00, 2.31, 4.27),
('Meta Ads', '2026-09-17', 1160000.00, 89000, 2070, 56, 5010000.00, 2.33, 4.32),

-- TikTok Ads (High viral traffic & impulse buying)
('TikTok Ads', '2026-08-19', 800000.00, 95000, 2470, 41, 3120000.00, 2.60, 3.90),
('TikTok Ads', '2026-08-20', 840000.00, 99000, 2590, 44, 3350000.00, 2.62, 3.99),
('TikTok Ads', '2026-08-21', 890000.00, 106000, 2790, 48, 3680000.00, 2.63, 4.13),
('TikTok Ads', '2026-08-22', 990000.00, 118000, 3120, 55, 4250000.00, 2.64, 4.29),
('TikTok Ads', '2026-08-23', 960000.00, 114000, 3010, 52, 4050000.00, 2.64, 4.22),
('TikTok Ads', '2026-08-24', 780000.00, 92000, 2390, 39, 2980000.00, 2.60, 3.82),
('TikTok Ads', '2026-08-25', 820000.00, 98000, 2570, 43, 3290000.00, 2.62, 4.01),
('TikTok Ads', '2026-08-26', 870000.00, 103000, 2720, 46, 3550000.00, 2.64, 4.08),
('TikTok Ads', '2026-08-27', 910000.00, 108000, 2860, 49, 3810000.00, 2.65, 4.19),
('TikTok Ads', '2026-08-28', 1030000.00, 122000, 3250, 58, 4480000.00, 2.66, 4.35),
('TikTok Ads', '2026-08-29', 1010000.00, 120000, 3190, 56, 4350000.00, 2.66, 4.31),
('TikTok Ads', '2026-08-30', 830000.00, 98000, 2580, 43, 3320000.00, 2.63, 4.00),
('TikTok Ads', '2026-08-31', 890000.00, 105000, 2780, 47, 3650000.00, 2.65, 4.10),
('TikTok Ads', '2026-09-01', 940000.00, 112000, 2980, 51, 3980000.00, 2.66, 4.23),
('TikTok Ads', '2026-09-02', 960000.00, 114000, 3030, 53, 4120000.00, 2.66, 4.29),
('TikTok Ads', '2026-09-03', 900000.00, 107000, 2840, 48, 3760000.00, 2.65, 4.18),
('TikTok Ads', '2026-09-04', 1060000.00, 126000, 3370, 60, 4690000.00, 2.67, 4.42),
('TikTok Ads', '2026-09-05', 1090000.00, 130000, 3490, 63, 4920000.00, 2.68, 4.51),
('TikTok Ads', '2026-09-06', 1020000.00, 121000, 3230, 56, 4380000.00, 2.67, 4.29),
('TikTok Ads', '2026-09-07', 890000.00, 105000, 2790, 47, 3690000.00, 2.66, 4.15),
('TikTok Ads', '2026-09-08', 1130000.00, 135000, 3650, 66, 5150000.00, 2.70, 4.56),
('TikTok Ads', '2026-09-09', 1580000.00, 189000, 5210, 96, 7550000.00, 2.76, 4.78), -- 9.9 Mega Campaign
('TikTok Ads', '2026-09-10', 1080000.00, 129000, 3450, 61, 4760000.00, 2.67, 4.41),
('TikTok Ads', '2026-09-11', 970000.00, 115000, 3070, 53, 4150000.00, 2.67, 4.28),
('TikTok Ads', '2026-09-12', 1020000.00, 121000, 3240, 57, 4420000.00, 2.68, 4.33),
('TikTok Ads', '2026-09-13', 950000.00, 113000, 3010, 52, 4060000.00, 2.66, 4.27),
('TikTok Ads', '2026-09-14', 910000.00, 108000, 2870, 49, 3850000.00, 2.66, 4.23),
('TikTok Ads', '2026-09-15', 930000.00, 110000, 2930, 50, 3950000.00, 2.66, 4.25),
('TikTok Ads', '2026-09-16', 950000.00, 113000, 3020, 52, 4080000.00, 2.67, 4.29),
('TikTok Ads', '2026-09-17', 990000.00, 117000, 3140, 55, 4290000.00, 2.68, 4.33);

-- ==============================================================================
-- Seed Data: Sample Sales Orders (Representative recent orders)
-- ==============================================================================
INSERT INTO `sales_orders` (`order_number`, `channel`, `customer_name`, `total_amount`, `profit_margin`, `status`, `created_at`) VALUES
('ORD-260917-8801', 'Shopee', 'Budi Santoso', 578000.00, 288000.00, 'Completed', '2026-09-17 14:22:10'),
('ORD-260917-8802', 'TikTok', 'Siti Rahmawati', 354000.00, 174000.00, 'Processing', '2026-09-17 13:45:00'),
('ORD-260917-8803', 'Direct', 'PT Global Niaga Jaya', 2890000.00, 1440000.00, 'Completed', '2026-09-17 11:10:45'),
('ORD-260916-8794', 'Shopee', 'Ahmad Fadillah', 289000.00, 144000.00, 'Shipped', '2026-09-16 18:30:12'),
('ORD-260916-8795', 'TikTok', 'Devi Anggraini', 438000.00, 218000.00, 'Completed', '2026-09-16 16:15:30'),
('ORD-260916-8796', 'Shopee', 'Rizky Pratama', 657000.00, 327000.00, 'Completed', '2026-09-16 12:05:22'),
('ORD-260915-8780', 'Shopee', 'Dewi Lestari', 219000.00, 109000.00, 'Completed', '2026-09-15 19:40:11'),
('ORD-260915-8781', 'TikTok', 'Hendra Gunawan', 178000.00, 89000.00, 'Completed', '2026-09-15 15:20:00'),
('ORD-260914-8772', 'Direct', 'Klinik Estetika Cantika', 1980000.00, 1140000.00, 'Completed', '2026-09-14 10:14:50'),
('ORD-260914-8773', 'Shopee', 'Maya Indah', 448000.00, 224000.00, 'Shipped', '2026-09-14 14:02:18');

-- ==============================================================================
-- Seed Data: Admins (Default: admin@nexuscommerce.com / admin123)
-- ==============================================================================
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('M. Hernan F.', 'admin@nexuscommerce.com', '$2y$10$eNKTMk4oAZWvVsQbLdA1TOn2AdTv9O.el4CzEksGbfJGvQYBJb8qq', 'Super Admin');

