<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

$lang = currentLang();

unset($_SESSION['user_id'], $_SESSION['login_at'], $_SESSION['csrf_token']);

header('Location: login.php?lang=' . rawurlencode($lang) . '&logged_out=1');
exit;
