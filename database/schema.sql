-- Crave Courier Database Schema
-- Advanced structure for food delivery website

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Users table with enhanced fields
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(15),
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Password reset tokens table
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_otp (otp)
);

-- Categories for menu organization
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items with comprehensive details
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    is_vegetarian BOOLEAN DEFAULT FALSE,
    is_vegan BOOLEAN DEFAULT FALSE,
    allergens TEXT, -- JSON array of allergens
    calories INT,
    preparation_time INT, -- in minutes
    is_available BOOLEAN DEFAULT TRUE,
    discount_percentage DECIMAL(5, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- User addresses for delivery
CREATE TABLE user_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    address_type ENUM('home', 'work', 'other') DEFAULT 'home',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    apartment VARCHAR(100),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'India',
    landmark VARCHAR(255),
    delivery_instructions TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Shopping cart for logged-in users
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_item (user_id, menu_item_id)
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    
    -- Customer information
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(15) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    
    -- Delivery address
    delivery_address TEXT NOT NULL,
    delivery_instructions TEXT,
    
    -- Order details
    subtotal DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) DEFAULT 50.00,
    tax_amount DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    total_amount DECIMAL(10, 2) NOT NULL,
    
    -- Payment information
    payment_method ENUM('card', 'upi', 'cod', 'wallet') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_reference VARCHAR(100),
    
    -- Timestamps
    order_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estimated_delivery_time TIMESTAMP,
    actual_delivery_time TIMESTAMP NULL,
    
    -- Additional fields
    special_notes TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items (individual items in each order)
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL, -- Store name at time of order
    item_price DECIMAL(10, 2) NOT NULL, -- Store price at time of order
    quantity INT NOT NULL,
    special_instructions TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
);

-- Insert sample categories
INSERT INTO categories (name, description, display_order) VALUES
('Burgers', 'Delicious handcrafted burgers with fresh ingredients', 1),
('Pizzas', 'Wood-fired pizzas with authentic Italian flavors', 2),
('Shawarmas', 'Middle Eastern wraps with tender meats and fresh vegetables', 3),
('Pasta', 'Authentic Italian pasta dishes', 4),
('Desserts', 'Sweet treats to end your meal perfectly', 5),
('Beverages', 'Refreshing drinks and beverages', 6);

-- Insert sample menu items
INSERT INTO menu_items (category_id, name, description, price, is_vegetarian, calories, preparation_time) VALUES
-- Burgers
(1, 'Classic Burger', 'Juicy beef patty with lettuce, tomato, onion, and special sauce', 300.00, FALSE, 650, 15),
(1, 'Chicken Burger', 'Grilled chicken breast with lettuce, tomato, and mayo', 400.00, FALSE, 580, 12),
(1, 'Veggie Burger', 'Plant-based patty with fresh vegetables and herbs', 350.00, TRUE, 420, 10),
(1, 'BBQ Burger', 'Beef patty with BBQ sauce, bacon, and onion rings', 450.00, FALSE, 780, 18),

-- Pizzas
(2, 'Margherita Pizza', 'Classic pizza with fresh mozzarella, tomatoes, and basil', 400.00, TRUE, 890, 20),
(2, 'Pepperoni Pizza', 'Spicy pepperoni with mozzarella cheese', 450.00, FALSE, 920, 20),
(2, 'Hawaiian Pizza', 'Ham and pineapple with mozzarella cheese', 480.00, FALSE, 850, 22),
(2, 'Veggie Supreme', 'Bell peppers, mushrooms, olives, and onions', 420.00, TRUE, 760, 25),

-- Shawarmas
(3, 'Chicken Shawarma', 'Tender chicken with garlic sauce and vegetables', 250.00, FALSE, 450, 8),
(3, 'Falafel Shawarma', 'Crispy falafel with tahini sauce and fresh vegetables', 220.00, TRUE, 380, 8),
(3, 'Paneer Shawarma', 'Grilled paneer with mint chutney and vegetables', 240.00, TRUE, 420, 10),

-- Pasta
(4, 'Spaghetti Bolognese', 'Classic meat sauce with herbs and parmesan', 350.00, FALSE, 680, 15),
(4, 'Alfredo Pasta', 'Creamy white sauce with chicken and herbs', 380.00, FALSE, 720, 12),
(4, 'Pesto Pasta', 'Fresh basil pesto with cherry tomatoes', 320.00, TRUE, 540, 10),

-- Desserts
(5, 'Tiramisu', 'Classic Italian coffee-flavored dessert', 180.00, TRUE, 320, 5),
(5, 'Chocolate Brownie', 'Rich chocolate brownie with vanilla ice cream', 150.00, TRUE, 420, 3),

-- Beverages
(6, 'Fresh Orange Juice', 'Freshly squeezed orange juice', 80.00, TRUE, 120, 2),
(6, 'Soft Drink', 'Chilled carbonated beverages', 50.00, TRUE, 150, 1),
(6, 'Iced Coffee', 'Cold brew coffee with milk and sugar', 100.00, TRUE, 80, 3);

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_menu_items_category ON menu_items(category_id);
CREATE INDEX idx_menu_items_available ON menu_items(is_available);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_date ON orders(order_time);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_cart_items_user ON cart_items(user_id);

-- Create triggers for automatic order number generation
DELIMITER //
CREATE TRIGGER generate_order_number 
    BEFORE INSERT ON orders 
    FOR EACH ROW 
BEGIN 
    IF NEW.order_number IS NULL OR NEW.order_number = '' THEN
        SET NEW.order_number = CONCAT('CC', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(LAST_INSERT_ID(), 4, '0'));
    END IF;
END//
DELIMITER ;

-- View for order summary
CREATE VIEW order_summary AS
SELECT 
    o.id,
    o.order_number,
    o.customer_name,
    o.status,
    o.total_amount,
    o.order_time,
    o.estimated_delivery_time,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.order_number, o.customer_name, o.status, o.total_amount, o.order_time, o.estimated_delivery_time;

-- View for popular menu items
CREATE VIEW popular_items AS
SELECT 
    mi.id,
    mi.name,
    mi.price,
    mi.image_url,
    COUNT(oi.id) as order_count,
    AVG(o.rating) as avg_rating
FROM menu_items mi
LEFT JOIN order_items oi ON mi.id = oi.menu_item_id
LEFT JOIN orders o ON oi.order_id = o.id
WHERE mi.is_available = TRUE
GROUP BY mi.id, mi.name, mi.price, mi.image_url
ORDER BY order_count DESC;

-- Sample user (password is 'password123' hashed)
INSERT INTO users (username, email, password, first_name, last_name, phone, email_verified) VALUES
('testuser', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'User', '+91-9876543210', TRUE);

-- Sample address for test user
INSERT INTO user_addresses (user_id, first_name, last_name, phone, street_address, city, state, zip_code, is_default) VALUES
(1, 'Test', 'User', '+91-9876543210', '123 Test Street, Test Area', 'Mumbai', 'Maharashtra', '400001', TRUE);
