<?php

require_once __DIR__ . '/../includes/helpers.php';

// Preserve language preference before destroying session
$lang = currentLang();

// Clear user session data
unset($_SESSION['user_id']);

// Optionally destroy the entire session (comment out if you want to keep language preference)
// session_destroy();

// Redirect to welcome page (index.php) with language preference
header('Location: ../index.php?lang=' . $lang);
exit;

