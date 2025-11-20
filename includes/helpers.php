<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
 * Fetch services optionally filtered by category.
 */
function getServices(string $category = null): array
{
    if ($category) {
        $result = query('SELECT * FROM services WHERE category = ? ORDER BY name_en', [$category]);
    } else {
        $result = query('SELECT * FROM services ORDER BY category, name_en');
    }

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
    switch ($page) {
        case 'welcome':
            return t('nav.welcome');
        case 'our-projects':
            return t('nav.projects');
        case 'photos':
            return t('nav.gallery.photos');
        case 'videos':
            return t('nav.gallery.videos');
        default:
            return ucfirst(str_replace('-', ' ', $page));
    }
}

/**
 * Returns the structured navigation tree.
 */
function navigation(): array
{
    return [
        [
            'slug' => 'welcome',
            'label' => 'nav.welcome',
            'children' => [],
        ],
        [
            'slug' => 'about',
            'label' => 'nav.about',
            'children' => [
                ['slug' => 'parent-company', 'label' => 'nav.about.parent_company'],
                ['slug' => 'historic', 'label' => 'nav.about.historic'],
                ['slug' => 'membership', 'label' => 'nav.about.membership'],
                ['slug' => 'partners', 'label' => 'nav.about.partners'],
                ['slug' => 'management', 'label' => 'nav.about.management'],
                ['slug' => 'distributors', 'label' => 'nav.about.distributors'],
                ['slug' => 'vision', 'label' => 'nav.about.vision'],
                ['slug' => 'mission', 'label' => 'nav.about.mission'],
            ],
        ],
        [
            'slug' => 'services',
            'label' => 'nav.services',
            'children' => [
                ['slug' => 'roofings', 'label' => 'nav.services.roofings'],
                ['slug' => 'construction-materials', 'label' => 'nav.services.construction'],
            ],
        ],
        [
            'slug' => 'our-projects',
            'label' => 'nav.projects',
            'children' => [],
        ],
        [
            'slug' => 'publications',
            'label' => 'nav.publications',
            'children' => [
                ['slug' => 'news', 'label' => 'nav.publications.news'],
                ['slug' => 'training', 'label' => 'nav.publications.training'],
                ['slug' => 'national-holidays', 'label' => 'nav.publications.national'],
                ['slug' => 'international-holidays', 'label' => 'nav.publications.international'],
                ['slug' => 'calls-for-tenders', 'label' => 'nav.publications.tenders'],
                ['slug' => 'communicates', 'label' => 'nav.publications.communicates'],
            ],
        ],
        [
            'slug' => 'gallery',
            'label' => 'nav.gallery',
            'children' => [
                ['slug' => 'photos', 'label' => 'nav.gallery.photos'],
                ['slug' => 'videos', 'label' => 'nav.gallery.videos'],
            ],
        ],
        [
            'slug' => 'contact-us',
            'label' => 'nav.contact',
            'children' => [
                ['slug' => 'general-management', 'label' => 'nav.contact.general'],
                ['slug' => 'sales-management', 'label' => 'nav.contact.sales'],
            ],
        ],
    ];
}


