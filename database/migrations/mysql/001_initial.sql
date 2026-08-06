CREATE TABLE IF NOT EXISTS app_meta (
    meta_key VARCHAR(100) PRIMARY KEY,
    meta_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(40) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT '',
    price_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    tebex_package_id VARCHAR(64) NULL,
    metadata TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_products_category_active (category, active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value INT UNSIGNED NOT NULL,
    min_subtotal_cents INT UNSIGNED NOT NULL DEFAULT 0,
    max_uses INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_token VARCHAR(64) NOT NULL UNIQUE,
    minecraft_name VARCHAR(32) NOT NULL,
    minecraft_platform ENUM('java', 'bedrock') NOT NULL,
    subtotal_cents INT UNSIGNED NOT NULL,
    discount_cents INT UNSIGNED NOT NULL DEFAULT 0,
    total_cents INT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'EUR',
    coupon_code VARCHAR(50) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    provider VARCHAR(30) NOT NULL DEFAULT 'local',
    provider_reference VARCHAR(150) NULL,
    provider_checkout_url TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_orders_recipient_created (minecraft_name, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(120) NOT NULL,
    unit_price_cents INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    line_total_cents INT UNSIGNED NOT NULL,
    tebex_package_id VARCHAR(64) NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhook_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(30) NOT NULL,
    event_id VARCHAR(100) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    received_at DATETIME NOT NULL,
    UNIQUE KEY uq_webhook_provider_event (provider, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_site_stats (
    visit_date DATE PRIMARY KEY,
    page_views INT UNSIGNED NOT NULL DEFAULT 0,
    unique_sessions INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

