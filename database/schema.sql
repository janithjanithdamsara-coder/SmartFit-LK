-- AI Smart Clothing System (SmartFit AI)
-- Database: aicloth_db

CREATE DATABASE IF NOT EXISTS `aicloth_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aicloth_db`;

-- Drop existing tables
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `tryon_logs`;
DROP TABLE IF EXISTS `customer_scans`;
DROP TABLE IF EXISTS `size_charts`;
DROP TABLE IF EXISTS `clothing_sizes`;
DROP TABLE IF EXISTS `clothing_items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Admins Table
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin / admin123 (password_hash using PASSWORD_DEFAULT)
INSERT INTO `admins` (`id`, `username`, `password`, `name`) VALUES
(1, 'admin', '$2y$10$eE0m7a7qVf1Qd7H7wL6vE.vN6fJ.R8iQeT.m1NlC2qA0iL5eCq0mS', 'SmartFit Administrator');

-- 2. Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `gender` ENUM('mens', 'womens', 'unisex') DEFAULT 'unisex',
  `icon` VARCHAR(50) DEFAULT 'fa-tshirt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `slug`, `gender`, `icon`) VALUES
(1, 'Men\'s T-Shirts & Tops', 'mens-t-shirts', 'mens', 'fa-tshirt'),
(2, 'Men\'s Polos & Seamless', 'mens-polos', 'mens', 'fa-vest'),
(3, 'Men\'s Shorts & Bottoms', 'mens-shorts', 'mens', 'fa-running'),
(4, 'Women\'s Leggings & Tights', 'womens-leggings', 'womens', 'fa-female'),
(5, 'Women\'s Athleisure & Joggers', 'womens-joggers', 'womens', 'fa-socks'),
(6, 'Performance Outerwear', 'performance-outerwear', 'unisex', 'fa-hoodie');

-- 3. Clothing Items Table
CREATE TABLE `clothing_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `item_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(100) DEFAULT 'Carnage',
  `gender` ENUM('mens', 'womens', 'unisex') DEFAULT 'mens',
  `color` VARCHAR(50) NOT NULL,
  `color_hex` VARCHAR(20) DEFAULT '#000000',
  `price` DECIMAL(10,2) NOT NULL,
  `compare_price` DECIMAL(10,2) DEFAULT NULL,
  `image_url` TEXT NOT NULL,
  `overlay_image_url` TEXT DEFAULT NULL,
  `description` TEXT,
  `fabric_details` VARCHAR(255) DEFAULT '87% Nylon, 13% Spandex 4-Way Stretch',
  `is_featured` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Items with Incarnage imagery
INSERT INTO `clothing_items` (`id`, `category_id`, `item_code`, `name`, `brand`, `gender`, `color`, `color_hex`, `price`, `compare_price`, `image_url`, `overlay_image_url`, `description`, `fabric_details`, `is_featured`) VALUES
(1, 2, 'CRN-0310-BLK', 'Essential Seamless 1/4 Zip Up Polo', 'Carnage', 'mens', 'Jet Black', '#111111', 5500.00, 6200.00, 
 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8608_8b80d0f0-277e-48e6-a2fd-75a1a40abe23.jpg?v=1785210500', 
 'assets/images/products/polo_black.svg',
 'Tailored, tapered athletic fit designed to accentuate physique. Sleek 1/4 zip closure with minimalist Carnage high-build logo.', '54% Nylon, 46% Polyester Stretch', 1),

(2, 2, 'CRN-0310-GRY', 'Essential Seamless 1/4 Zip Polo', 'Carnage', 'mens', 'Grey Heather', '#55595e', 5500.00, 6200.00, 
 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8641_9897e0a0-58c6-4033-bca0-80633d15fc5f.jpg?v=1785210459', 
 'assets/images/products/polo_grey.svg',
 'Premium lightweight seamless construction engineered for breathability and aesthetic tapered contouring.', '54% Nylon, 46% Polyester Stretch', 1),

(3, 2, 'CRN-0310-WHT', 'Essential Seamless 1/4 Zip Polo', 'Carnage', 'mens', 'Sheer White', '#f4f4f6', 5500.00, 6200.00, 
 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/IMG_8636_67963610-4cad-4ee1-8e86-b8028212a3e7.jpg?v=1785210532', 
 'assets/images/products/polo_white.svg',
 'Ultra-clean white athletic polo with high elasticity, quick-drying moisture control and soft hand-feel.', '54% Nylon, 46% Polyester Stretch', 1),

(4, 6, 'CRN-0650-HOD', 'Carnage Signature Heavyweight Hoodie', 'Carnage', 'unisex', 'Onyx Charcoal', '#1c1e23', 6800.00, 7500.00, 
 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800', 
 'assets/images/products/hoodie_black.svg', 
 'Heavyweight 380 GSM fleece athletic streetwear hoodie with kangaroo pocket, double-lined hood, and structured aesthetic fit.', '80% Combed Cotton, 20% Polyester Heavyweight Fleece', 1),

(5, 4, 'CRN-0219-CRP', 'Revline Seamless Performance Crop Top', 'Carnage', 'womens', 'Jet Black', '#151515', 3800.00, 4500.00, 
 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800', 
 'assets/images/products/crop_top.svg', 
 'Ultra-smooth butter-soft fabric with supportive ribbed underband, racerback contouring, and 4-way compression stretch.', '75% Nylon, 25% Spandex ButterSoft', 1),

(8, 1, 'CRN-0992-OVR', 'Carnage Signature Oversized Tee', 'Carnage', 'mens', 'Charcoal Smoke', '#27272a', 4200.00, 4800.00, 
 'https://cdn.shopify.com/s/files/1/0607/0619/3614/files/DSC08950_839886da-634a-498a-844b-44850e5442fb.jpg?v=1706784942', 
 'assets/images/products/oversized_tee.svg', 
 'Heavyweight 240 GSM drop-shoulder boxy oversized streetwear t-shirt with signature high-density silicone print.', '100% Combed Cotton 240 GSM', 1),

(9, 1, 'CRN-0101-BSC', 'SmartFit Essential Cotton Crew Tee', 'SmartFit', 'mens', 'Navy Indigo', '#1e293b', 1800.00, 2400.00, 
 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800', 
 'assets/images/products/basic_tee.svg', 
 'Super combed 100% bio-wash breathable cotton everyday athletic crew neck t-shirt. Ideal everyday fit at the best price.', '100% Combed Bio-Wash Cotton', 1),

(10, 1, 'CRN-0205-CLS', 'Carnage Core Performance Athletic Tee', 'Carnage', 'mens', 'Athletic Grey', '#475569', 2800.00, 3400.00, 
 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=800', 
 'assets/images/products/polo_grey.svg', 
 'Lightweight moisture-wicking quick dry performance athletic t-shirt for daily training and gym workouts.', '92% Micro-Polyester, 8% Elastane', 1);

-- 4. Clothing Sizes & Stock Quantity Table
CREATE TABLE `clothing_sizes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_id` INT NOT NULL,
  `size_name` VARCHAR(10) NOT NULL,
  `stock_qty` INT DEFAULT 10,
  FOREIGN KEY (`item_id`) REFERENCES `clothing_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed sizes for items
