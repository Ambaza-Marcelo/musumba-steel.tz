<?php

declare(strict_types=1);

/**
 * Admin AJAX API: create/update product + variants (PDO + CSRF).
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function jsonFail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/../includes/auth.php';
} catch (Throwable $e) {
    jsonFail(500, 'Authentication bootstrap failed.');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jsonFail(405, 'POST required');
}

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);
    if (!is_array($data)) {
        $data = $_POST;
        if (isset($data['variants']) && is_string($data['variants'])) {
            $data['variants'] = json_decode($data['variants'], true) ?: [];
        }
    }

    if (!verifyCsrf($data['csrf_token'] ?? null)) {
        jsonFail(403, 'Invalid CSRF token');
    }

    $action = $data['action'] ?? 'save';

    if ($action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) {
            throw new InvalidArgumentException('Missing product id');
        }
        pdoQuery('DELETE FROM products WHERE id = ?', [$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    $id = (int) ($data['id'] ?? 0);
    $name = trim((string) ($data['name'] ?? ''));
    $profileType = trim((string) ($data['profile_type'] ?? ''));
    $basePrice = (float) ($data['base_price_per_meter'] ?? 0);
    $imageUrl = trim((string) ($data['image_url'] ?? ''));
    $sortOrder = (int) ($data['sort_order'] ?? 0);
    $isActive = !empty($data['is_active']) ? 1 : 0;
    $variants = $data['variants'] ?? [];

    if ($name === '' || $profileType === '') {
        throw new InvalidArgumentException('Name and profile type are required.');
    }
    if ($basePrice < 0) {
        throw new InvalidArgumentException('Base price cannot be negative.');
    }
    if (!is_array($variants) || !$variants) {
        throw new InvalidArgumentException('Add at least one variant (gauge, colour, finish).');
    }

    $cleanVariants = [];
    foreach ($variants as $v) {
        if (!is_array($v)) {
            continue;
        }
        $gauge = trim((string) ($v['gauge'] ?? ''));
        $colorName = trim((string) ($v['color_name'] ?? ''));
        $colorHex = trim((string) ($v['color_hex'] ?? '#888888'));
        $finish = trim((string) ($v['finish'] ?? 'Glossy'));
        $coef = (float) ($v['price_coefficient'] ?? 1);
        if ($gauge === '' || $colorName === '') {
            continue;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $colorHex)) {
            $colorHex = '#888888';
        }
        if ($coef <= 0) {
            $coef = 1;
        }
        $cleanVariants[] = compact('gauge', 'colorName', 'colorHex', 'finish', 'coef');
    }

    if (!$cleanVariants) {
        throw new InvalidArgumentException('No valid variants provided.');
    }

    $pdo = pdo();
    $pdo->beginTransaction();

    if ($id > 0) {
        pdoQuery(
            'UPDATE products SET name=?, profile_type=?, base_price_per_meter=?, image_url=?, sort_order=?, is_active=? WHERE id=?',
            [$name, $profileType, $basePrice, $imageUrl !== '' ? $imageUrl : null, $sortOrder, $isActive, $id]
        );
        pdoQuery('DELETE FROM product_variants WHERE product_id = ?', [$id]);
        $productId = $id;
    } else {
        pdoQuery(
            'INSERT INTO products (name, profile_type, base_price_per_meter, image_url, sort_order, is_active)
             VALUES (?,?,?,?,?,?)',
            [$name, $profileType, $basePrice, $imageUrl !== '' ? $imageUrl : null, $sortOrder, $isActive]
        );
        $productId = (int) $pdo->lastInsertId();
    }

    $ins = $pdo->prepare(
        'INSERT INTO product_variants (product_id, gauge, color_name, color_hex, finish, price_coefficient)
         VALUES (?,?,?,?,?,?)'
    );
    foreach ($cleanVariants as $v) {
        $ins->execute([
            $productId,
            $v['gauge'],
            $v['colorName'],
            $v['colorHex'],
            $v['finish'],
            $v['coef'],
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'id' => $productId,
        'message' => 'Product saved successfully.',
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
