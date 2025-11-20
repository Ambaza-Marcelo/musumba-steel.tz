<?php

require_once __DIR__ . '/helpers.php';

$page = $_GET['page'] ?? 'welcome';
$navItems = navigation();
$lang = currentLang();
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Musumba Steel | <?= htmlspecialchars(pageHeading($page)); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="top-bar">
        <div class="container">
            <div class="brand">
                <img src="assets/img/logo.jpg" alt="Musumba Steel logo" class="logo">
                <div>
                    <strong>Musumba Steel</strong>
                    <span><?= t('brand.tagline'); ?></span>
                </div>
            </div>
            <div class="header-actions">
                <div class="contact-tags">
                    <a href="tel:+255766280903">+255 766 280 903</a>
                    <a href="tel:+255741497470">+255 741 497 470</a>
                </div>
                <div class="language-switch">
                    <a class="<?= $lang === 'en' ? 'active' : ''; ?>" href="?lang=en<?= $page ? '&page=' . urlencode($page) : ''; ?>">English</a>
                    <a class="<?= $lang === 'sw' ? 'active' : ''; ?>" href="?lang=sw<?= $page ? '&page=' . urlencode($page) : ''; ?>">Kiswahili</a>
                </div>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="container">
            <button class="nav-toggle" id="navToggle">☰</button>
            <ul id="navMenu">
                <?php foreach ($navItems as $item): ?>
                    <li class="<?= !empty($item['children']) ? 'has-children' : ''; ?> <?= $page === $item['slug'] ? 'active' : ''; ?>">
                        <a href="?page=<?= $item['slug']; ?>"><?= t($item['label']); ?></a>
                        <?php if (!empty($item['children'])): ?>
                            <ul class="dropdown">
                                <?php foreach ($item['children'] as $child): ?>
                                    <li class="<?= $page === $child['slug'] ? 'active' : ''; ?>">
                                        <a href="?page=<?= $child['slug']; ?>"><?= t($child['label']); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="admin/login.php" class="nav-login-btn"><?= t('nav.login'); ?></a>
        </div>
    </nav>

    <section class="hero">
        <!-- Construction Illustrations - Houses Under Construction -->
        <div class="construction-illustrations">
            <!-- House 1 - With Scaffolding -->
            <svg class="construction-icon construction-house-1" width="120" height="100" viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="houseGrad1" x1="60" y1="0" x2="60" y2="100">
                        <stop offset="0%" stop-color="#f4b000" stop-opacity="0.8"/>
                        <stop offset="100%" stop-color="#c98900" stop-opacity="0.6"/>
                    </linearGradient>
                </defs>
                <!-- Scaffolding -->
                <line x1="10" y1="70" x2="10" y2="20" stroke="#f4b000" stroke-width="2" opacity="0.6"/>
                <line x1="110" y1="70" x2="110" y2="20" stroke="#f4b000" stroke-width="2" opacity="0.6"/>
                <line x1="10" y1="35" x2="110" y2="35" stroke="#f4b000" stroke-width="2" opacity="0.5"/>
                <line x1="10" y1="50" x2="110" y2="50" stroke="#f4b000" stroke-width="2" opacity="0.5"/>
                <!-- House frame -->
                <rect x="25" y="40" width="70" height="50" fill="none" stroke="#f4b000" stroke-width="3"/>
                <!-- Roof frame -->
                <polygon points="25,40 60,15 95,40" fill="none" stroke="#f4b000" stroke-width="3"/>
                <!-- Door opening -->
                <rect x="50" y="70" width="20" height="20" fill="none" stroke="#f4b000" stroke-width="2" opacity="0.7"/>
            </svg>
            
            <!-- House 2 - Partial Roof -->
            <svg class="construction-icon construction-house-2" width="100" height="90" viewBox="0 0 100 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="houseGrad2" x1="50" y1="0" x2="50" y2="90">
                        <stop offset="0%" stop-color="#f4b000" stop-opacity="0.7"/>
                        <stop offset="100%" stop-color="#c98900" stop-opacity="0.5"/>
                    </linearGradient>
                </defs>
                <!-- Foundation/base -->
                <rect x="20" y="75" width="60" height="15" fill="url(#houseGrad2)" stroke="#f4b000" stroke-width="2"/>
                <!-- Walls -->
                <rect x="20" y="45" width="60" height="30" fill="none" stroke="#f4b000" stroke-width="2.5"/>
                <!-- Window openings -->
                <rect x="30" y="50" width="15" height="15" fill="none" stroke="#f4b000" stroke-width="2" opacity="0.7"/>
                <rect x="55" y="50" width="15" height="15" fill="none" stroke="#f4b000" stroke-width="2" opacity="0.7"/>
                <!-- Partial roof (left side only) -->
                <polygon points="20,45 40,25 60,45" fill="none" stroke="#f4b000" stroke-width="3"/>
                <line x1="40" y1="25" x2="80" y2="45" stroke="#f4b000" stroke-width="2" stroke-dasharray="5,3" opacity="0.6"/>
            </svg>
            
            <!-- House 3 - Just Frame -->
            <svg class="construction-icon construction-house-3" width="90" height="85" viewBox="0 0 90 85" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Foundation lines -->
                <line x1="15" y1="75" x2="75" y2="75" stroke="#f4b000" stroke-width="3"/>
                <!-- Corner posts -->
                <line x1="15" y1="75" x2="15" y2="40" stroke="#f4b000" stroke-width="2.5"/>
                <line x1="75" y1="75" x2="75" y2="40" stroke="#f4b000" stroke-width="2.5"/>
                <!-- Top frame -->
                <line x1="15" y1="40" x2="75" y2="40" stroke="#f4b000" stroke-width="2.5"/>
                <!-- Diagonal braces -->
                <line x1="15" y1="75" x2="45" y2="40" stroke="#f4b000" stroke-width="1.5" opacity="0.6"/>
                <line x1="75" y1="75" x2="45" y2="40" stroke="#f4b000" stroke-width="1.5" opacity="0.6"/>
                <!-- Roof peak -->
                <line x1="45" y1="40" x2="45" y2="20" stroke="#f4b000" stroke-width="2" stroke-dasharray="3,2"/>
            </svg>
            
            <!-- House 4 - Almost Complete -->
            <svg class="construction-icon construction-house-4" width="110" height="95" viewBox="0 0 110 95" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="roofPattern" x="0" y="0" width="15" height="10" patternUnits="userSpaceOnUse">
                        <line x1="0" y1="0" x2="15" y2="10" stroke="#f4b000" stroke-width="0.5" opacity="0.4"/>
                    </pattern>
                </defs>
                <!-- Foundation -->
                <rect x="20" y="80" width="70" height="15" fill="#f4b000" opacity="0.3"/>
                <!-- Walls -->
                <rect x="20" y="50" width="70" height="30" fill="none" stroke="#f4b000" stroke-width="3"/>
                <!-- Windows -->
                <rect x="30" y="55" width="18" height="18" fill="none" stroke="#f4b000" stroke-width="2"/>
                <rect x="62" y="55" width="18" height="18" fill="none" stroke="#f4b000" stroke-width="2"/>
                <line x1="39" y1="55" x2="39" y2="73" stroke="#f4b000" stroke-width="1.5"/>
                <line x1="71" y1="55" x2="71" y2="73" stroke="#f4b000" stroke-width="1.5"/>
                <!-- Door -->
                <rect x="48" y="65" width="14" height="15" fill="none" stroke="#f4b000" stroke-width="2"/>
                <!-- Roof -->
                <polygon points="20,50 55,20 90,50" fill="none" stroke="#f4b000" stroke-width="3"/>
                <!-- Roof tiles pattern -->
                <polygon points="20,50 55,20 90,50" fill="url(#roofPattern)" opacity="0.3"/>
                <!-- Missing section (under construction) -->
                <polygon points="55,50 90,50 90,55 55,55" fill="#1d1d1d" opacity="0.8"/>
                <line x1="55" y1="50" x2="55" y2="55" stroke="#f4b000" stroke-width="2" stroke-dasharray="2,2"/>
            </svg>
        
            <!-- House 5 - Early Stage -->
            <svg class="construction-icon construction-house-5" width="85" height="80" viewBox="0 0 85 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Ground level -->
                <line x1="10" y1="70" x2="75" y2="70" stroke="#f4b000" stroke-width="3"/>
                <!-- Corner markers -->
                <circle cx="20" cy="70" r="4" fill="#f4b000"/>
                <circle cx="65" cy="70" r="4" fill="#f4b000"/>
                <!-- Measuring lines -->
                <line x1="20" y1="70" x2="20" y2="50" stroke="#f4b000" stroke-width="2" stroke-dasharray="3,2"/>
                <line x1="65" y1="70" x2="65" y2="50" stroke="#f4b000" stroke-width="2" stroke-dasharray="3,2"/>
                <!-- Future wall lines (dashed) -->
                <line x1="20" y1="50" x2="65" y2="50" stroke="#f4b000" stroke-width="2" stroke-dasharray="4,3" opacity="0.6"/>
                <line x1="20" y1="50" x2="42.5" y2="35" stroke="#f4b000" stroke-width="1.5" stroke-dasharray="3,2" opacity="0.5"/>
                <line x1="65" y1="50" x2="42.5" y2="35" stroke="#f4b000" stroke-width="1.5" stroke-dasharray="3,2" opacity="0.5"/>
                    </svg>
            
            <!-- House 6 - With Crane -->
            <svg class="construction-icon construction-house-6" width="130" height="105" viewBox="0 0 130 105" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Crane base -->
                <rect x="5" y="85" width="12" height="20" fill="#f4b000" opacity="0.4"/>
                <!-- Crane tower -->
                <line x1="11" y1="85" x2="11" y2="25" stroke="#f4b000" stroke-width="3"/>
                <!-- Crane arm -->
                <line x1="11" y1="35" x2="70" y2="20" stroke="#f4b000" stroke-width="2.5"/>
                <!-- Counterweight -->
                <rect x="15" y="30" width="8" height="10" fill="#f4b000" opacity="0.5"/>
                <!-- Hook/cable -->
                <line x1="65" y1="22" x2="85" y2="45" stroke="#f4b000" stroke-width="1.5" stroke-dasharray="2,2"/>
                <!-- House being built -->
                <rect x="75" y="50" width="45" height="35" fill="none" stroke="#f4b000" stroke-width="2.5"/>
                <polygon points="75,50 97.5,30 120,50" fill="none" stroke="#f4b000" stroke-width="2.5"/>
                    </svg>
        </div>
        
        <div class="hero-slider" id="heroSlider">
            <article class="slide active">
                <div class="container">
                    <h1><?= t('nav.welcome'); ?></h1>
                    <p><?= t('brand.tagline'); ?></p>
                    <div class="cta-group">
                        <a href="?page=our-projects" class="btn primary"><?= t('hero.cta'); ?></a>
                        <a href="?page=contact-us" class="btn ghost"><?= t('hero.contact'); ?></a>
                    </div>
                </div>
            </article>
            <article class="slide">
                <div class="container">
                    <h1><?= t('nav.services'); ?></h1>
                    <p>Premium galvanized roofing sheets, rebars and profiles.</p>
                    <div class="cta-group">
                        <a href="?page=roofings" class="btn primary"><?= t('nav.services.roofings'); ?></a>
                    </div>
                </div>
            </article>
            <article class="slide">
                <div class="container">
                    <h1><?= t('nav.projects'); ?></h1>
                    <p>Delivering strategic infrastructure for Tanzania and EAC.</p>
                    <div class="cta-group">
                        <a href="?page=our-projects" class="btn primary"><?= t('nav.projects'); ?></a>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <main class="container main-content">

