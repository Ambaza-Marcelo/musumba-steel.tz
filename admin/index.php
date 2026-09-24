<?php

declare(strict_types=1);

/**
 * Admin entry point — /admin and /admin/ must not 404.
 * Sends visitors to the dashboard (if logged in) or login page.
 */

require_once __DIR__ . '/../includes/helpers.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
