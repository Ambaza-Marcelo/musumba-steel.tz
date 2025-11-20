<?php

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/visitor_tracker.php';

$page = $_GET['page'] ?? 'welcome';
$pageData = getPage($page);

include __DIR__ . '/includes/header.php';
?>

<section class="page-content">
    <?php if ($pageData): ?>
        <header class="page-header">
            <h2><?= htmlspecialchars(localized($pageData, 'title')); ?></h2>
            <p><?= htmlspecialchars(localized($pageData, 'summary')); ?></p>
        </header>
        <article class="page-body">
            <?= localized($pageData, 'content'); ?>
        </article>
    <?php else: ?>
        <p><?= t('nav.welcome'); ?></p>
    <?php endif; ?>
</section>

<?php if (in_array($page, ['roofings', 'construction-materials'], true)): ?>
    <?php $services = getServices($page); ?>
    <section class="grid-section">
        <?php foreach ($services as $service): ?>
            <article class="card">
                <h3><?= htmlspecialchars(localized($service, 'name')); ?></h3>
                <p><?= htmlspecialchars(localized($service, 'description')); ?></p>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif ($page === 'our-projects'): ?>
    <?php $projects = getProjects(); ?>
    <section class="grid-section">
        <?php foreach ($projects as $project): ?>
            <article class="card project-card">
                <span class="badge"><?= htmlspecialchars($project['status']); ?></span>
                <h3><?= htmlspecialchars(localized($project, 'title')); ?></h3>
                <p><?= htmlspecialchars(localized($project, 'summary')); ?></p>
                <small><?= date('M Y', strtotime($project['launched_on'])); ?> · <?= htmlspecialchars($project['location']); ?></small>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif (in_array($page, ['news', 'training', 'national-holidays', 'international-holidays', 'calls-for-tenders', 'communicates'], true)): ?>
    <?php $publications = getPublications($page); ?>
    <section class="timeline">
        <?php foreach ($publications as $pub): ?>
            <?php $images = getPublicationImages((int) $pub['id']); ?>
            <article>
                <h3><?= htmlspecialchars(localized($pub, 'title')); ?></h3>
                <time><?= date('d M Y', strtotime($pub['published_on'])); ?></time>
                <p><?= htmlspecialchars(localized($pub, 'body')); ?></p>
                
                <?php if (!empty($images)): ?>
                    <div class="publication-images">
                        <?php foreach ($images as $img): ?>
                            <img src="<?= htmlspecialchars($img['image_path']); ?>" alt="<?= htmlspecialchars(localized($pub, 'title')); ?>" loading="lazy" onclick="openLightbox('<?= htmlspecialchars($img['image_path']); ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($pub['attachment'])): ?>
                    <a href="<?= htmlspecialchars($pub['attachment']); ?>" target="_blank" class="attachment-link">Download</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php elseif ($page === 'photos'): ?>
    <?php $pictures = getPictures(); ?>
    <section class="gallery-grid">
        <?php if (empty($pictures)): ?>
            <p style="text-align: center; color: #999; padding: 2rem;"><?= t('admin.no_pictures'); ?></p>
        <?php else: ?>
            <?php foreach ($pictures as $pic): ?>
                <article class="gallery-item">
                    <img src="<?= htmlspecialchars($pic['image_path']); ?>" alt="<?= htmlspecialchars(localized($pic, 'title')); ?>" loading="lazy" onclick="openLightbox('<?= htmlspecialchars($pic['image_path']); ?>')">
                    <div class="gallery-info">
                        <h3><?= htmlspecialchars(localized($pic, 'title')); ?></h3>
                        <?php if (!empty(localized($pic, 'description'))): ?>
                            <p><?= htmlspecialchars(localized($pic, 'description')); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
<?php elseif ($page === 'videos'): ?>
    <?php $videos = getVideos(); ?>
    <section class="videos-grid">
        <?php if (empty($videos)): ?>
            <p style="text-align: center; color: #999; padding: 2rem;"><?= t('admin.no_videos'); ?></p>
        <?php else: ?>
            <?php foreach ($videos as $video): ?>
                <article class="video-card">
                    <div class="video-container">
                        <?php if (!empty($video['youtube_id'])): ?>
                            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($video['youtube_id']); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <?php else: ?>
                            <div style="color: #fff; text-align: center;">No preview available</div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
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

