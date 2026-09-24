<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/upload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure media tables/columns exist for a fully dynamic site
try {
    ensureMediaSchema();
} catch (Throwable $e) {
    // ignore on first boot if DB not ready
}

$supportedLanguages = ['en', 'sw'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLanguages, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

/**
 * Returns the current language key.
 */
function currentLang(): string
{
    return $_SESSION['lang'] ?? 'en';
}

/**
 * Translate a UI string.
 */
function t(string $key): string
{
    static $dictionary;

    if ($dictionary === null) {
        $dictionary = include __DIR__ . '/translations.php';
    }

    $lang = currentLang();

    return $dictionary[$key][$lang] ?? $key;
}

/**
 * Fetch a single page by slug.
 */
function getPage(string $slug)
{
    $sql = 'SELECT * FROM pages WHERE slug = ? LIMIT 1';
    $result = query($sql, [$slug]);

    return $result ? $result->fetch_assoc() : null;
}

/**
 * Fetch services optionally filtered by category (supports aliases).
 */
function getServices(string $category = null): array
{
    if (!$category) {
        $result = query('SELECT * FROM services ORDER BY category, name_en');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    $aliases = [
        'residential-roofing' => ['residential-roofing', 'roofings'],
        'industrial-roofing' => ['industrial-roofing'],
        'pipes-tubes' => ['pipes-tubes', 'construction-materials'],
        'flashings' => ['flashings'],
        'coated-steel' => ['coated-steel', 'residential-roofing'],
        'roofings' => ['residential-roofing', 'roofings'],
        'construction-materials' => ['pipes-tubes', 'construction-materials'],
    ];

    $cats = $aliases[$category] ?? [$category];

    if ($category === 'coated-steel') {
        $result = query(
            "SELECT * FROM services WHERE category IN ('residential-roofing','coated-steel')
             AND (name_en LIKE '%Alu-Zinc%' OR name_en LIKE '%Pre-Painted%' OR name_en LIKE '%Rangi Max%' OR name_en LIKE '%Colour%' OR name_en LIKE '%Color%')
             ORDER BY name_en"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    $placeholders = implode(',', array_fill(0, count($cats), '?'));
    $result = query("SELECT * FROM services WHERE category IN ($placeholders) ORDER BY name_en", $cats);

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetch projects.
 */
function getProjects(): array
{
    $result = query('SELECT * FROM projects ORDER BY launched_on DESC');

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetch publications filtered by type.
 */
function getPublications(string $type = null): array
{
    if ($type) {
        $result = query('SELECT * FROM publications WHERE type = ? ORDER BY published_on DESC', [$type]);
    } else {
        $result = query('SELECT * FROM publications ORDER BY published_on DESC');
    }

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetch images for a publication.
 */
function getPublicationImages(int $publicationId): array
{
    $result = query('SELECT * FROM publication_images WHERE publication_id = ? ORDER BY image_order, id', [$publicationId]);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetch contact entries filtered by department.
 */
function getContacts(string $department = null): array
{
    if ($department) {
        $result = query('SELECT * FROM contacts WHERE department = ? ORDER BY priority', [$department]);
    } else {
        $result = query('SELECT * FROM contacts ORDER BY department, priority');
    }

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetch pictures (photos).
 */
function getPictures(): array
{
    $result = query('SELECT * FROM pictures ORDER BY display_order, created_at DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Fetch videos.
 */
function getVideos(): array
{
    $result = query('SELECT * FROM videos ORDER BY display_order, created_at DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Format text by language.
 */
function localized(array $row, string $fieldBase): string
{
    $lang = currentLang() === 'sw' ? '_sw' : '_en';
    $field = $fieldBase . $lang;

    return $row[$field] ?? '';
}

/**
 * Determine page heading default.
 */
function pageHeading(string $page): string
{
    $map = [
        'welcome' => 'nav.welcome',
        'about' => 'nav.about',
        'parent-company' => 'nav.about.parent_company',
        'historic' => 'nav.about.historic',
        'management' => 'nav.about.people',
        'our-community' => 'nav.about.community',
        'why-musumba' => 'nav.about.why',
        'vision' => 'nav.about.vision',
        'mission' => 'nav.about.mission',
        'values' => 'nav.about.values',
        'residential-roofing' => 'nav.building.residential',
        'industrial-roofing' => 'nav.building.industrial',
        'pipes-tubes' => 'nav.building.pipes',
        'flashings' => 'nav.building.flashings',
        'construction-materials' => 'nav.building.pipes',
        'roofings' => 'nav.building.residential',
        'coated-steel' => 'nav.coated',
        'need-a-new-roof' => 'nav.resources.need_roof',
        'why-steel-roofing' => 'nav.resources.why_steel',
        'roof-designs' => 'nav.resources.designs',
        'technical-specs' => 'nav.resources.specs',
        'storage-handling' => 'nav.resources.storage',
        'warranty' => 'nav.resources.warranty',
        'faqs' => 'nav.faqs',
        'deals' => 'nav.buy.deals',
        'retail-centres' => 'nav.buy.centres',
        'distributors' => 'nav.buy.distributors',
        'services' => 'nav.services',
        'our-projects' => 'nav.projects',
        'news' => 'nav.publications.news',
        'training' => 'nav.publications.training',
        'calls-for-tenders' => 'nav.publications.tenders',
        'communicates' => 'nav.publications.communicates',
        'photos' => 'nav.gallery.photos',
        'videos' => 'nav.gallery.videos',
        'contact-us' => 'nav.contact',
        'general-management' => 'nav.contact.general',
        'sales-management' => 'nav.contact.sales',
    ];

    if (isset($map[$page])) {
        return t($map[$page]);
    }

    $pageData = getPage($page);
    if ($pageData) {
        $title = localized($pageData, 'title');
        if ($title !== '') {
            return $title;
        }
    }

    return ucfirst(str_replace('-', ' ', $page));
}

/**
 * Mabati-style main navigation (Musumba labels & pages).
 */
function navigation(): array
{
    return [
        [
            'slug' => 'about',
            'label' => 'nav.who_we_are',
            'children' => [
                ['slug' => 'about', 'label' => 'nav.about'],
                ['slug' => 'management', 'label' => 'nav.about.people'],
                ['slug' => 'our-community', 'label' => 'nav.about.community'],
                ['slug' => 'vision', 'label' => 'nav.about.vision'],
                ['slug' => 'mission', 'label' => 'nav.about.mission'],
                ['slug' => 'values', 'label' => 'nav.about.values'],
                ['slug' => 'why-musumba', 'label' => 'nav.about.why'],
                ['slug' => 'news', 'label' => 'nav.publications.news'],
                ['slug' => 'parent-company', 'label' => 'nav.about.parent_company'],
            ],
        ],
        [
            'slug' => 'residential-roofing',
            'label' => 'nav.building_solutions',
            'children' => [
                ['slug' => 'residential-roofing', 'label' => 'nav.building.residential'],
                ['slug' => 'industrial-roofing', 'label' => 'nav.building.industrial'],
                ['slug' => 'pipes-tubes', 'label' => 'nav.building.pipes'],
                ['slug' => 'flashings', 'label' => 'nav.building.flashings'],
            ],
        ],
        [
            'slug' => 'coated-steel',
            'label' => 'nav.coated',
            'children' => [
                ['slug' => 'coated-steel', 'label' => 'nav.coated.all'],
                ['slug' => 'coated-steel', 'label' => 'nav.coated.aluzinc'],
                ['slug' => 'coated-steel', 'label' => 'nav.coated.colour'],
            ],
        ],
        [
            'slug' => 'need-a-new-roof',
            'label' => 'nav.resources',
            'children' => [
                ['slug' => 'need-a-new-roof', 'label' => 'nav.resources.homeowners_group', 'is_heading' => true],
                ['slug' => 'need-a-new-roof', 'label' => 'nav.resources.need_roof'],
                ['slug' => 'why-steel-roofing', 'label' => 'nav.resources.why_steel'],
                ['slug' => 'roof-designs', 'label' => 'nav.resources.designs'],
                ['slug' => 'technical-specs', 'label' => 'nav.resources.pros_group', 'is_heading' => true],
                ['slug' => 'technical-specs', 'label' => 'nav.resources.specs'],
                ['slug' => 'storage-handling', 'label' => 'nav.resources.storage'],
                ['slug' => 'warranty', 'label' => 'nav.resources.warranty'],
            ],
        ],
        [
            'slug' => 'faqs',
            'label' => 'nav.faqs',
            'children' => [],
        ],
        [
            'slug' => 'deals',
            'label' => 'nav.where_to_buy',
            'children' => [
                ['slug' => 'deals', 'label' => 'nav.buy.deals'],
                ['slug' => 'retail-centres', 'label' => 'nav.buy.centres'],
                ['slug' => 'distributors', 'label' => 'nav.buy.distributors'],
            ],
        ],
        [
            'slug' => 'services',
            'label' => 'nav.services',
            'children' => [],
        ],
        [
            'slug' => 'our-projects',
            'label' => 'nav.projects',
            'children' => [],
        ],
        [
            'slug' => 'contact-us',
            'label' => 'nav.contact',
            'children' => [],
        ],
    ];
}

/**
 * Active home hero slides.
 */
function getHomeSlides(): array
{
    try {
        $result = query('SELECT * FROM home_slides WHERE is_active = 1 ORDER BY sort_order, id');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * “How can we help you” audience cards.
 */
function getHelpCards(): array
{
    try {
        $result = query('SELECT * FROM home_help_cards WHERE is_active = 1 ORDER BY sort_order, id');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Featured products for homepage (limit).
 */
function getFeaturedServices(int $limit = 6): array
{
    $limit = max(1, min(24, $limit));
    try {
        $result = query('SELECT * FROM services ORDER BY category, name_en LIMIT ' . $limit);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Latest publications for homepage resource strip.
 */
function getLatestPublications(int $limit = 3): array
{
    $limit = max(1, min(12, $limit));
    try {
        $result = query(
            "SELECT * FROM publications
             WHERE title_en NOT LIKE 'publication%'
             ORDER BY published_on DESC, id DESC
             LIMIT " . $limit
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * FAQs from database.
 */
function getFaqs(?string $category = null): array
{
    try {
        if ($category) {
            $result = query('SELECT * FROM faqs WHERE is_active = 1 AND category = ? ORDER BY sort_order, id', [$category]);
        } else {
            $result = query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order, id');
        }
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Partners logos: distributors and affiliations.
 *
 * @param string|null $category 'distributor'|'affiliation'|null for all
 */
function getPartners(?string $category = null): array
{
    try {
        if ($category === 'distributor' || $category === 'affiliation') {
            $result = query(
                'SELECT * FROM partners WHERE is_active = 1 AND category = ? ORDER BY sort_order, id',
                [$category]
            );
        } else {
            $result = query('SELECT * FROM partners WHERE is_active = 1 ORDER BY category, sort_order, id');
        }
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        return array_values(array_filter($rows, static function (array $row): bool {
            return mediaUrl($row['logo_path'] ?? '') !== '';
        }));
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Testimonials / Google reviews from database (no fake fallbacks).
 */
function getTestimonials(): array
{
    try {
        $result = query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id DESC');
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Render 1–5 star rating for Google-style reviews.
 */
function reviewStarsHtml(int $rating): string
{
    $rating = max(1, min(5, $rating));
    $html = '<span class="review-stars" aria-label="' . $rating . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<span class="star filled" aria-hidden="true">★</span>'
            : '<span class="star" aria-hidden="true">☆</span>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Core values for values page.
 */
function coreValues(): array
{
    return [
        ['title' => 'home.value.quality', 'body' => 'home.value.quality_body'],
        ['title' => 'home.value.integrity', 'body' => 'home.value.integrity_body'],
        ['title' => 'home.value.customer', 'body' => 'home.value.customer_body'],
        ['title' => 'home.value.innovation', 'body' => 'home.value.innovation_body'],
        ['title' => 'home.value.safety', 'body' => 'home.value.safety_body'],
        ['title' => 'home.value.excellence', 'body' => 'home.value.excellence_body'],
        ['title' => 'home.value.local', 'body' => 'home.value.local_body'],
    ];
}

/**
 * @deprecated use getTestimonials()
 */
function homeTestimonials(): array
{
    return getTestimonials();
}

/**
 * Whether a nav item (or one of its children) matches the current page.
 */
function isNavActive(string $page, array $item): bool
{
    if ($page === $item['slug']) {
        return true;
    }

    foreach ($item['children'] ?? [] as $child) {
        if ($page === $child['slug']) {
            return true;
        }
        foreach ($child['children'] ?? [] as $grand) {
            if ($page === $grand['slug']) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Product center sidebar links.
 */
function productCenterLinks(): array
{
    return [
        ['slug' => 'residential-roofing', 'label' => 'nav.building.residential'],
        ['slug' => 'industrial-roofing', 'label' => 'nav.building.industrial'],
        ['slug' => 'pipes-tubes', 'label' => 'nav.building.pipes'],
        ['slug' => 'flashings', 'label' => 'nav.building.flashings'],
        ['slug' => 'coated-steel', 'label' => 'nav.coated'],
        ['slug' => 'contact-us', 'label' => 'nav.contact'],
    ];
}

/**
 * Show product center on product pages.
 */
function showProductCenter(string $page): bool
{
    return in_array($page, [
        'residential-roofing',
        'industrial-roofing',
        'pipes-tubes',
        'flashings',
        'coated-steel',
        'roofings',
        'construction-materials',
        'services',
    ], true);
}

/**
 * Product category slugs that list services.
 */
function productListingPages(): array
{
    return [
        'residential-roofing',
        'industrial-roofing',
        'pipes-tubes',
        'flashings',
        'coated-steel',
        'roofings',
        'construction-materials',
    ];
}

/**
 * Breadcrumb trail for interior pages.
 */
function breadcrumbs(string $page): array
{
    if ($page === 'welcome') {
        return [];
    }

    $crumbs = [
        ['slug' => 'welcome', 'label' => t('nav.welcome')],
    ];

    $parents = [
        'parent-company' => 'about',
        'historic' => 'about',
        'membership' => 'about',
        'partners' => 'about',
        'management' => 'about',
        'our-community' => 'about',
        'why-musumba' => 'about',
        'distributors' => 'deals',
        'vision' => 'about',
        'mission' => 'about',
        'values' => 'about',
        'residential-roofing' => 'residential-roofing',
        'industrial-roofing' => 'residential-roofing',
        'pipes-tubes' => 'residential-roofing',
        'flashings' => 'residential-roofing',
        'coated-steel' => 'coated-steel',
        'roofings' => 'residential-roofing',
        'construction-materials' => 'pipes-tubes',
        'need-a-new-roof' => 'need-a-new-roof',
        'why-steel-roofing' => 'need-a-new-roof',
        'roof-designs' => 'need-a-new-roof',
        'technical-specs' => 'need-a-new-roof',
        'storage-handling' => 'need-a-new-roof',
        'warranty' => 'need-a-new-roof',
        'deals' => 'deals',
        'retail-centres' => 'deals',
        'news' => 'news',
        'training' => 'news',
        'national-holidays' => 'news',
        'international-holidays' => 'news',
        'calls-for-tenders' => 'news',
        'communicates' => 'news',
        'photos' => 'photos',
        'videos' => 'photos',
        'general-management' => 'contact-us',
        'sales-management' => 'contact-us',
    ];

    if (isset($parents[$page]) && $parents[$page] !== $page) {
        $parent = $parents[$page];
        $crumbs[] = ['slug' => $parent, 'label' => pageHeading($parent)];
    }

    $crumbs[] = ['slug' => null, 'label' => pageHeading($page)];

    return $crumbs;
}


