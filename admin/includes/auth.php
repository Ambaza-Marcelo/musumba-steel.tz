<?php

declare(strict_types=1);

if (!defined('ADMIN_AREA')) {
    define('ADMIN_AREA', true);
}

require_once __DIR__ . '/../../includes/helpers.php';

/**
 * Harden admin gate + session.
 */
if (empty($_SESSION['user_id'])) {
    $scriptDir = dirname($_SERVER['PHP_SELF'] ?? '/admin');
    // When called from admin/api/*, go up to /admin/login.php
    if (substr($scriptDir, -4) === '/api') {
        $scriptDir = dirname($scriptDir);
    }
    $loginPath = rtrim(str_replace('\\', '/', $scriptDir), '/') . '/login.php';
    header('Location: ' . $loginPath);
    exit;
}

// Idle timeout: 8 hours
$loginAt = (int) ($_SESSION['login_at'] ?? 0);
if ($loginAt > 0 && (time() - $loginAt) > 28800) {
    unset($_SESSION['user_id'], $_SESSION['login_at']);
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['login_at'] = time();

if (!function_exists('currentUser')) {
    function currentUser()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        try {
            $result = query('SELECT id, username, role FROM users WHERE id = ?', [(int) $userId]);
            if ($result) {
                $user = $result->fetch_assoc();
                if ($user) {
                    $r = strtolower(trim((string) ($user['role'] ?? '')));
                    $user['role'] = $r === 'user' ? 'user' : 'admin';
                    return $user;
                }
            }
        } catch (Throwable $e) {
            try {
                $result = query('SELECT id, username FROM users WHERE id = ?', [(int) $userId]);
                if ($result) {
                    $user = $result->fetch_assoc();
                    if ($user) {
                        $user['role'] = 'admin';
                    }
                    return $user;
                }
            } catch (Throwable $e2) {
                error_log('Error fetching user: ' . $e2->getMessage());
            }
        }

        return null;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        $user = currentUser();
        return $user && ($user['role'] ?? 'admin') === 'admin';
    }
}

if (!function_exists('canDelete')) {
    function canDelete()
    {
        return isAdmin();
    }
}

