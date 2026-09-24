<?php

declare(strict_types=1);

/**
 * Shared media helpers for dynamic uploads.
 */

function uploadsRoot(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
}

function ensureUploadDir(string $subdir): string
{
    $dir = uploadsRoot() . DIRECTORY_SEPARATOR . trim($subdir, '/\\');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Whether a stored relative media path exists on disk.
 * Dynamic CMS media must live under uploads/ (admin uploads only).
 */
function mediaExists(?string $path, bool $uploadsOnly = true): bool
{
    if ($path === null || trim($path) === '') {
        return false;
    }
    $path = ltrim(str_replace(['\\', '..'], ['/', ''], $path), '/');
    if ($uploadsOnly && strpos($path, 'uploads/') !== 0) {
        return false;
    }
    $full = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    return is_file($full);
}

/**
 * Return relative path if file exists, otherwise empty string.
 * By default only accepts paths under uploads/.
 */
function mediaUrl(?string $path, bool $uploadsOnly = true): string
{
    $path = ltrim(str_replace('\\', '/', (string) $path), '/');
    return mediaExists($path, $uploadsOnly) ? $path : '';
}

/**
 * Upload an image; returns relative web path (uploads/...).
 *
 * @throws RuntimeException
 */
function uploadImage(array $file, string $subdir = 'general'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . ($file['error'] ?? 'unknown') . ').');
    }

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: ($file['type'] ?? '');
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Invalid file type. Use JPG, PNG, GIF or WEBP.');
    }

    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        throw new RuntimeException('File exceeds 10MB limit.');
    }

    $extMap = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    $ext = $extMap[$mime] ?? 'jpg';
    $dir = ensureUploadDir($subdir);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . DIRECTORY_SEPARATOR . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    return 'uploads/' . trim($subdir, '/') . '/' . $name;
}

/**
 * Delete a media file if it lives under uploads/.
 */
function deleteMediaFile(?string $path): void
{
    if (!mediaExists($path)) {
        return;
    }
    $path = str_replace('\\', '/', (string) $path);
    if (strpos($path, 'uploads/') !== 0) {
        return;
    }
    $full = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    @unlink($full);
}

/**
 * Site setting get/set (logo, banner images, etc.).
 */
function getSetting(string $key, string $default = ''): string
{
    try {
        $result = query('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1', [$key]);
        if ($result && $row = $result->fetch_assoc()) {
            return (string) ($row['setting_value'] ?? $default);
        }
    } catch (Throwable $e) {
        // table may not exist yet
    }
    return $default;
}

function setSetting(string $key, string $value): void
{
    query(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
}

/**
 * Ensure dynamic media schema columns/tables exist.
 */
function ensureMediaSchema(): void
{
    $db = db();
    @$db->query("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(120) PRIMARY KEY,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    @$db->query("CREATE TABLE IF NOT EXISTS home_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_en VARCHAR(255) NOT NULL,
        title_sw VARCHAR(255) NOT NULL,
        subtitle_en TEXT,
        subtitle_sw TEXT,
        cta_label_en VARCHAR(120) DEFAULT 'Learn More',
        cta_label_sw VARCHAR(120) DEFAULT 'Jifunze Zaidi',
        cta_url VARCHAR(255) DEFAULT '?page=about',
        image_path VARCHAR(500) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    @$db->query("CREATE TABLE IF NOT EXISTS home_help_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_en VARCHAR(255) NOT NULL,
        title_sw VARCHAR(255) NOT NULL,
        body_en TEXT,
        body_sw TEXT,
        link_url VARCHAR(255) DEFAULT '?page=contact-us',
        link_label_en VARCHAR(120) DEFAULT 'Learn More',
        link_label_sw VARCHAR(120) DEFAULT 'Jifunze Zaidi',
        image_path VARCHAR(500) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    @$db->query("ALTER TABLE home_help_cards ADD COLUMN image_path VARCHAR(500) DEFAULT NULL AFTER link_label_sw");
    @$db->query("ALTER TABLE services ADD COLUMN image_path VARCHAR(500) DEFAULT NULL AFTER description_sw");
    @$db->query("ALTER TABLE projects ADD COLUMN image_path VARCHAR(500) DEFAULT NULL AFTER summary_sw");
    @$db->query("ALTER TABLE pages ADD COLUMN image_path VARCHAR(500) DEFAULT NULL AFTER content_sw");

    @$db->query("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name_en VARCHAR(255) NOT NULL,
        name_sw VARCHAR(255) NOT NULL,
        quote_en TEXT NOT NULL,
        quote_sw TEXT NOT NULL,
        rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
        source VARCHAR(40) NOT NULL DEFAULT 'google',
        reviewed_on DATE NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    @$db->query("ALTER TABLE testimonials ADD COLUMN rating TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER quote_sw");
    @$db->query("ALTER TABLE testimonials ADD COLUMN source VARCHAR(40) NOT NULL DEFAULT 'google' AFTER rating");
    @$db->query("ALTER TABLE testimonials ADD COLUMN reviewed_on DATE NULL AFTER source");

    @$db->query("CREATE TABLE IF NOT EXISTS partners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category ENUM('distributor','affiliation') NOT NULL DEFAULT 'distributor',
        logo_path VARCHAR(500) DEFAULT NULL,
        website_url VARCHAR(500) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Drop placeholder/demo testimonials that were never real Google reviews
    @$db->query("DELETE FROM testimonials WHERE source = 'google' AND quote_en LIKE 'Musumba Steel has been our first choice%'");
    @$db->query("DELETE FROM testimonials WHERE quote_en LIKE 'Excellent products and customer care%'");
    @$db->query("DELETE FROM testimonials WHERE quote_en LIKE 'Strong steel, fair pricing%'");
    @$db->query("DELETE FROM testimonials WHERE quote_en LIKE 'Professional service from corporate%'");
}
