<?php

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/visitor_tracker.php';

$page = $_GET['page'] ?? 'welcome';
$pageData = getPage($page);
$productPages = productListingPages();

include __DIR__ . '/includes/header.php';
?>

<?php if ($page === 'welcome'): ?>
    <?php
    $helpCards = getHelpCards();
    $newsItems = getLatestPublications(3);
    $testimonials = getTestimonials();
    ?>

    <section class="help-section">
        <div class="container">
            <header class="section-head center">
                <p class="eyebrow"><?= t('home.help_eyebrow'); ?></p>
                <h2><?= t('home.help_title'); ?></h2>
            </header>
            <div class="help-grid">
                <?php foreach ($helpCards ?: [] as $card): ?>
                    <?php $cardImg = mediaUrl($card['image_path'] ?? ''); ?>
                    <article class="help-card<?= $cardImg ? ' help-card-media' : ''; ?>"<?= $cardImg ? ' style="--help-bg:url(\'' . htmlspecialchars($cardImg) . '\')"' : ''; ?>>
                        <div class="help-card-roof" aria-hidden="true"></div>
                        <?php if ($cardImg): ?><div class="help-card-media-bg" aria-hidden="true"></div><?php endif; ?>
                        <div class="help-card-body">
                            <h3><?= htmlspecialchars(localized($card, 'title')); ?></h3>
                            <p><?= htmlspecialchars(localized($card, 'body')); ?></p>
                            <a class="btn help-cta" href="<?= htmlspecialchars($card['link_url']); ?>">
                                <?= htmlspecialchars(localized($card, 'link_label')); ?>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php if (!$helpCards): ?>
                    <article class="help-card">
                        <div class="help-card-roof" aria-hidden="true"></div>
                        <div class="help-card-body">
                            <h3><?= t('nav.building.residential'); ?></h3>
                            <p><?= t('home.roofing_card'); ?></p>
                            <a class="btn help-cta" href="?page=residential-roofing"><?= t('service.learn'); ?></a>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php $whyBanner = mediaUrl(getSetting('why_banner_image')); ?>
    <section class="why-banner visualizer-band<?= $whyBanner ? ' has-image' : ''; ?>"<?= $whyBanner ? ' style="--why-banner-image:url(\'' . htmlspecialchars($whyBanner) . '\')"' : ''; ?>>
        <div class="container center-text">
            <h2><?= t('home.why_title'); ?></h2>
            <p><?= t('home.why_lead'); ?></p>
            <a class="btn primary" href="?page=why-musumba"><?= t('home.try_cta'); ?></a>
        </div>
    </section>

    <section class="find-us find-centre">
        <div class="container find-centre-grid">
            <div class="find-centre-copy">
                <h2><?= t('home.find_title'); ?></h2>
                <p><?= t('home.find_lead'); ?></p>
                <a class="btn primary" href="?page=retail-centres"><?= t('home.get_directions'); ?></a>
            </div>
            <div class="find-grid">
                <article>
                    <h3><?= t('footer.factory'); ?></h3>
                    <p><?= t('footer.factory_address'); ?></p>
                    <p><a href="tel:+255781502260">+255 781 502 260</a></p>
                    <p><?= t('home.hours_weekdays'); ?></p>
                    <p><?= t('home.hours_saturday'); ?></p>
                </article>
                <article>
                    <h3><?= t('footer.corporate'); ?></h3>
                    <p><?= t('footer.corporate_address'); ?></p>
                    <p><a href="tel:+255761037271">+255 761 037 271</a></p>
                    <p><a href="mailto:info@musumba-steel.com">info@musumba-steel.com</a></p>
                    <p><a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a></p>
                </article>
            </div>
        </div>
    </section>

    <section class="testimonials-section">
        <div class="container">
            <header class="section-head center">
                <p class="eyebrow"><?= t('home.google_reviews_eyebrow'); ?></p>
                <h2><?= t('home.testimonials_title'); ?></h2>
            </header>
            <?php $googleReviewsUrl = getSetting('google_reviews_url'); ?>
            <div class="testimonials-track" id="testimonialsTrack">
                <?php if ($testimonials): ?>
                    <?php foreach ($testimonials as $item): ?>
                        <blockquote class="testimonial-card google-review">
                            <div class="review-meta">
                                <span class="google-badge" title="Google Review">
                                    <svg class="google-g" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    Google
                                </span>
                                <?= reviewStarsHtml((int) ($item['rating'] ?? 5)); ?>
                            </div>
                            <p>“<?= htmlspecialchars(localized($item, 'quote')); ?>”</p>
                            <cite>
                                <strong><?= htmlspecialchars(localized($item, 'name')); ?></strong>
                                <?php if (!empty($item['reviewed_on'])): ?>
                                    <span class="review-date"><?= date('M Y', strtotime((string) $item['reviewed_on'])); ?></span>
                                <?php endif; ?>
                            </cite>
                        </blockquote>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state center-text" style="grid-column:1/-1"><?= t('home.testimonials_empty'); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($testimonials): ?>
            <div class="testimonials-nav">
                <button type="button" id="testimonialPrev" aria-label="Previous">‹</button>
                <button type="button" id="testimonialNext" aria-label="Next">›</button>
            </div>
            <?php endif; ?>
            <p class="center-text review-actions" style="margin-top:1rem">
                <?php if ($googleReviewsUrl): ?>
                    <a class="btn primary" href="<?= htmlspecialchars($googleReviewsUrl); ?>" target="_blank" rel="noopener noreferrer"><?= t('home.see_reviews'); ?></a>
                    <a class="text-link" href="<?= htmlspecialchars($googleReviewsUrl); ?>" target="_blank" rel="noopener noreferrer"><?= t('home.leave_review'); ?></a>
                <?php else: ?>
                    <a class="text-link" href="?page=contact-us"><?= t('nav.contact'); ?></a>
                <?php endif; ?>
            </p>
        </div>
    </section>

    <section class="news-strip more-about">
        <div class="container">
            <header class="section-head">
                <div>
                    <p class="eyebrow"><?= t('home.news_eyebrow'); ?></p>
                    <h2><?= t('home.news_title'); ?></h2>
                    <p><?= t('home.news_lead'); ?></p>
                </div>
                <a class="btn outline" href="?page=need-a-new-roof"><?= t('home.read_more'); ?></a>
            </header>
            <div class="news-grid">
                <article>
                    <h3><?= t('nav.resources.need_roof'); ?></h3>
                    <p><?= t('home.resource.need_roof'); ?></p>
                    <a class="btn help-cta" href="?page=need-a-new-roof"><?= t('home.read_more'); ?></a>
                </article>
                <article>
                    <h3><?= t('nav.resources.why_steel'); ?></h3>
                    <p><?= t('home.resource.why_steel'); ?></p>
                    <a class="btn help-cta" href="?page=why-steel-roofing"><?= t('home.read_more'); ?></a>
                </article>
                <article>
                    <h3><?= t('nav.about.why'); ?></h3>
                    <p><?= t('home.why_lead'); ?></p>
                    <a class="btn help-cta" href="?page=why-musumba"><?= t('home.read_more'); ?></a>
                </article>
            </div>
            <?php if ($newsItems): ?>
                <div class="news-grid" style="margin-top:1.5rem">
                    <?php foreach ($newsItems as $item): ?>
                        <article>
                            <time><?= date('d M Y', strtotime((string) $item['published_on'])); ?></time>
                            <h3><?= htmlspecialchars(localized($item, 'title')); ?></h3>
                            <p><?= htmlspecialchars(function_exists('mb_strimwidth') ? mb_strimwidth(localized($item, 'body'), 0, 120, '…') : substr(localized($item, 'body'), 0, 117) . '…'); ?></p>
                            <a class="text-link" href="?page=news"><?= t('home.read_more'); ?> →</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php
    $distributors = getPartners('distributor');
    $affiliations = getPartners('affiliation');
    ?>
    <?php if ($distributors || $affiliations): ?>
    <section class="partners-strip partners-logos">
        <div class="container">
            <?php if ($distributors): ?>
                <div class="partners-band">
                    <span class="partners-label"><?= t('home.distributors'); ?></span>
                    <div class="partners-logos-row">
                        <?php foreach ($distributors as $partner): ?>
                            <?php $logo = mediaUrl($partner['logo_path'] ?? ''); if (!$logo) { continue; } ?>
                            <?php if (!empty($partner['website_url'])): ?>
                                <a class="partner-logo" href="<?= htmlspecialchars($partner['website_url']); ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($partner['name']); ?>">
                                    <img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($partner['name']); ?>" loading="lazy">
                                </a>
                            <?php else: ?>
                                <span class="partner-logo" title="<?= htmlspecialchars($partner['name']); ?>">
                                    <img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($partner['name']); ?>" loading="lazy">
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($affiliations): ?>
                <div class="partners-band">
                    <span class="partners-label affiliations"><?= t('home.affiliations'); ?></span>
                    <div class="partners-logos-row">
                        <?php foreach ($affiliations as $partner): ?>
                            <?php $logo = mediaUrl($partner['logo_path'] ?? ''); if (!$logo) { continue; } ?>
                            <?php if (!empty($partner['website_url'])): ?>
                                <a class="partner-logo" href="<?= htmlspecialchars($partner['website_url']); ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($partner['name']); ?>">
                                    <img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($partner['name']); ?>" loading="lazy">
                                </a>
                            <?php else: ?>
                                <span class="partner-logo" title="<?= htmlspecialchars($partner['name']); ?>">
                                    <img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($partner['name']); ?>" loading="lazy">
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php else: ?>
    <section class="partners-strip">
        <div class="container">
            <p><?= t('home.trust_bar'); ?></p>
            <div class="partners-row">
                <span>Kahama Factory</span>
                <span>Lake Zone Supply</span>
                <span>Quality Assured</span>
                <span>Local Impact</span>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($page === 'faqs'): ?>
    <section class="page-content">
        <?php if ($pageData): ?>
            <header class="page-header">
                <h2><?= htmlspecialchars(localized($pageData, 'title')); ?></h2>
                <p><?= htmlspecialchars(localized($pageData, 'summary')); ?></p>
            </header>
            <article class="page-body"><?= localized($pageData, 'content'); ?></article>
        <?php endif; ?>
        <div class="faq-list">
            <?php foreach (getFaqs() as $faq): ?>
                <details class="faq-item">
                    <summary><?= htmlspecialchars(localized($faq, 'question')); ?></summary>
                    <div class="faq-answer"><?= nl2br(htmlspecialchars(localized($faq, 'answer'))); ?></div>
                </details>
            <?php endforeach; ?>
        </div>
    </section>

