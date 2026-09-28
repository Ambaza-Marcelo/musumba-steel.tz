<?php

declare(strict_types=1);

/**
 * Global configuration — mysqli + PDO with Hostinger-safe result wrapper.
 *
 * Optional override file: config/config.local.php
 *   <?php
 *   define('DB_HOST', 'localhost');
 *   define('DB_NAME', '...');
 *   define('DB_USER', '...');
 *   define('DB_PASS', '...');
 */

if (is_file(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Auto-detect Laragon / local vs production Hostinger
$isLocalDev = (
    strpos(str_replace('\\', '/', __DIR__), '/laragon/') !== false
    || in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)
);

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $isLocalDev ? 'musumbasteeltz' : 'u727805234_aeors');
}
if (!defined('DB_USER')) {
    define('DB_USER', $isLocalDev ? 'root' : 'u727805234_aeors');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $isLocalDev ? '' : 'EdenG@rden2025!');
}

/**
 * Lightweight result object compatible with mysqli_result usage in this project.
 */
class DbResult
{
    /** @var list<array<string, mixed>> */
    private $rows = [];

    /** @var int */
    private $index = 0;

    /** @var int */
    public $num_rows = 0;

    /**
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = array_values($rows);
        $this->num_rows = count($this->rows);
        $this->index = 0;
    }

    public function fetch_assoc(): ?array
    {
        if ($this->index >= $this->num_rows) {
            return null;
        }
        $row = $this->rows[$this->index];
        $this->index++;
        return $row;
    }

    /**
     * @param int|null $mode Ignored — always assoc (MYSQLI_ASSOC compatible)
     * @return list<array<string, mixed>>
     */
    public function fetch_all($mode = null): array
    {
        return $this->rows;
    }

    public function fetch_row(): ?array
    {
        $row = $this->fetch_assoc();
        return $row === null ? null : array_values($row);
    }

    public function free(): void
    {
        $this->rows = [];
        $this->num_rows = 0;
        $this->index = 0;
    }
}

/**
 * Create and return a mysqli connection (legacy CMS / schema ALTERs).
 */
function db(): mysqli
{
    static $connection;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($connection->connect_errno) {
        $altHost = DB_HOST === 'localhost' ? '127.0.0.1' : 'localhost';
        $connection = @new mysqli($altHost, DB_USER, DB_PASS, DB_NAME);
        if ($connection->connect_errno) {
            throw new RuntimeException('Database connection failed: ' . $connection->connect_error);
        }
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}

/**
 * Secure PDO singleton.
 */
function pdo(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $hosts = [DB_HOST];
    if (DB_HOST === 'localhost') {
        $hosts[] = '127.0.0.1';
    } elseif (DB_HOST === '127.0.0.1') {
        $hosts[] = 'localhost';
    }

    $lastError = null;
    foreach ($hosts as $host) {
        try {
            $dsn = 'mysql:host=' . $host . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
            ]);
            return $pdo;
        } catch (Throwable $e) {
            $lastError = $e;
            $pdo = null;
        }
    }

    throw new RuntimeException('PDO connection failed: ' . ($lastError ? $lastError->getMessage() : 'unknown'));
}

/**
 * Run SQL with optional bound params.
 * Uses PDO for prepared statements (works without mysqlnd get_result).
 *
 * @param array<int, mixed> $params
 * @return DbResult|bool
 */
function query(string $sql, array $params = [])
{
    $trimmed = ltrim($sql);

    if (!$params) {
        $connection = db();
        $result = $connection->query($sql);
        if ($result === false) {
            throw new RuntimeException('SQL error: ' . $connection->error);
        }
        if ($result === true) {
            return true;
        }
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        return new DbResult($rows);
    }

    try {
        $stmt = pdo()->prepare($sql);
        $stmt->execute(array_values($params));

        if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $trimmed)) {
            return new DbResult($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        }

        return true;
    } catch (Throwable $e) {
        throw new RuntimeException('Failed to execute statement: ' . $e->getMessage(), 0, $e);
    }
}

/**
 * PDO prepared query helper.
 *
 * @param array<string|int, mixed> $params
 */
function pdoQuery(string $sql, array $params = []): PDOStatement
{
    $stmt = pdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
