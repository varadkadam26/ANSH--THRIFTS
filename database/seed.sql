-- =====================================================================
-- ANSH THRIFTS — Database Seed & Migration Data
-- Imports authentic product catalog, inventory, admin, settings, and messages
-- Version: 1.0.0
-- Created: 2026-11-16
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. Seed Products (8 authentic thrift items)
-- ---------------------------------------------------------------------
INSERT INTO `products` (`id`, `name`, `slug`, `price`, `category`, `details`, `image_file`, `status`, `created_at`) VALUES
('PRD-1001', 'Washed Black Graphic T-Shirt', 'washed-black-graphic-t-shirt', 399.00, 'T-Shirts', 'Authentic pre-loved washed black graphic t-shirt featuring front artwork with gentle vintage fading. Soft worn-in cotton feel, casual relaxed silhouette. Single unique piece.', 'uploads/1.jpeg', 'Active', NOW()),
('PRD-1002', 'Crimson Red Graphic T-Shirt', 'crimson-red-graphic-t-shirt', 349.00, 'T-Shirts', 'Vibrant crimson red pre-loved crewneck t-shirt with classic center chest graphic print. Comfortable breathable knit with authentic vintage character. 1-of-1 in stock.', 'uploads/2.png', 'Active', NOW()),
('PRD-1003', 'Charcoal Dark Wash Graphic Tee', 'charcoal-dark-wash-graphic-tee', 399.00, 'T-Shirts', 'Curated charcoal dark wash graphic tee with vintage chest motif. Soft circular pre-owned garment in ready-to-wear condition. Single unique piece.', 'uploads/3.jpeg', 'Active', NOW()),
('PRD-1004', 'Heather Grey Crewneck Tee', 'heather-grey-crewneck-tee', 299.00, 'T-Shirts', 'Classic heather grey pre-loved everyday crewneck t-shirt. Clean minimal aesthetic, lightweight breathable cotton blend, standard comfortable fit. 1-of-1 piece.', 'uploads/4.jpeg', 'Active', NOW()),
('PRD-1005', 'Vintage Illustrated Graphic Tee', 'vintage-illustrated-graphic-tee', 449.00, 'T-Shirts', 'Standout thrifted tee featuring distinctive multi-tone graphic illustration on light grey/heather base. Authentic archival street style with vintage patina.', 'uploads/5.jpeg', 'Active', NOW()),
('PRD-1006', 'Vintage Wash Blue Denim Jacket', 'vintage-wash-blue-denim-jacket', 799.00, 'Jackets', 'Classic heavyweight washed blue denim jacket with front button closure, point collar, and dual chest flap pockets. Authentic vintage fade and timeless outerwear styling.', 'uploads/6.jpg', 'Active', NOW()),
('PRD-1007', 'Black Canvas Zip Bomber Jacket', 'black-canvas-zip-bomber-jacket', 899.00, 'Jackets', 'Substantial pre-loved dark charcoal/black canvas zip-up bomber jacket. Ribbed cuffs and hem, structured workwear-inspired silhouette with clean vintage aesthetic. 1-of-1.', 'uploads/7.webp', 'Active', NOW()),
('PRD-1008', 'Distressed Black Streetwear Hoodie', 'distressed-black-streetwear-hoodie', 699.00, 'Hoodies', 'Heavyweight washed black streetwear pullover hoodie featuring kangaroo pouch pocket, drawstring hood, and vintage sun-faded patina. Perfect cozy pre-loved staple.', 'uploads/8.webp', 'Active', NOW())
ON DUPLICATE KEY UPDATE 
  `name` = VALUES(`name`),
  `slug` = VALUES(`slug`),
  `price` = VALUES(`price`),
  `category` = VALUES(`category`),
  `details` = VALUES(`details`),
  `image_file` = VALUES(`image_file`),
  `status` = VALUES(`status`);

