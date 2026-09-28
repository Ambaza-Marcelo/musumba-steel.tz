<?php

declare(strict_types=1);

/**
 * Shared admin bootstrap — auth + schema + safe error handling.
 * Include this instead of auth/helpers/upload separately.
 */

if (!defined('ADMIN_AREA')) {
    define('ADMIN_AREA', true);
}

require_once __DIR__ . '/auth.php';

// Avoid double-loading; helpers already pulled by auth
if (!function_exists('ensureMediaSchema') && is_file(__DIR__ . '/../../includes/upload.php')) {
    require_once __DIR__ . '/../../includes/upload.php';
}
if (!function_exists('ensureConfiguratorSchema') && is_file(__DIR__ . '/../../includes/configurator.php')) {
    require_once __DIR__ . '/../../includes/configurator.php';
}

/**
 * Run schema ensures without killing the page.
 */
function adminEnsureSchemas(): void
{
    try {
        if (function_exists('ensureMediaSchema')) {
            ensureMediaSchema();
        }
    } catch (Throwable $e) {
        error_log('adminEnsureSchemas media: ' . $e->getMessage());
    }
    try {
        if (function_exists('ensureConfiguratorSchema')) {
            ensureConfiguratorSchema();
        }
    } catch (Throwable $e) {
        error_log('adminEnsureSchemas configurator: ' . $e->getMessage());
    }
    try {
        if (function_exists('ensureAdminSchema')) {
            ensureAdminSchema();
        }
    } catch (Throwable $e) {
        error_log('adminEnsureSchemas admin: ' . $e->getMessage());
    }
}

adminEnsureSchemas();

$lang = currentLang();
$adminError = '';

if (!function_exists('adminFetchAll')) {
    /**
     * Safe fetch-all helper for admin lists.
     *
     * @return list<array<string, mixed>>
     */
    function adminFetchAll(string $sql, array $params = []): array
    {
        try {
            $result = query($sql, $params);
            if (!$result || $result === true) {
                return [];
            }
            return $result->fetch_all(MYSQLI_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('adminFetchAll: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('adminFetchOne')) {
    /**
     * Safe fetch-one helper.
     *
     * @return array<string, mixed>|null
     */
    function adminFetchOne(string $sql, array $params = []): ?array
    {
        try {
            $result = query($sql, $params);
            if (!$result || $result === true) {
                return null;
            }
            $row = $result->fetch_assoc();
            return $row ?: null;
        } catch (Throwable $e) {
            error_log('adminFetchOne: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('adminParams')) {
    /**
     * Bind nulls as empty string for Hostinger/MariaDB quirks (optional columns).
     *
     * @param array<int, mixed> $params
     * @return array<int, mixed>
     */
    function adminParams(array $params): array
    {
        $out = [];
        foreach ($params as $p) {
            $out[] = $p === null ? '' : $p;
        }
        return $out;
    }
}
