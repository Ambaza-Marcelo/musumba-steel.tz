<?php

declare(strict_types=1);

define('ADMIN_AREA', true);

require_once __DIR__ . '/../../includes/helpers.php';

if (empty($_SESSION['user_id'])) {
    // Redirect to login page - using relative path that works from admin/ directory
    $scriptDir = dirname($_SERVER['PHP_SELF']);
    $loginPath = rtrim($scriptDir, '/') . '/login.php';
    header('Location: ' . $loginPath);
    exit;
}

function currentUser()
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return null;
    }

    try {
        // Try to get user with role column
        $result = query('SELECT id, username, role FROM users WHERE id = ?', [(int) $userId]);
        if ($result) {
            $user = $result->fetch_assoc();
            // If role column doesn't exist, set default to 'admin' for backward compatibility
            if ($user && !isset($user['role'])) {
                $user['role'] = 'admin';
            }
            return $user;
        }
    } catch (Exception $e) {
        // If role column doesn't exist, try without it
        try {
            $result = query('SELECT id, username FROM users WHERE id = ?', [(int) $userId]);
            if ($result) {
                $user = $result->fetch_assoc();
                if ($user) {
                    // Default to admin for existing users without role column
                    $user['role'] = 'admin';
                }
                return $user;
            }
        } catch (Exception $e2) {
            error_log('Error fetching user: ' . $e2->getMessage());
        }
    }

    return null;
}

function isAdmin()
{
    $user = currentUser();
    return $user && ($user['role'] ?? 'admin') === 'admin';
}

function canDelete()
{
    return isAdmin();
}