INSERT INTO `clothing_sizes` (`item_id`, `size_name`, `stock_qty`) VALUES
-- Item 1: Jet Black Polo (S, M, L, XL, XXL)
(1, 'S', 12), (1, 'M', 25), (1, 'L', 18), (1, 'XL', 8), (1, 'XXL', 4),
-- Item 2: Grey Polo
(2, 'S', 15), (2, 'M', 20), (2, 'L', 14), (2, 'XL', 6), (2, 'XXL', 3),
-- Item 3: White Polo
(3, 'S', 10), (3, 'M', 18), (3, 'L', 15), (3, 'XL', 5), (3, 'XXL', 2),
-- Item 4: Heavyweight Hoodie
(4, 'S', 15), (4, 'M', 25), (4, 'L', 20), (4, 'XL', 12), (4, 'XXL', 6),
-- Item 5: Women Crop Top (XS, S, M, L, XL)
(5, 'XS', 10), (5, 'S', 22), (5, 'M', 28), (5, 'L', 14), (5, 'XL', 6),
-- Item 8: Oversized Tee
(8, 'S', 20), (8, 'M', 35), (8, 'L', 30), (8, 'XL', 15), (8, 'XXL', 8),
-- Item 9: Basic Crew Tee
(9, 'S', 25), (9, 'M', 45), (9, 'L', 35), (9, 'XL', 20), (9, 'XXL', 10),
-- Item 10: Core Athletic Tee
(10, 'S', 18), (10, 'M', 30), (10, 'L', 22), (10, 'XL', 12), (10, 'XXL', 5);

-- 5. Precision Size Charts Table (Mens & Ladies Measurements in CM)
CREATE TABLE `size_charts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gender` ENUM('mens', 'womens', 'unisex') NOT NULL,
  `category_type` VARCHAR(50) DEFAULT 'tops',
  `size_name` VARCHAR(10) NOT NULL,
  `min_shoulder_cm` DECIMAL(5,1) NOT NULL,
  `max_shoulder_cm` DECIMAL(5,1) NOT NULL,
  `min_chest_cm` DECIMAL(5,1) NOT NULL,
  `max_chest_cm` DECIMAL(5,1) NOT NULL,
  `min_waist_cm` DECIMAL(5,1) NOT NULL,
  `max_waist_cm` DECIMAL(5,1) NOT NULL,
  `min_height_cm` DECIMAL(5,1) NOT NULL,
  `max_height_cm` DECIMAL(5,1) NOT NULL,
  `fit_description` VARCHAR(255) DEFAULT 'Standard Fit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Standard Incarnage / Sri Lankan Body Size Chart Metrics
