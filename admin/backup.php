<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/config.php';

if (!isAdmin()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Access denied. Admin role required.';
    exit;
}

/**
 * Build a full SQL dump of the current database via mysqli.
 */
function dumpDatabaseSql(): string
{
    $db = db();
    $dbName = DB_NAME;
    $safeDb = str_replace('`', '``', $dbName);

    $lines = [];
    $lines[] = '-- Musumba Steel database backup';
    $lines[] = '-- Generated: ' . date('Y-m-d H:i:s');
    $lines[] = '-- Database: ' . $dbName;
    $lines[] = '';
    $lines[] = 'SET NAMES utf8mb4;';
    $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
    $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
    $lines[] = '';
    $lines[] = 'CREATE DATABASE IF NOT EXISTS `' . $safeDb . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
    $lines[] = 'USE `' . $safeDb . '`;';
    $lines[] = '';

    $tablesResult = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if (!$tablesResult) {
        throw new RuntimeException('Unable to list tables: ' . $db->error);
    }

    while ($row = $tablesResult->fetch_row()) {
        $table = (string) $row[0];
        $safeTable = str_replace('`', '``', $table);

        $createResult = $db->query('SHOW CREATE TABLE `' . $safeTable . '`');
        if (!$createResult) {
            continue;
        }

        $createRow = $createResult->fetch_assoc();
        $createSql = $createRow['Create Table'] ?? null;
        if (!$createSql) {
            continue;
        }

        $lines[] = '-- --------------------------------------------------------';
        $lines[] = '-- Table structure for `' . $safeTable . '`';
        $lines[] = '-- --------------------------------------------------------';
        $lines[] = '';
        $lines[] = 'DROP TABLE IF EXISTS `' . $safeTable . '`;';
        $lines[] = $createSql . ';';
        $lines[] = '';

        $dataResult = $db->query('SELECT * FROM `' . $safeTable . '`');
        if (!$dataResult || $dataResult->num_rows === 0) {
            $lines[] = '';
            continue;
        }

        $lines[] = '-- Dumping data for table `' . $safeTable . '`';
        $lines[] = '';

        while ($data = $dataResult->fetch_assoc()) {
            $columns = [];
            $values = [];
            foreach ($data as $col => $value) {
                $columns[] = '`' . str_replace('`', '``', (string) $col) . '`';
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $db->real_escape_string((string) $value) . "'";
                }
            }
            $lines[] = 'INSERT INTO `' . $safeTable . '` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ');';
        }

        $lines[] = '';
    }

    $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
    $lines[] = '';
    $lines[] = '-- End of backup';

    return implode("\n", $lines);
}

try {
    $sql = dumpDatabaseSql();
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Backup failed: ' . $e->getMessage();
    exit;
}

$filename = 'musumbasteeltz_backup_' . date('Y-m-d_His') . '.sql';

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . (string) strlen($sql));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

echo $sql;
exit;