<?php elseif ($page === 'values'): ?>
    <section class="page-content">
        <?php if ($pageData): ?>
            <header class="page-header">
                <h2><?= htmlspecialchars(localized($pageData, 'title')); ?></h2>
                <p><?= htmlspecialchars(localized($pageData, 'summary')); ?></p>
            </header>
        <?php endif; ?>
        <div class="values-grid home-values" style="padding-top:0">
            <?php foreach (coreValues() as $value): ?>
                <article>
                    <h3><?= t($value['title']); ?></h3>
                    <p><?= t($value['body']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

<?php elseif (in_array($page, $productPages, true)): ?>
    <section class="page-content">
        <?php if ($pageData): ?>
            <header class="page-header">
                <h2><?= htmlspecialchars(localized($pageData, 'title')); ?></h2>
                <p><?= htmlspecialchars(localized($pageData, 'summary')); ?></p>
            </header>
            <article class="page-body"><?= localized($pageData, 'content'); ?></article>
        <?php endif; ?>
    </section>
    <?php
    $serviceCategory = $page;
    if ($page === 'roofings') {
        $serviceCategory = 'residential-roofing';
    }
    if ($page === 'construction-materials') {
        $serviceCategory = 'pipes-tubes';
    }
    $services = getServices($serviceCategory);
    if (!$services && in_array($page, ['residential-roofing', 'industrial-roofing', 'coated-steel'], true)) {
        $services = getServices('residential-roofing');
    }
    ?>
    <section class="product-grid">
        <?php foreach ($services as $service): ?>
            <?php $svcImg = mediaUrl($service['image_path'] ?? ''); ?>
            <article class="product-card">
                <?php if ($svcImg): ?>
                    <img class="product-card-image" src="<?= htmlspecialchars($svcImg); ?>" alt="<?= htmlspecialchars(localized($service, 'name')); ?>" loading="lazy">
                <?php else: ?>
                    <div class="product-card-mark" aria-hidden="true">MS</div>
                <?php endif; ?>
                <h3><?= htmlspecialchars(localized($service, 'name')); ?></h3>
                <p><?= htmlspecialchars(localized($service, 'description')); ?></p>
                <div class="product-card-actions">
                    <a href="?page=contact-us" class="text-link"><?= t('service.learn'); ?> →</a>
                    <a href="mailto:sales@musumba-steel.com" class="text-link"><?= t('service.enquire'); ?></a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$services): ?>
            <p class="empty-state"><?= t('home.products_soon'); ?></p>
        <?php endif; ?>
    </section>