INSERT INTO `size_charts` (`gender`, `category_type`, `size_name`, `min_shoulder_cm`, `max_shoulder_cm`, `min_chest_cm`, `max_chest_cm`, `min_waist_cm`, `max_waist_cm`, `min_height_cm`, `max_height_cm`, `fit_description`) VALUES
-- MEN'S TOPS / POLOS / TEES
('mens', 'tops', 'S', 39.0, 42.5, 88.0, 95.0, 72.0, 78.0, 160.0, 172.0, 'Men Small (Chest: 35-37", Shoulder: 40-42cm)'),
('mens', 'tops', 'M', 42.6, 45.5, 95.1, 102.0, 78.1, 84.0, 168.0, 178.0, 'Men Medium (Chest: 38-40", Shoulder: 43-45cm)'),
('mens', 'tops', 'L', 45.6, 48.5, 102.1, 109.0, 84.1, 91.0, 174.0, 184.0, 'Men Large (Chest: 40-43", Shoulder: 46-48cm)'),
('mens', 'tops', 'XL', 48.6, 52.0, 109.1, 117.0, 91.1, 99.0, 178.0, 190.0, 'Men Extra Large (Chest: 43-46", Shoulder: 49-51cm)'),
('mens', 'tops', 'XXL', 52.1, 56.0, 117.1, 126.0, 99.1, 108.0, 180.0, 196.0, 'Men Double XL (Chest: 46-49", Shoulder: 52-55cm)'),

-- WOMEN'S TOPS / ATHLEISURE / LEGGINGS
('womens', 'tops', 'XS', 34.0, 37.0, 76.0, 82.0, 58.0, 64.0, 148.0, 158.0, 'Women XS (Bust: 30-32", Shoulder: 35-37cm)'),
('womens', 'tops', 'S', 37.1, 39.5, 82.1, 88.0, 64.1, 70.0, 154.0, 165.0, 'Women Small (Bust: 32-34", Shoulder: 37-39cm)'),
('womens', 'tops', 'M', 39.6, 42.0, 88.1, 95.0, 70.1, 77.0, 160.0, 172.0, 'Women Medium (Bust: 35-37", Shoulder: 40-42cm)'),
('womens', 'tops', 'L', 42.1, 45.0, 95.1, 103.0, 77.1, 85.0, 165.0, 178.0, 'Women Large (Bust: 37-40", Shoulder: 42-44cm)'),
('womens', 'tops', 'XL', 45.1, 48.0, 103.1, 112.0, 85.1, 94.0, 168.0, 182.0, 'Women XL (Bust: 40-44", Shoulder: 45-47cm)');

-- 6. Customer Scans History Table
CREATE TABLE `customer_scans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `gender` ENUM('mens', 'womens') DEFAULT 'mens',
  `measured_shoulder` DECIMAL(5,1) NOT NULL,
  `measured_chest` DECIMAL(5,1) NOT NULL,
  `measured_waist` DECIMAL(5,1) NOT NULL,
  `measured_height` DECIMAL(5,1) NOT NULL,
  `recommended_size` VARCHAR(10) NOT NULL,
  `confidence_score` DECIMAL(4,1) NOT NULL,
  `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Customers Table (Personalized Fit Profiles)
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `gender` ENUM('mens', 'womens', 'unisex') DEFAULT 'mens',
  `saved_height` DECIMAL(5,1) DEFAULT 172.0,
  `saved_shoulder` DECIMAL(5,1) DEFAULT 44.0,
  `saved_chest` DECIMAL(5,1) DEFAULT 98.0,
  `saved_waist` DECIMAL(5,1) DEFAULT 80.0,
  `recommended_size` VARCHAR(10) DEFAULT 'M',
  `fit_preference` ENUM('snug', 'regular', 'oversized') DEFAULT 'regular',
  `body_build` VARCHAR(50) DEFAULT 'Regular Athletic',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Demo Customer (Sandes Thameesha: demo@smartfit.lk / demo123)
INSERT INTO `customers` (`id`, `full_name`, `email`, `password`, `gender`, `saved_height`, `saved_shoulder`, `saved_chest`, `saved_waist`, `recommended_size`, `fit_preference`, `body_build`)
VALUES (1, 'Sandes Thameesha', 'demo@smartfit.lk', '$2y$10$eE0m7a7qVf1Qd7H7wL6vE.vN6fJ.R8iQeT.m1NlC2qA0iL5eCq0mS', 'mens', 174.0, 44.5, 99.0, 81.0, 'M', 'regular', 'Regular Athletic')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- 8. Customer Saved Try-On Looks / Wardrobe
CREATE TABLE IF NOT EXISTS `customer_wardrobe` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `clothing_id` INT NOT NULL,
  `saved_size` VARCHAR(10) NOT NULL,
  `snapshot_url` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`clothing_id`) REFERENCES `clothing_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

