<?php

declare(strict_types=1);

/**
 * Shared admin navigation — keep every admin page linked.
 *
 * @var string $lang
 * @var string $adminNavActive  e.g. 'users', 'dashboard', 'products'
 */

$lang = $lang ?? currentLang();
$adminNavActive = $adminNavActive ?? '';

$navItems = [
    'dashboard' => ['dashboard.php', 'admin.dashboard'],
    'pages' => ['pages.php', 'admin.pages'],
    'services' => ['services.php', 'admin.services'],
    'products' => ['products.php', 'Quote Products'],
    'homepage' => ['homepage.php', 'Homepage Media'],
    'partners' => ['partners.php', 'Partners'],
    'testimonials' => ['testimonials.php', 'Google Reviews'],
    'projects' => ['projects.php', 'admin.projects'],
    'publications' => ['publications.php', 'admin.publications'],
    'pictures' => ['pictures.php', 'admin.pictures'],
    'videos' => ['videos.php', 'admin.videos'],
    'contacts' => ['contacts.php', 'admin.contacts'],
    'users' => ['users.php', 'admin.users'],
    'backup' => ['backup.php', 'admin.backup'],
    'logout' => ['logout.php', 'admin.logout'],
];
?>
<nav class="admin-nav">
    <?php foreach ($navItems as $key => [$href, $labelKey]): ?>
        <?php
        $label = (strpos($labelKey, 'admin.') === 0) ? t($labelKey) : $labelKey;
        $url = $key === 'logout' || $key === 'backup'
            ? $href
            : $href . '?lang=' . rawurlencode($lang);
        $isActive = $adminNavActive === $key;
        ?>
        <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"<?= $isActive ? ' style="background: var(--primary); color: #111;"' : ''; ?>>
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
        </a>
    <?php endforeach; ?>
</nav>
