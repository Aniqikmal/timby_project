-- ============================================================
-- TIMBY PROJECT - LOCAL DATABASE SETUP
-- Run this in phpMyAdmin > SQL tab to set up your local DB
-- ============================================================

CREATE DATABASE IF NOT EXISTS `timbydb` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `timbydb`;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `user_id`   INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email`     VARCHAR(150) NOT NULL UNIQUE,
    `password`  VARCHAR(255) NOT NULL,
    `role`      ENUM('admin','marketing','member') NOT NULL DEFAULT 'member',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample Users (passwords are hashed versions of "Admin@123")
INSERT INTO `users` (`full_name`, `email`, `password`, `role`) VALUES
('Admin User',       'admin@timby.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Marketing Staff',  'marketing@timby.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'marketing'),
('Ahmad Rizal',      'ahmad@email.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Siti Aminah',      'siti@email.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

-- ============================================================
-- TABLE: products
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
    `product_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(150) NOT NULL,
    `description`    TEXT,
    `price`          DECIMAL(10,2) NOT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `category`       VARCHAR(100),
    `image`          VARCHAR(255),
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `products` (`name`, `description`, `price`, `stock_quantity`, `category`, `image`) VALUES
('Wooden Train Set',    'A classic wooden train set with tracks, perfect for toddlers.',       45.00, 20, 'Vehicles',  'train.jpg'),
('Stacking Blocks',     'Colourful eco-friendly stacking blocks made from sustainable wood.',   28.00, 35, 'Blocks',    'blocks.jpg'),
('Wooden Puzzle',       'Educational animal puzzle set, great for developing problem-solving.', 32.00, 15, 'Puzzles',   'puzzle.jpg'),
('Wooden Truck',        'A sturdy push-and-pull wooden truck toy for active kids.',             38.00, 10, 'Vehicles',  'truck.jpg'),
('Wooden Dog Pull Toy', 'A cute pull-along dog toy that wobbles as it moves.',                  25.00,  8, 'Pull Toys', 'dog.png');

-- ============================================================
-- TABLE: transactions
-- ============================================================
CREATE TABLE IF NOT EXISTS `transactions` (
    `trans_id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`          INT NOT NULL,
    `trans_date`       DATE NOT NULL,
    `amount`           DECIMAL(10,2) NOT NULL,
    `status`           ENUM('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    `product_image`    VARCHAR(255),
    `product_name`     VARCHAR(255),
    `delivery_address` VARCHAR(500),
    `total_qty`        INT DEFAULT 1,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample transaction for member "Ahmad Rizal" (user_id = 3)
INSERT INTO `transactions` (`user_id`, `trans_date`, `amount`, `status`, `product_image`, `product_name`, `delivery_address`, `total_qty`) VALUES
(3, CURDATE(), 50.00, 'Pending',   'train.jpg',  'Wooden Train Set',    '12 Jalan Kayu, Kuching, 93000', 1),
(3, CURDATE(), 38.00, 'Delivered', 'truck.jpg',  'Wooden Truck',        '12 Jalan Kayu, Kuching, 93000', 1),
(4, CURDATE(), 61.00, 'Shipped',   'puzzle.jpg', 'Stacking Blocks, Wooden Puzzle', '5 Lorong Merpati, Sibu, 96000', 2);

-- ============================================================
-- TABLE: promotions
-- ============================================================
CREATE TABLE IF NOT EXISTS `promotions` (
    `promo_id`         INT AUTO_INCREMENT PRIMARY KEY,
    `code`             VARCHAR(50) NOT NULL UNIQUE,
    `discount_percent` INT NOT NULL,
    `description`      VARCHAR(255),
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `promotions` (`code`, `discount_percent`, `description`) VALUES
('SAVE10',  10, '10% off your entire order'),
('TIMBY20', 20, '20% off for loyal members');

-- ============================================================
-- TABLE: custom_requests
-- ============================================================
CREATE TABLE IF NOT EXISTS `custom_requests` (
    `request_id`   INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT NOT NULL,
    `description`  TEXT NOT NULL,
    `budget`       DECIMAL(10,2) NOT NULL,
    `status`       ENUM('Pending','Reviewing','Accepted','Rejected','Completed') DEFAULT 'Pending',
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `custom_requests` (`user_id`, `description`, `budget`, `status`) VALUES
(3, 'I would like a wooden rocking horse shaped like a dragon, painted in green.', 150.00, 'Reviewing');

-- ============================================================
-- TABLE: reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS `reviews` (
    `review_id`  INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id`    INT NOT NULL,
    `rating`     TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment`    TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`product_id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`user_id`)    ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`) VALUES
(2, 3, 5, 'My son loves these blocks! Great quality and very safe.');

-- ============================================================
-- TABLE: newsletter
-- ============================================================
CREATE TABLE IF NOT EXISTS `newsletter` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `newsletter` (`email`) VALUES
('subscriber1@email.com'),
('subscriber2@email.com');

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL,
    `message`    VARCHAR(500) NOT NULL,
    `is_read`    TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `notifications` (`user_id`, `message`, `is_read`) VALUES
(3, 'Your order #1 status has been updated to: Pending', 0);

-- ============================================================
-- DONE! Your timbydb is ready.
-- ============================================================
