<?php

declare(strict_types=1);

/**
 * Async price calculation API for the roofing quote configurator.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function jsonFail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require_once __DIR__ . '/includes/helpers.php';
} catch (Throwable $e) {
    error_log('calculer_prix bootstrap: ' . $e->getMessage());
    jsonFail(500, 'Service unavailable.');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonFail(405, 'Method not allowed. Use POST.');
}

try {
    $raw = file_get_contents('php://input');
    $payload = [];
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $payload = $decoded;
        }
    }
    if (!$payload) {
        $payload = $_POST;
    }

    $productId = (int) ($payload['product_id'] ?? 0);
    $variantId = (int) ($payload['variant_id'] ?? 0);
    $length = (float) ($payload['length'] ?? 0);

    if ($productId < 1 || $variantId < 1) {
        jsonFail(422, 'product_id and variant_id are required.');
    }

    if ($length <= 0 || $length > 15) {
        jsonFail(422, 'Length must be greater than 0 and at most 15 metres.');
    }

    $result = calculateQuotePrice($productId, $variantId, $length);
    if (empty($result['ok'])) {
        http_response_code(404);
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('calculer_prix: ' . $e->getMessage());
    jsonFail(500, 'Unable to calculate price. Please try again.');
}
