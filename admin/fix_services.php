<?php
/**
 * Explicit product category assignment.
 * Open: /musumba_steel/admin/fix_services.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
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

// Duplicate key coated products into coated-steel visibility via second rows? Better: set coated as mirror category list in PHP.
// Also keep coated copies by updating a subset for the coated page only via LIKE on coated page query.
// For coated-steel page, show products matching coated names:
$db->query("UPDATE services SET category = 'coated-steel' WHERE name_en IN (
  'Alu-Zinc Corrugated Sheet (Plain)',
  'Pre-Painted Corrugated Sheet',
  'Musumba Rangi Max / Rangi Max+'
)");
// Wait that removes them from residential. Instead use dual category via getServices aliases.

// Final explicit map — residential keeps sheet products; coated page uses name filter in helper.
$db->query("UPDATE services SET category = 'residential-roofing' WHERE name_en IN (
  'Alu-Zinc Corrugated Sheet (Plain)',
  'Pre-Painted Corrugated Sheet',
  'Musumba Rangi Max / Rangi Max+',
  'Versa Tile'
)");
$db->query("UPDATE services SET category = 'industrial-roofing' WHERE name_en LIKE 'IT4%'");
$db->query("UPDATE services SET category = 'flashings' WHERE name_en LIKE 'Flash%' OR name_en LIKE 'Crimp%'");
$db->query("UPDATE services SET category = 'pipes-tubes' WHERE name_en LIKE '%Pipe%' OR name_en LIKE 'Hollow%' OR name_en LIKE 'MS Plate%' OR name_en LIKE '%Nail%'");

$db->query("DELETE FROM publications WHERE title_en IN ('publication','publication2') OR title_en LIKE 'publication%'");

$r = $db->query('SELECT category, name_en FROM services ORDER BY category, name_en');
echo '<h1>Catalogue OK</h1><ul>';
while ($row = $r->fetch_assoc()) {
    echo '<li>' . htmlspecialchars($row['category'] . ' — ' . $row['name_en']) . '</li>';
}
echo '</ul><p><a href="../?page=residential-roofing">Residential</a> · <a href="../?page=coated-steel">Coated</a> · <a href="../">Home</a></p>';
