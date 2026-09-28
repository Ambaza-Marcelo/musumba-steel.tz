<?php
/**
 * Explicit product category assignment.
 * Open: /musumba_steel/admin/fix_services.php (admin login required)
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (!isAdmin()) {
    http_response_code(403);
    echo 'Access denied. Admin role required.';
    exit;
}

header('Content-Type: text/html; charset=utf-8');
$db = db();

$exact = [
    'Alu-Zinc Corrugated Sheet (Plain)' => 'residential-roofing',
    'Pre-Painted Corrugated Sheet' => 'residential-roofing',
    'IT4 Box Profile Sheet (Colour)' => 'industrial-roofing',
    'IT4 Box Profile Sheet (Non-Colour)' => 'industrial-roofing',
    'Musumba Rangi Max / Rangi Max+' => 'residential-roofing',
    'Versa Tile' => 'residential-roofing',
    'Crimp / Curve Sheet & Ridges' => 'flashings',
    'Flashing' => 'flashings',
    'Hollow Sections' => 'pipes-tubes',
    'Square Pipes' => 'pipes-tubes',
    'Round Pipes' => 'pipes-tubes',
    'MS Plates' => 'pipes-tubes',
    'Common Mild Steel Nails' => 'pipes-tubes',
];

$stmt = $db->prepare('UPDATE services SET category = ? WHERE name_en = ?');
foreach ($exact as $name => $cat) {
    $stmt->bind_param('ss', $cat, $name);
    $stmt->execute();
}
$stmt->close();

$countRes = $db->query('SELECT category, COUNT(*) c FROM services GROUP BY category');
echo '<h2>Services categories fixed</h2><ul>';
if ($countRes) {
    while ($row = $countRes->fetch_assoc()) {
        echo '<li>' . htmlspecialchars($row['category']) . ': ' . (int) $row['c'] . '</li>';
    }
}
echo '</ul><p><a href="services.php">Back to services</a></p>';
