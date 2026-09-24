<?php
require_once __DIR__ . '/../config/config.php';
$db = db();
$db->query("UPDATE services SET description_en = REPLACE(description_en, '???', '–')");
$db->query("UPDATE services SET description_sw = REPLACE(description_sw, '???', '–')");
$db->query("UPDATE services SET description_en = REPLACE(description_en, '�', '-')");
echo 'Encoding cleaned. <a href="../?page=residential-roofing">Back</a>';
