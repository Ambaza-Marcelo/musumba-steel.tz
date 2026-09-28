<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (!isAdmin()) {
    http_response_code(403);
    echo 'Access denied. Admin role required.';
    exit;
}

$db = db();
$db->query("UPDATE services SET description_en = REPLACE(description_en, '???', '–')");
$db->query("UPDATE services SET description_sw = REPLACE(description_sw, '???', '–')");
$db->query("UPDATE services SET description_en = REPLACE(description_en, '�', '-')");
echo 'Encoding cleaned. <a href="services.php">Back to services</a>';
