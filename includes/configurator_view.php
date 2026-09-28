<?php

declare(strict_types=1);

/**
 * Front partial: interactive roofing quote configurator (Mabati-style).
 *
 * @var array $pageData
 */

$catalog = getConfiguratorCatalog();
$catalogJson = json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
?>
<section class="page-content configurator-page">
    <?php if (!empty($pageData)): ?>
        <header class="page-header">
            <h2><?= htmlspecialchars(localized($pageData, 'title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?= htmlspecialchars(localized($pageData, 'summary'), ENT_QUOTES, 'UTF-8'); ?></p>
        </header>
        <?php if (!empty(localized($pageData, 'content'))): ?>
            <article class="page-body"><?= localized($pageData, 'content'); ?></article>
        <?php endif; ?>
    <?php else: ?>
        <header class="page-header">
            <h2><?= htmlspecialchars(t('config.title'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?= htmlspecialchars(t('config.lead'), ENT_QUOTES, 'UTF-8'); ?></p>
        </header>
    <?php endif; ?>

    <div class="configurator" id="quoteConfigurator" data-api="calculer_prix.php">
        <div class="configurator-grid">
            <form class="configurator-form" id="configuratorForm" novalidate>
                <fieldset>
                    <legend><?= htmlspecialchars(t('config.profile'), ENT_QUOTES, 'UTF-8'); ?></legend>
                    <label class="sr-only" for="cfgProfile"><?= htmlspecialchars(t('config.profile'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <select id="cfgProfile" name="product_id" required>
                        <option value=""><?= htmlspecialchars(t('config.choose_profile'), ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php foreach ($catalog as $product): ?>
                            <option value="<?= (int) $product['id']; ?>"
                                data-profile="<?= htmlspecialchars($product['profile_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </fieldset>

                <fieldset>
                    <legend><?= htmlspecialchars(t('config.gauge'), ENT_QUOTES, 'UTF-8'); ?></legend>
                    <label class="sr-only" for="cfgGauge"><?= htmlspecialchars(t('config.gauge'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <select id="cfgGauge" name="gauge" required disabled>
                        <option value=""><?= htmlspecialchars(t('config.choose_gauge'), ENT_QUOTES, 'UTF-8'); ?></option>
                    </select>
                </fieldset>

                <fieldset>
                    <legend><?= htmlspecialchars(t('config.finish'), ENT_QUOTES, 'UTF-8'); ?></legend>
                    <label class="sr-only" for="cfgFinish"><?= htmlspecialchars(t('config.finish'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <select id="cfgFinish" name="finish" required disabled>
                        <option value=""><?= htmlspecialchars(t('config.choose_finish'), ENT_QUOTES, 'UTF-8'); ?></option>
                    </select>
                </fieldset>

                <fieldset>
                    <legend><?= htmlspecialchars(t('config.color'), ENT_QUOTES, 'UTF-8'); ?></legend>
                    <div class="color-swatches" id="cfgColors" role="listbox" aria-label="<?= htmlspecialchars(t('config.color'), ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <input type="hidden" id="cfgVariantId" name="variant_id" value="">
                    <p class="color-selected" id="cfgColorLabel"><?= htmlspecialchars(t('config.choose_color'), ENT_QUOTES, 'UTF-8'); ?></p>
                </fieldset>

                <fieldset>
                    <legend><?= htmlspecialchars(t('config.length'), ENT_QUOTES, 'UTF-8'); ?></legend>
                    <label class="sr-only" for="cfgLength"><?= htmlspecialchars(t('config.length'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <div class="length-field">
                        <input type="number" id="cfgLength" name="length" min="0.1" max="15" step="0.1" placeholder="e.g. 3.5" required>
                        <span class="unit">m</span>
                    </div>
                    <p class="field-hint"><?= htmlspecialchars(t('config.length_hint'), ENT_QUOTES, 'UTF-8'); ?></p>
                </fieldset>

                <p class="config-error" id="cfgError" hidden></p>
            </form>

            <aside class="configurator-preview">
                <div class="roof-preview" id="roofPreview">
                    <svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect x="0" y="0" width="320" height="180" fill="#f0f0f0"/>
                        <polygon class="roof-fill" id="roofFill" points="20,110 160,30 300,110 280,110 160,50 40,110" fill="#888888"/>
                        <g stroke="#222" stroke-width="1.5" fill="none" opacity=".35">
                            <line x1="55" y1="100" x2="160" y2="45"/>
                            <line x1="90" y1="100" x2="160" y2="52"/>
                            <line x1="125" y1="100" x2="160" y2="55"/>
                            <line x1="195" y1="100" x2="160" y2="55"/>
                            <line x1="230" y1="100" x2="160" y2="52"/>
                            <line x1="265" y1="100" x2="160" y2="45"/>
                        </g>
                        <rect x="70" y="110" width="180" height="50" fill="#2d2d2d"/>
                        <rect x="120" y="125" width="40" height="35" fill="#1a1a1a"/>
                    </svg>
                </div>
                <div class="price-panel">
                    <p class="price-label"><?= htmlspecialchars(t('config.estimated_price'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="price-value" id="cfgPrice">—</p>
                    <p class="price-meta" id="cfgPriceMeta"><?= htmlspecialchars(t('config.price_meta'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <a class="btn primary" href="?page=contact-us"><?= htmlspecialchars(t('config.request_quote'), ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            </aside>
        </div>
    </div>
</section>
<script type="application/json" id="configuratorCatalog"><?= $catalogJson ?: '[]'; ?></script>
<script src="assets/js/configurator.js?v=20260925a" defer></script>