<?php elseif ($page !== 'welcome' && $pageData): ?>
    <section class="page-content">
        <header class="page-header">
            <h2><?= htmlspecialchars(localized($pageData, 'title')); ?></h2>
            <p><?= htmlspecialchars(localized($pageData, 'summary')); ?></p>
        </header>
        <article class="page-body">
            <?= localized($pageData, 'content'); ?>
        </article>
    </section>

<?php else: ?>
    <section class="page-content">
        <p><?= t('nav.welcome'); ?></p>
    </section>
<?php endif; ?>

<?php if ($page === 'our-projects'): ?>
    <?php $projects = getProjects(); ?>
    <section class="grid-section">
        <?php foreach ($projects as $project): ?>
            <?php $projImg = mediaUrl($project['image_path'] ?? ''); ?>
            <article class="card project-card">
                <?php if ($projImg): ?>
                    <img class="project-card-image" src="<?= htmlspecialchars($projImg); ?>" alt="<?= htmlspecialchars(localized($project, 'title')); ?>" loading="lazy">
                <?php endif; ?>
                <span class="badge"><?= htmlspecialchars($project['status']); ?></span>
                <h3><?= htmlspecialchars(localized($project, 'title')); ?></h3>
                <p><?= htmlspecialchars(localized($project, 'summary')); ?></p>
                <small><?= date('M Y', strtotime($project['launched_on'])); ?> · <?= htmlspecialchars($project['location']); ?></small>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif (in_array($page, ['news', 'training', 'national-holidays', 'international-holidays', 'calls-for-tenders', 'communicates'], true)): ?>
    <?php
    $publications = $page === 'news'
        ? getLatestPublications(50)
        : getPublications($page);
    ?>
    <section class="timeline">
        <?php foreach ($publications as $pub): ?>
            <?php $images = getPublicationImages((int) $pub['id']); ?>
            <article>
                <h3><?= htmlspecialchars(localized($pub, 'title')); ?></h3>
                <time><?= date('d M Y', strtotime((string) $pub['published_on'])); ?></time>
                <p><?= htmlspecialchars(localized($pub, 'body')); ?></p>
                <?php if (!empty($images)): ?>
                    <div class="publication-images">
                        <?php foreach ($images as $img): ?>
                            <?php $imgUrl = mediaUrl($img['image_path'] ?? ''); if (!$imgUrl) { continue; } ?>
                            <img src="<?= htmlspecialchars($imgUrl); ?>" alt="<?= htmlspecialchars(localized($pub, 'title')); ?>" loading="lazy" onclick="openLightbox('<?= htmlspecialchars($imgUrl); ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif ($page === 'photos'): ?>
    <?php $pictures = getPictures(); ?>
    <section class="gallery-grid">
        <?php if (empty($pictures)): ?>
            <p class="empty-state"><?= t('admin.no_pictures'); ?></p>
        <?php else: ?>
            <?php foreach ($pictures as $pic): ?>
                <?php if (!mediaUrl($pic['image_path'] ?? '')) { continue; } ?>
                <article class="gallery-item">
                    <img src="<?= htmlspecialchars(mediaUrl($pic['image_path'])); ?>" alt="<?= htmlspecialchars(localized($pic, 'title')); ?>" loading="lazy" onclick="openLightbox('<?= htmlspecialchars(mediaUrl($pic['image_path'])); ?>')">
                    <div class="gallery-info">
                        <h3><?= htmlspecialchars(localized($pic, 'title')); ?></h3>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
