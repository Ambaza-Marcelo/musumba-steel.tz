-- Musumba Steel — Quote configurator schema
-- Applied automatically via ensureConfiguratorSchema() (PDO)

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  profile_type VARCHAR(120) NOT NULL,
  base_price_per_meter DECIMAL(12,2) NOT NULL DEFAULT 0,
  image_url VARCHAR(500) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_products_profile (profile_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_variants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  gauge VARCHAR(20) NOT NULL,
  color_name VARCHAR(80) NOT NULL,
  color_hex VARCHAR(7) NOT NULL DEFAULT '#888888',
  finish VARCHAR(40) NOT NULL DEFAULT 'Glossy',
  price_coefficient DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_variant_product FOREIGN KEY (product_id)
    REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_variant_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
