<?php

declare(strict_types=1);

/**
 * Global configuration helper for the Musumba Steel site.
 */

const DB_HOST = '127.0.0.1';
const DB_NAME = 'u727805234_musumbasteeltz';
const DB_USER = 'u727805234_musumbasteeltz';
const DB_PASS = 'MusumbaSteel@2025TZ!';

/**
 * Create and return a mysqli connection.
 */
function db(): mysqli
{
    static $connection;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($connection->connect_errno) {
        throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}

/**
 * Basic helper for running prepared statements.
 */
function query(string $sql, array $params = [])
{
    $connection = db();

    if (!$params) {
        return $connection->query($sql);
    }

    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare statement: ' . $connection->error);
    }

    $types = '';
    $values = [];
    foreach ($params as $param) {
        $types .= is_int($param) ? 'i' : 's';
        $values[] = $param;
    }

    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    return $stmt->get_result();
}


