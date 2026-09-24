<?php

require_once __DIR__ . '/helpers.php';

$page = $_GET['page'] ?? 'welcome';
$navItems = navigation();
$lang = currentLang();
$pageTitle = pageHeading($page);
$crumbs = breadcrumbs($page);
$isHome = ($page === 'welcome');
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Musumba Steel Tanzania Limited — Building Tanzania. Strengthening the Lake Zone.">
    <title>Musumba STEEL (TZ) | <?= htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css?v=20260904t">
</head>

<body class="<?= $isHome ? 'is-home' : 'is-inner'; ?>">
    <header class="site-header is-sticky">
        <div class="container site-header-inner">
            <a class="brand" href="?page=welcome">
                <?php $siteLogo = mediaUrl(getSetting('site_logo')); ?>
                <?php if ($siteLogo): ?>
                    <img src="<?= htmlspecialchars($siteLogo); ?>" alt="Musumba Steel Tanzania Limited" class="logo">
                <?php elseif (mediaExists('assets/img/logo.jpg', false)): ?>
                    <img src="assets/img/logo.jpg" alt="Musumba Steel Tanzania Limited" class="logo">
                <?php else: ?>
                    <span class="logo logo-fallback" aria-hidden="true">MS</span>
                <?php endif; ?>
                <div class="brand-text">
                    <strong>MUSUMBA</strong>
                    <span>STEEL TANZANIA</span>
                </div>
            </a>
            <button class="nav-toggle" id="navToggle" type="button" aria-label="Menu" aria-expanded="false" aria-controls="navMenu">☰</button>
            <nav class="main-nav" aria-label="Main">
                <ul id="navMenu">
                    <?php foreach ($navItems as $item): ?>
                        <li class="<?= !empty($item['children']) ? 'has-children' : ''; ?> <?= isNavActive($page, $item) ? 'active' : ''; ?>">
                            <a href="?page=<?= $item['slug']; ?>"><?= t($item['label']); ?><?= !empty($item['children']) ? ' <span class="caret">▾</span>' : ''; ?></a>
                            <?php if (!empty($item['children'])): ?>
                                <ul class="dropdown<?= count($item['children']) > 5 ? ' dropdown-wide' : ''; ?>">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <?php if (!empty($child['is_heading'])): ?>
                                            <li class="dropdown-heading"><?= t($child['label']); ?></li>
                                        <?php else: ?>
                                            <li class="<?= $page === $child['slug'] ? 'active' : ''; ?>">
                                                <a href="?page=<?= $child['slug']; ?>"><?= t($child['label']); ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <li class="nav-search desk-only">
                        <a href="?page=contact-us" class="search-link" aria-label="Search">
                            <span class="search-icon" aria-hidden="true">⌕</span>
                            <span><?= t('nav.search'); ?></span>
                        </a>
                    </li>
                    <li class="mobile-only"><a href="?lang=<?= $lang === 'en' ? 'sw' : 'en'; ?>&page=<?= urlencode($page); ?>"><?= $lang === 'en' ? 'Kiswahili' : 'English'; ?></a></li>
                    <li class="mobile-only"><a href="admin/login.php"><?= t('nav.login'); ?></a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="talk-bar">
        <div class="container talk-bar-inner">
            <p class="talk-copy">
                <strong><?= t('home.talk_to_us'); ?></strong>
                <?= t('home.talk_please'); ?>
                <a href="tel:+255761037271">+255 761 037 271</a>
                <span class="talk-label">(<?= t('contact.corporate_phone'); ?>)</span>
                <span class="sep">·</span>
                <a href="tel:+255781502260">+255 781 502 260</a>
                <span class="talk-label">(<?= t('contact.factory_phone'); ?>)</span>
            </p>
            <div class="talk-actions">
                <a class="btn-buy" href="?page=deals"><?= t('home.buy_now'); ?></a>
                <a class="btn-warranty" href="?page=warranty"><?= t('home.quality_cta'); ?></a>
                <a class="btn-design" href="?page=roof-designs"><?= t('home.design_cta'); ?></a>
            </div>
        </div>
    </div>

    <?php if ($isHome): ?>
        <?php
        $slides = getHomeSlides();
        if (!$slides) {
            $slides = [[
                'title_en' => 'Building Tanzania',
                'title_sw' => 'Kujenga Tanzania',
                'subtitle_en' => 'Strengthening the Lake Zone',
                'subtitle_sw' => 'Kuimarisha Ukanda wa Ziwa',
                'cta_label_en' => 'Learn More',
                'cta_label_sw' => 'Jifunze Zaidi',
                'cta_url' => '?page=about',
                'image_path' => '',
            ]];
        }
        ?>
        <section class="hero-slider" id="heroSlider" aria-label="Highlights">
            <?php foreach ($slides as $i => $slide): ?>
                <?php $bg = mediaUrl($slide['image_path'] ?? ''); ?>
                <article class="hero-slide<?= $i === 0 ? ' active' : ''; ?><?= $bg ? '' : ' hero-slide-fallback'; ?>"<?= $bg ? ' style="background-image:url(\'' . htmlspecialchars($bg) . '\')"' : ''; ?>>
                    <div class="hero-slide-overlay"></div>
                    <div class="hero-slide-content">
                        <h1><?= htmlspecialchars(localized($slide, 'title')); ?></h1>
                        <p><?= htmlspecialchars(localized($slide, 'subtitle')); ?></p>
                        <?php if (!empty($slide['cta_url'])): ?>
                            <a class="btn hero-cta" href="<?= htmlspecialchars($slide['cta_url']); ?>">
                                <?= htmlspecialchars(localized($slide, 'cta_label') ?: t('home.read_more')); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (count($slides) > 1): ?>
                <button type="button" class="hero-prev" id="heroPrev" aria-label="Previous">‹</button>
                <button type="button" class="hero-next" id="heroNext" aria-label="Next">›</button>
                <div class="hero-dots" id="heroDots"></div>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <?php $pageHeroImg = mediaUrl(getSetting('page_hero_image')); ?>
        <section class="page-hero<?= $pageHeroImg ? ' has-image' : ''; ?>"<?= $pageHeroImg ? ' style="--page-hero-image:url(\'' . htmlspecialchars($pageHeroImg) . '\')"' : ''; ?>>
            <div class="container">
                <h1><?= htmlspecialchars($pageTitle); ?></h1>
                <?php if ($crumbs): ?>
                    <nav class="breadcrumb" aria-label="Breadcrumb">
                        <?php foreach ($crumbs as $i => $crumb): ?>
                            <?php if ($i > 0): ?><span class="sep">/</span><?php endif; ?>
                            <?php if (!empty($crumb['slug'])): ?>
                                <a href="?page=<?= htmlspecialchars($crumb['slug']); ?>"><?= htmlspecialchars($crumb['label']); ?></a>
                            <?php else: ?>
                                <span><?= htmlspecialchars($crumb['label']); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <div class="layout-shell<?= $isHome ? ' home-shell' : ' container'; ?><?= showProductCenter($page) ? ' has-sidebar container' : ''; ?>">
        <?php if (showProductCenter($page)): ?>
            <aside class="product-center">
                <h2><?= t('product_center.title'); ?></h2>
                <ul>
                    <?php foreach (productCenterLinks() as $link): ?>
                        <li class="<?= $page === $link['slug'] ? 'active' : ''; ?>">
                            <a href="?page=<?= $link['slug']; ?>"><?= t($link['label']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="product-center-cta">
                    <h3><?= t('product_center.contact'); ?></h3>
                    <a href="tel:+255761037271">+255 761 037 271</a>
                    <a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a>
                    <a class="btn primary btn-block" href="?page=contact-us"><?= t('product_center.message'); ?></a>
                </div>
            </aside>
        <?php endif; ?>

        <main class="main-content<?= showProductCenter($page) ? ' with-sidebar' : ''; ?><?= $isHome ? ' home-main' : ''; ?>">
