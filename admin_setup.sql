-- =============================================
-- Collectify Admin Panel - Database Migration
-- Run this AFTER your existing database.sql
-- =============================================

USE collectify_db;

-- Admin users table (separate from regular users)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_id VARCHAR(50) NOT NULL UNIQUE,  -- used by cart/orders (e.g. "hw_001")
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Default admin account (username: admin, password: Admin@123)
-- Change this immediately after first login!
INSERT INTO admin_users (username, password_hash, email)
VALUES ('admin', '$2y$10$Awz8ZeoTC6Ac4ShcZzvgX.QKNx9fWMHdiiN3jRNZjfbhuE0LEk3am', 'admin@collectify.com')
ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), email=VALUES(email);

-- Seed some default categories based on the existing PHP files
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Hot Wheels', 'hot-wheels', 'Die-cast Hot Wheels collectibles', 1),
('LEGO', 'lego', 'LEGO sets and minifigures', 2),
('Mini GT', 'minigt', '1:64 scale Mini GT models', 3)
ON DUPLICATE KEY UPDATE id=id;

-- Seed sample products (matching original hardcoded ones)
INSERT INTO products (category_id, product_id, name, price, stock, sort_order) VALUES
((SELECT id FROM categories WHERE slug='hotwheels'), 'hw_001', 'Hot Wheels RLC Exclusive', 299.00, 10, 1),
((SELECT id FROM categories WHERE slug='hotwheels'), 'hw_002', 'Hot Wheels Super Treasure Hunt', 499.00, 5, 2),
((SELECT id FROM categories WHERE slug='lego'), 'lg_001', 'LEGO Technic Set', 3999.00, 8, 1),
((SELECT id FROM categories WHERE slug='lego'), 'lg_002', 'LEGO Creator Expert', 5499.00, 4, 2),
((SELECT id FROM categories WHERE slug='minigt'), 'mg_001', 'Mini GT Nissan GTR R35', 799.00, 15, 1),
((SELECT id FROM categories WHERE slug='minigt'), 'mg_002', 'Mini GT Toyota Supra A90', 799.00, 12, 2)
ON DUPLICATE KEY UPDATE id=id;