<?php elseif ($page === 'videos'): ?>
    <?php $videos = getVideos(); ?>
    <section class="videos-grid">
        <?php foreach ($videos as $video): ?>
            <article class="video-card">
                <div class="video-container">
                    <?php if (!empty($video['youtube_id'])): ?>
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($video['youtube_id']); ?>" frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif ($page === 'contact-us' || $page === 'retail-centres'): ?>
    <section class="contact-offices">
        <article>
            <h3><?= t('footer.factory'); ?></h3>
            <p><?= t('footer.factory_address'); ?></p>
            <p>P.O. Box 612, Kahama, Shinyanga, Tanzania</p>
            <p><strong><?= t('contact.factory_phone'); ?>:</strong> <a href="tel:+255781502260">+255 781 502 260</a></p>
            <p><?= t('footer.hours'); ?></p>
        </article>
        <article>
            <h3><?= t('footer.corporate'); ?></h3>
            <p><?= t('footer.corporate_address'); ?></p>
            <p><strong><?= t('contact.corporate_phone'); ?>:</strong> <a href="tel:+255761037271">+255 761 037 271</a></p>
            <p><a href="mailto:info@musumba-steel.com">info@musumba-steel.com</a></p>
            <p><a href="mailto:sales@musumba-steel.com">sales@musumba-steel.com</a></p>
            <p><a href="mailto:marketing@musumba-steel.com">marketing@musumba-steel.com</a></p>
        </article>
    </section>
<?php elseif (in_array($page, ['general-management', 'sales-management'], true)): ?>
    <?php $contacts = getContacts($page); ?>
    <section class="contact-grid">
        <?php foreach ($contacts as $person): ?>
            <article class="card contact-card">
                <h3><?= htmlspecialchars($person['name']); ?></h3>
                <p><?= htmlspecialchars($person['position']); ?></p>
                <p><a href="tel:<?= htmlspecialchars($person['phone']); ?>"><?= htmlspecialchars($person['phone']); ?></a></p>
                <p><a href="mailto:<?= htmlspecialchars($person['email']); ?>"><?= htmlspecialchars($person['email']); ?></a></p>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
