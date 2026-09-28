<?php

declare(strict_types=1);

/**
 * Quote configurator schema + data helpers (PDO).
 */

function ensureConfiguratorSchema(): void
{
    try {
        $db = pdo();

        $db->exec("CREATE TABLE IF NOT EXISTS products (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("CREATE TABLE IF NOT EXISTS product_variants (
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
            INDEX idx_variant_product (product_id),
            INDEX idx_variant_lookup (product_id, gauge, finish, color_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        seedConfiguratorCatalog();
    } catch (Throwable $e) {
        error_log('ensureConfiguratorSchema: ' . $e->getMessage());
    }
}

/**
 * Seed default Musumba profiles if empty.
 */
function seedConfiguratorCatalog(): void
{
    $count = (int) pdoQuery('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $profiles = [
        [
            'name' => 'Versatile Profile',
            'profile_type' => 'Versatile',
            'base_price_per_meter' => 18500.00,
            'variants' => [
                ['G28', 'Charcoal Grey', '#36454F', 'Matte', 1.00],
                ['G28', 'Brick Red', '#8B2500', 'Glossy', 1.05],
                ['G30', 'Charcoal Grey', '#36454F', 'Matte', 0.92],
                ['G30', 'Ocean Blue', '#1E4D7B', 'Glossy', 0.95],
            ],
        ],
        [
            'name' => 'IT-5 Tekdek',
            'profile_type' => 'IT-5 Tekdek',
            'base_price_per_meter' => 21000.00,
            'variants' => [
                ['G28', 'Forest Green', '#228B22', 'Glossy', 1.00],
                ['G28', 'Tile Red', '#A52A2A', 'Matte', 1.08],
                ['G26', 'Charcoal Grey', '#36454F', 'Matte', 1.18],
            ],
        ],
        [
            'name' => 'Dumuzas Corrugated',
            'profile_type' => 'Dumuzas',
            'base_price_per_meter' => 14500.00,
            'variants' => [
                ['G30', 'Natural Zinc', '#C0C0C0', 'Glossy', 1.00],
                ['G28', 'Natural Zinc', '#C0C0C0', 'Glossy', 1.12],
                ['G28', 'Chocolate Brown', '#5C3317', 'Matte', 1.15],
            ],
        ],
        [
            'name' => 'Box Profile',
            'profile_type' => 'Box Profile',
            'base_price_per_meter' => 19800.00,
            'variants' => [
                ['G28', 'White', '#F5F5F5', 'Glossy', 1.00],
                ['G28', 'Black', '#1A1A1A', 'Matte', 1.06],
                ['G30', 'White', '#F5F5F5', 'Glossy', 0.90],
            ],
        ],
    ];

    $insProduct = pdo()->prepare(
        'INSERT INTO products (name, profile_type, base_price_per_meter, image_url, sort_order)
         VALUES (:name, :profile_type, :base_price, NULL, :sort)'
    );
    $insVariant = pdo()->prepare(
        'INSERT INTO product_variants
            (product_id, gauge, color_name, color_hex, finish, price_coefficient)
         VALUES
            (:product_id, :gauge, :color_name, :color_hex, :finish, :coef)'
    );

    $sort = 1;
    foreach ($profiles as $profile) {
        $insProduct->execute([
            ':name' => $profile['name'],
            ':profile_type' => $profile['profile_type'],
            ':base_price' => $profile['base_price_per_meter'],
            ':sort' => $sort++,
        ]);
        $productId = (int) pdo()->lastInsertId();
        foreach ($profile['variants'] as $v) {
            $insVariant->execute([
                ':product_id' => $productId,
                ':gauge' => $v[0],
                ':color_name' => $v[1],
                ':color_hex' => $v[2],
                ':finish' => $v[3],
                ':coef' => $v[4],
            ]);
        }
    }
}

/**
 * @return list<array<string, mixed>>
 */
function getConfiguratorProducts(): array
{
    try {
        return pdoQuery(
            'SELECT id, name, profile_type, base_price_per_meter, image_url
             FROM products WHERE is_active = 1 ORDER BY sort_order, id'
        )->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * @return list<array<string, mixed>>
 */
function getProductVariants(int $productId): array
{
    try {
        return pdoQuery(
            'SELECT id, product_id, gauge, color_name, color_hex, finish, price_coefficient
             FROM product_variants
             WHERE product_id = ? AND is_active = 1
             ORDER BY gauge, finish, color_name',
            [$productId]
        )->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Full catalog payload for the front configurator (JSON-ready).
 *
 * @return list<array<string, mixed>>
 */
function getConfiguratorCatalog(): array
{
    $products = getConfiguratorProducts();
    foreach ($products as &$product) {
        $product['id'] = (int) $product['id'];
        $product['base_price_per_meter'] = (float) $product['base_price_per_meter'];
        $product['variants'] = getProductVariants((int) $product['id']);
        foreach ($product['variants'] as &$variant) {
            $variant['id'] = (int) $variant['id'];
            $variant['product_id'] = (int) $variant['product_id'];
            $variant['price_coefficient'] = (float) $variant['price_coefficient'];
        }
        unset($variant);
    }
    unset($product);

    return $products;
}

/**
 * Calculate quote: base_price × coefficient × length.
 *
 * @return array{ok:bool, total?:float, currency?:string, breakdown?:array, error?:string}
 */
function calculateQuotePrice(int $productId, int $variantId, float $lengthMeters): array
{
    if ($lengthMeters <= 0 || $lengthMeters > 15) {
        return ['ok' => false, 'error' => 'Length must be between 0.1 and 15 metres.'];
    }

    $stmt = pdo()->prepare(
        'SELECT p.id AS product_id, p.name, p.profile_type, p.base_price_per_meter,
                v.id AS variant_id, v.gauge, v.color_name, v.color_hex, v.finish, v.price_coefficient
         FROM products p
         INNER JOIN product_variants v ON v.product_id = p.id
         WHERE p.id = :product_id AND v.id = :variant_id
           AND p.is_active = 1 AND v.is_active = 1
         LIMIT 1'
    );
    $stmt->execute([
        ':product_id' => $productId,
        ':variant_id' => $variantId,
    ]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['ok' => false, 'error' => 'Product or variant not found.'];
    }

    $base = (float) $row['base_price_per_meter'];
    $coef = (float) $row['price_coefficient'];
    $total = round($base * $coef * $lengthMeters, 2);

    return [
        'ok' => true,
        'currency' => 'TZS',
        'total' => $total,
        'breakdown' => [
            'product' => $row['name'],
            'profile_type' => $row['profile_type'],
            'gauge' => $row['gauge'],
            'finish' => $row['finish'],
            'color_name' => $row['color_name'],
            'color_hex' => $row['color_hex'],
            'base_price_per_meter' => $base,
            'price_coefficient' => $coef,
            'length_meters' => $lengthMeters,
            'formula' => 'base_price × coefficient × length',
        ],
    ];
}