-- ---------------------------------------------------------------------
-- 2. Seed Product Images
-- ---------------------------------------------------------------------
INSERT INTO `product_images` (`product_id`, `image_file`, `is_primary`, `sort_order`, `created_at`) VALUES
('PRD-1001', 'uploads/1.jpeg', 1, 0, NOW()),
('PRD-1002', 'uploads/2.png', 1, 0, NOW()),
('PRD-1003', 'uploads/3.jpeg', 1, 0, NOW()),
('PRD-1004', 'uploads/4.jpeg', 1, 0, NOW()),
('PRD-1005', 'uploads/5.jpeg', 1, 0, NOW()),
('PRD-1006', 'uploads/6.jpg', 1, 0, NOW()),
('PRD-1007', 'uploads/7.webp', 1, 0, NOW()),
('PRD-1008', 'uploads/8.webp', 1, 0, NOW())
ON DUPLICATE KEY UPDATE 
  `is_primary` = VALUES(`is_primary`),
  `sort_order` = VALUES(`sort_order`);

-- ---------------------------------------------------------------------
-- 3. Seed Inventory (Unique 1-of-1 thrift pieces)
-- ---------------------------------------------------------------------
INSERT INTO `inventory` (`product_id`, `sku`, `stock_quantity`, `reserved_quantity`, `available_quantity`, `low_stock_alert`, `updated_at`) VALUES
('PRD-1001', 'SKU-1001', 1, 0, 1, 'Yes', NOW()),
('PRD-1002', 'SKU-1002', 1, 0, 1, 'Yes', NOW()),
('PRD-1003', 'SKU-1003', 1, 0, 1, 'Yes', NOW()),
('PRD-1004', 'SKU-1004', 1, 0, 1, 'Yes', NOW()),
('PRD-1005', 'SKU-1005', 1, 0, 1, 'Yes', NOW()),
('PRD-1006', 'SKU-1006', 1, 0, 1, 'Yes', NOW()),
('PRD-1007', 'SKU-1007', 1, 0, 1, 'Yes', NOW()),
('PRD-1008', 'SKU-1008', 1, 0, 1, 'Yes', NOW())
ON DUPLICATE KEY UPDATE
  `sku` = VALUES(`sku`),
  `stock_quantity` = VALUES(`stock_quantity`),
  `available_quantity` = VALUES(`available_quantity`),
  `low_stock_alert` = VALUES(`low_stock_alert`);

-- ---------------------------------------------------------------------
-- 4. Seed Admin User (username: admin, password: admin123)
-- Uses standard password_hash(PASSWORD_BCRYPT)
-- ---------------------------------------------------------------------
INSERT INTO `admins` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$pp9XGrY9KLts0jYn9inEhOm0FsdOIyOUFoles9FbcYm3UldzM7lAC', 'admin', NOW())
ON DUPLICATE KEY UPDATE 
  `password_hash` = VALUES(`password_hash`),
  `role` = VALUES(`role`);

-- ---------------------------------------------------------------------
-- 5. Seed Settings
-- ---------------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`, `updated_at`) VALUES
('store_name', 'ANSH THRIFTS', 'The brand name displayed in header and footer', NOW()),
('tagline', 'Pre-Loved Fashion, New Stories.', 'Store tagline for hero banner', NOW()),
('currency', 'INR (₹)', 'Currency symbol and code', NOW()),
('support_email', 'mitramsolutions@gmail.com', 'Recipient email for contact messages', NOW())
ON DUPLICATE KEY UPDATE 
  `setting_value` = VALUES(`setting_value`),
  `description` = VALUES(`description`);

-- ---------------------------------------------------------------------
-- 6. Seed Sample Contact Message
-- ---------------------------------------------------------------------
INSERT INTO `contact_messages` (`id`, `sender_name`, `email`, `phone`, `subject`, `message`, `status`, `received_at`) VALUES
('MSG-1001', 'Prem', 'prem@example.com', '+91 9876543210', 'Inquiring about Vintage Denim Jacket', 'Hello, is this vintage denim jacket still available? Love the collection!', 'New', NOW())
ON DUPLICATE KEY UPDATE
  `sender_name` = VALUES(`sender_name`),
  `email` = VALUES(`email`);

SET FOREIGN_KEY_CHECKS = 1;
