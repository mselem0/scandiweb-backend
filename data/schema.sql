-- =====================================================
-- CATEGORIES TABLE
-- =====================================================
CREATE TABLE categories(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- PRODUCTS TABLE
-- =====================================================
CREATE TABLE products(
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    in_stock BOOLEAN DEFAULT TRUE,
    brand VARCHAR(255),
    type VARCHAR(50) NOT NULL DEFAULT 'generic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_type ON products(type);

-- =====================================================
-- PRODUCT_GALLERY TABLE
-- =====================================================
CREATE TABLE product_gallery(
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    image_url TEXT NOT NULL,
    image_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_gallery_product ON product_gallery(product_id);

-- =====================================================
-- CURRENCIES TABLE
-- =====================================================
CREATE TABLE currencies(
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency_label VARCHAR(10) NOT NULL,
    currency_symbol VARCHAR(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- PRODUCT_PRICES TABLE
-- =====================================================
CREATE TABLE product_prices(
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    currency_id int NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_prices_product ON product_prices(product_id);
CREATE INDEX idx_prices_currency ON product_prices(currency_id);

-- =====================================================
-- ATTRIBUTES TABLE
-- =====================================================
CREATE TABLE attributes(
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 

-- =====================================================
-- ATTRIBUTE_ITEMS TABLE
-- =====================================================
CREATE TABLE attribute_items(
    id INT AUTO_INCREMENT PRIMARY KEY,
    attribute_id VARCHAR(100) NOT NULL,
    item_id VARCHAR(100) NOT NULL,
    display_value VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_attr_items_attribute ON attribute_items(attribute_id);


-- =====================================================
-- PRODUCT_ATTRIBUTES TABLE
-- =====================================================
CREATE TABLE product_attributes(
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    attribute_id VARCHAR(100) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_attribute (product_id, attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================
-- ORDERS TABLE
-- =====================================================
CREATE TABLE orders(
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency_label VARCHAR(10) NOT NULL DEFAULT 'USD',
    currency_symbol VARCHAR(5) NOT NULL DEFAULT '$',
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =====================================================
-- ORDER_ITEMS TABLE
-- =====================================================
CREATE TABLE order_items(
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id int NOT NULL,
    product_id VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    selected_attributes JSON,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
