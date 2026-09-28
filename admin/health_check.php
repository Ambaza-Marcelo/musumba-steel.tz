<?php
/**
 * Temporary production diagnostics — DELETE after fixing 500 errors.
 * Open while logged out: /admin/health_check.php
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=utf-8');

echo "Musumba Steel health check\n";
echo 'PHP: ' . PHP_VERSION . "\n";
echo 'PDO MySQL: ' . (extension_loaded('pdo_mysql') ? 'yes' : 'NO') . "\n";
echo 'mysqli: ' . (extension_loaded('mysqli') ? 'yes' : 'NO') . "\n";
echo 'mysqlnd: ' . (defined('MYSQLND_VERSION') || function_exists('mysqli_fetch_all') ? 'likely yes' : 'maybe no') . "\n";
echo 'finfo: ' . (class_exists('finfo') ? 'yes' : 'no') . "\n\n";

try {
    require_once __DIR__ . '/../config/config.php';
    echo 'DB_HOST=' . DB_HOST . "\n";
    echo 'DB_NAME=' . DB_NAME . "\n";

    $m = db();
    echo "mysqli: OK\n";

    $p = pdo();
    echo "pdo: OK\n";

    $r = query('SELECT COUNT(*) AS c FROM users');
    $row = $r ? $r->fetch_assoc() : null;
    echo 'users count: ' . (int) ($row['c'] ?? 0) . "\n";

    $r2 = query('SELECT COUNT(*) AS c FROM services');
    $row2 = $r2 ? $r2->fetch_assoc() : null;
    echo 'services count: ' . (int) ($row2['c'] ?? 0) . "\n";

    $r3 = query('SELECT COUNT(*) AS c FROM partners');
    $row3 = $r3 ? $r3->fetch_assoc() : null;
    echo 'partners count: ' . (int) ($row3['c'] ?? 0) . "\n";

    $cols = query("SHOW COLUMNS FROM partners LIKE 'category'");
    $cat = $cols ? $cols->fetch_assoc() : null;
    echo 'partners.category type: ' . (string) ($cat['Type'] ?? 'MISSING') . "\n";

    echo "\nALL CHECKS PASSED — delete this file after debugging.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "FAIL: " . $e->getMessage() . "\n";
}
