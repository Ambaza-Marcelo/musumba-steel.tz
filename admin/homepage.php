<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/upload.php';

ensureMediaSchema();

$lang = currentLang();
$message = '';
$error = '';

// Clear broken hardcoded media paths that are not real files
try {
    foreach (['home_slides', 'home_help_cards'] as $table) {
        $slidesCheck = query("SELECT id, image_path FROM {$table}");
        if ($slidesCheck) {
            while ($row = $slidesCheck->fetch_assoc()) {
                $p = (string) ($row['image_path'] ?? '');
                if ($p !== '' && !mediaExists($p)) {
                    query("UPDATE {$table} SET image_path = NULL WHERE id = ?", [(int) $row['id']]);
                }
            }
        }
    }
} catch (Throwable $e) {
    // ignore
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_slide') {
            $id = (int) ($_POST['id'] ?? 0);
            $fields = [
                trim($_POST['title_en'] ?? ''),
                trim($_POST['title_sw'] ?? ''),
                trim($_POST['subtitle_en'] ?? ''),
                trim($_POST['subtitle_sw'] ?? ''),
                trim($_POST['cta_label_en'] ?? 'Learn More'),
                trim($_POST['cta_label_sw'] ?? 'Jifunze Zaidi'),
                trim($_POST['cta_url'] ?? '?page=about'),
                (int) ($_POST['sort_order'] ?? 0),
                isset($_POST['is_active']) ? 1 : 0,
            ];
            if ($fields[0] === '' || $fields[1] === '') {
                throw new RuntimeException('Slide titles (EN/SW) are required.');
            }

            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = uploadImage($_FILES['image'], 'slides');
            }

            if ($id > 0) {
                if ($imagePath) {
                    $old = query('SELECT image_path FROM home_slides WHERE id = ?', [$id])->fetch_assoc();
                    deleteMediaFile($old['image_path'] ?? null);
                    query(
                        'UPDATE home_slides SET title_en=?, title_sw=?, subtitle_en=?, subtitle_sw=?, cta_label_en=?, cta_label_sw=?, cta_url=?, sort_order=?, is_active=?, image_path=? WHERE id=?',
                        [...$fields, $imagePath, $id]
                    );
                } else {
                    query(
                        'UPDATE home_slides SET title_en=?, title_sw=?, subtitle_en=?, subtitle_sw=?, cta_label_en=?, cta_label_sw=?, cta_url=?, sort_order=?, is_active=? WHERE id=?',
                        [...$fields, $id]
                    );
                }
            } else {
                query(
                    'INSERT INTO home_slides (title_en, title_sw, subtitle_en, subtitle_sw, cta_label_en, cta_label_sw, cta_url, sort_order, is_active, image_path)
                     VALUES (?,?,?,?,?,?,?,?,?,?)',
                    [...$fields, $imagePath]
                );
            }
            $message = 'Slide saved.';
        }

        if ($action === 'delete_slide') {
            $id = (int) ($_POST['id'] ?? 0);
            $old = query('SELECT image_path FROM home_slides WHERE id = ?', [$id])->fetch_assoc();
            deleteMediaFile($old['image_path'] ?? null);
            query('DELETE FROM home_slides WHERE id = ?', [$id]);
            $message = 'Slide deleted.';
        }

        if ($action === 'save_card') {
            $id = (int) ($_POST['id'] ?? 0);
            $fields = [
                trim($_POST['title_en'] ?? ''),
                trim($_POST['title_sw'] ?? ''),
                trim($_POST['body_en'] ?? ''),
                trim($_POST['body_sw'] ?? ''),
                trim($_POST['link_url'] ?? '?page=contact-us'),
                trim($_POST['link_label_en'] ?? 'Learn More'),
                trim($_POST['link_label_sw'] ?? 'Jifunze Zaidi'),
                (int) ($_POST['sort_order'] ?? 0),
                isset($_POST['is_active']) ? 1 : 0,
            ];
            if ($fields[0] === '' || $fields[1] === '') {
                throw new RuntimeException('Card titles (EN/SW) are required.');
            }

            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $imagePath = uploadImage($_FILES['image'], 'help');
            }

            if ($id > 0) {
                if ($imagePath) {
                    $old = query('SELECT image_path FROM home_help_cards WHERE id = ?', [$id])->fetch_assoc();
                    deleteMediaFile($old['image_path'] ?? null);
                    query(
                        'UPDATE home_help_cards SET title_en=?, title_sw=?, body_en=?, body_sw=?, link_url=?, link_label_en=?, link_label_sw=?, sort_order=?, is_active=?, image_path=? WHERE id=?',
                        [...$fields, $imagePath, $id]
                    );
                } else {
                    query(
                        'UPDATE home_help_cards SET title_en=?, title_sw=?, body_en=?, body_sw=?, link_url=?, link_label_en=?, link_label_sw=?, sort_order=?, is_active=? WHERE id=?',
                        [...$fields, $id]
                    );
                }
            } else {
                query(
                    'INSERT INTO home_help_cards (title_en, title_sw, body_en, body_sw, link_url, link_label_en, link_label_sw, sort_order, is_active, image_path)
                     VALUES (?,?,?,?,?,?,?,?,?,?)',
                    [...$fields, $imagePath]
                );
            }
            $message = 'Help card saved.';
        }

        if ($action === 'delete_card') {
            $id = (int) ($_POST['id'] ?? 0);
            $old = query('SELECT image_path FROM home_help_cards WHERE id = ?', [$id])->fetch_assoc();
            deleteMediaFile($old['image_path'] ?? null);
            query('DELETE FROM home_help_cards WHERE id = ?', [$id]);
            $message = 'Help card deleted.';
        }

        if ($action === 'save_settings') {
            if (!empty($_FILES['logo']['name'])) {
                $old = getSetting('site_logo');
                $path = uploadImage($_FILES['logo'], 'brand');
                deleteMediaFile($old);
                setSetting('site_logo', $path);
            }
            if (!empty($_FILES['banner']['name'])) {
                $old = getSetting('why_banner_image');
                $path = uploadImage($_FILES['banner'], 'banners');
                deleteMediaFile($old);
                setSetting('why_banner_image', $path);
            }
            if (!empty($_FILES['page_hero']['name'])) {
                $old = getSetting('page_hero_image');
                $path = uploadImage($_FILES['page_hero'], 'banners');
                deleteMediaFile($old);
                setSetting('page_hero_image', $path);
            }
            if (!empty($_POST['remove_logo'])) {
                deleteMediaFile(getSetting('site_logo'));
                setSetting('site_logo', '');
            }
            $message = 'Brand / banner images updated.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$slides = [];
$cards = [];
try {
    $r = query('SELECT * FROM home_slides ORDER BY sort_order, id');
    $slides = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    $r = query('SELECT * FROM home_help_cards ORDER BY sort_order, id');
    $cards = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $e) {
    $error = $error ?: $e->getMessage();
}

$editSlide = null;
$editCard = null;
if (isset($_GET['slide'])) {
    $r = query('SELECT * FROM home_slides WHERE id = ?', [(int) $_GET['slide']]);
    $editSlide = $r ? $r->fetch_assoc() : null;
}
if (isset($_GET['card'])) {
    $r = query('SELECT * FROM home_help_cards WHERE id = ?', [(int) $_GET['card']]);
    $editCard = $r ? $r->fetch_assoc() : null;
}

$logo = getSetting('site_logo');
$banner = getSetting('why_banner_image');
$pageHero = getSetting('page_hero_image');
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage Media | Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background:#f5f5f5; font-family:Montserrat,sans-serif; }
        .admin-header { background:linear-gradient(135deg,#2d2d2d,#1a1a1a); color:#fff; padding:1.5rem 2rem; margin-bottom:1.5rem; }
        .admin-nav { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1rem; }
        .admin-nav a { color:#fff; text-decoration:none; background:rgba(255,255,255,.12); padding:.5rem .9rem; border-radius:6px; font-size:.9rem; }
        .admin-nav a:hover { background:var(--brand-gold); color:#111; }
        .wrap { max-width:1200px; margin:0 auto; padding:0 1.25rem 2.5rem; }
        .panel { background:#fff; border:1px solid #e5e5e5; border-radius:10px; padding:1.25rem; margin-bottom:1.25rem; }
        .panel h2 { margin:0 0 1rem; font-size:1.2rem; border-bottom:2px solid var(--brand-gold); padding-bottom:.4rem; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        label { display:block; font-weight:600; font-size:.85rem; margin:.55rem 0 .25rem; }
        input[type=text], input[type=number], textarea, select { width:100%; padding:.55rem .7rem; border:1px solid #ccc; border-radius:6px; font:inherit; }
        textarea { min-height:70px; }
        .btn { display:inline-block; background:var(--brand-gold); color:#111; border:0; padding:.6rem 1rem; font-weight:700; cursor:pointer; text-decoration:none; border-radius:6px; }
        .btn.dark { background:#1a1a1a; color:#fff; }
        .btn.danger { background:#b00020; color:#fff; }
        .msg { padding:.75rem 1rem; background:#e8f5e9; border-radius:6px; margin-bottom:1rem; }
        .err { padding:.75rem 1rem; background:#ffebee; border-radius:6px; margin-bottom:1rem; }
        table { width:100%; border-collapse:collapse; font-size:.9rem; }
        th, td { border-bottom:1px solid #eee; padding:.55rem; text-align:left; vertical-align:top; }
        .thumb { width:90px; height:56px; object-fit:cover; background:#eee; border-radius:4px; }
        .preview { max-width:160px; max-height:90px; object-fit:cover; margin-top:.4rem; border-radius:4px; }
        @media (max-width:900px){ .grid2{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>Homepage &amp; Media</h1>
    <p>Upload hero slides, help-card images, logo and banners. Only uploaded files are shown on the site.</p>
    <nav class="admin-nav">
        <a href="dashboard.php?lang=<?= $lang; ?>">Dashboard</a>
        <a href="homepage.php?lang=<?= $lang; ?>">Homepage Media</a>
        <a href="services.php?lang=<?= $lang; ?>">Products</a>
        <a href="pictures.php?lang=<?= $lang; ?>">Gallery</a>
        <a href="../index.php">View site</a>
    </nav>
</div>
<div class="wrap">
    <?php if ($message): ?><div class="msg"><?= htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error); ?></div><?php endif; ?>

    <section class="panel">
        <h2>Brand &amp; banners</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_settings">
            <div class="grid2">
                <div>
                    <label>Site logo</label>
                    <input type="file" name="logo" accept="image/*">
                    <?php if (mediaUrl($logo)): ?>
                        <img class="preview" src="../<?= htmlspecialchars(mediaUrl($logo)); ?>" alt="Logo">
                        <label><input type="checkbox" name="remove_logo" value="1"> Remove logo</label>
                    <?php endif; ?>
                </div>
                <div>
                    <label>“Why Musumba” banner image</label>
                    <input type="file" name="banner" accept="image/*">
                    <?php if (mediaUrl($banner)): ?>
                        <img class="preview" src="../<?= htmlspecialchars(mediaUrl($banner)); ?>" alt="Banner">
                    <?php endif; ?>
                </div>
                <div>
                    <label>Inner page hero image</label>
                    <input type="file" name="page_hero" accept="image/*">
                    <?php if (mediaUrl($pageHero)): ?>
                        <img class="preview" src="../<?= htmlspecialchars(mediaUrl($pageHero)); ?>" alt="Page hero">
                    <?php endif; ?>
                </div>
            </div>
            <p style="margin-top:1rem"><button class="btn" type="submit">Save brand images</button></p>
        </form>
    </section>

    <section class="panel">
        <h2><?= $editSlide ? 'Edit slide #' . (int) $editSlide['id'] : 'Add hero slide'; ?></h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_slide">
            <input type="hidden" name="id" value="<?= (int) ($editSlide['id'] ?? 0); ?>">
            <div class="grid2">
                <div>
                    <label>Title EN</label>
                    <input type="text" name="title_en" required value="<?= htmlspecialchars($editSlide['title_en'] ?? ''); ?>">
                    <label>Subtitle EN</label>
                    <textarea name="subtitle_en"><?= htmlspecialchars($editSlide['subtitle_en'] ?? ''); ?></textarea>
                    <label>CTA label EN</label>
                    <input type="text" name="cta_label_en" value="<?= htmlspecialchars($editSlide['cta_label_en'] ?? 'Learn More'); ?>">
                </div>
                <div>
                    <label>Title SW</label>
                    <input type="text" name="title_sw" required value="<?= htmlspecialchars($editSlide['title_sw'] ?? ''); ?>">
                    <label>Subtitle SW</label>
                    <textarea name="subtitle_sw"><?= htmlspecialchars($editSlide['subtitle_sw'] ?? ''); ?></textarea>
                    <label>CTA label SW</label>
                    <input type="text" name="cta_label_sw" value="<?= htmlspecialchars($editSlide['cta_label_sw'] ?? 'Jifunze Zaidi'); ?>">
                </div>
            </div>
            <label>CTA URL</label>
            <input type="text" name="cta_url" value="<?= htmlspecialchars($editSlide['cta_url'] ?? '?page=about'); ?>">
            <div class="grid2">
                <div>
                    <label>Sort order</label>
                    <input type="number" name="sort_order" value="<?= (int) ($editSlide['sort_order'] ?? 0); ?>">
                </div>
                <div>
                    <label>Slide image (JPG/PNG/WEBP)</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if (!empty($editSlide['image_path']) && mediaUrl($editSlide['image_path'])): ?>
                        <img class="preview" src="../<?= htmlspecialchars(mediaUrl($editSlide['image_path'])); ?>" alt="">
                    <?php endif; ?>
                </div>
            </div>
            <label><input type="checkbox" name="is_active" value="1" <?= !isset($editSlide['is_active']) || !empty($editSlide['is_active']) ? 'checked' : ''; ?>> Active</label>
            <p style="margin-top:1rem">
                <button class="btn" type="submit">Save slide</button>
                <?php if ($editSlide): ?><a class="btn dark" href="homepage.php?lang=<?= $lang; ?>">Cancel</a><?php endif; ?>
            </p>
        </form>

        <h3 style="margin-top:1.5rem">All slides</h3>
        <table>
            <thead><tr><th>Image</th><th>Title</th><th>Order</th><th>Active</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($slides as $s): ?>
                <tr>
                    <td><?php if (mediaUrl($s['image_path'] ?? '')): ?><img class="thumb" src="../<?= htmlspecialchars(mediaUrl($s['image_path'])); ?>" alt=""><?php else: ?><span style="color:#999">No image</span><?php endif; ?></td>
                    <td><?= htmlspecialchars($s['title_en']); ?></td>
                    <td><?= (int) $s['sort_order']; ?></td>
                    <td><?= !empty($s['is_active']) ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a href="?slide=<?= (int) $s['id']; ?>&lang=<?= $lang; ?>">Edit</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Delete this slide?')">
                            <input type="hidden" name="action" value="delete_slide">
                            <input type="hidden" name="id" value="<?= (int) $s['id']; ?>">
                            <button class="btn danger" type="submit" style="padding:.25rem .5rem;font-size:.75rem">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel">
        <h2><?= $editCard ? 'Edit help card #' . (int) $editCard['id'] : 'Add help card'; ?></h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_card">
            <input type="hidden" name="id" value="<?= (int) ($editCard['id'] ?? 0); ?>">
            <div class="grid2">
                <div>
                    <label>Title EN</label>
                    <input type="text" name="title_en" required value="<?= htmlspecialchars($editCard['title_en'] ?? ''); ?>">
                    <label>Body EN</label>
                    <textarea name="body_en"><?= htmlspecialchars($editCard['body_en'] ?? ''); ?></textarea>
                    <label>Link label EN</label>
                    <input type="text" name="link_label_en" value="<?= htmlspecialchars($editCard['link_label_en'] ?? 'Learn More'); ?>">
                </div>
                <div>
                    <label>Title SW</label>
                    <input type="text" name="title_sw" required value="<?= htmlspecialchars($editCard['title_sw'] ?? ''); ?>">
                    <label>Body SW</label>
                    <textarea name="body_sw"><?= htmlspecialchars($editCard['body_sw'] ?? ''); ?></textarea>
                    <label>Link label SW</label>
                    <input type="text" name="link_label_sw" value="<?= htmlspecialchars($editCard['link_label_sw'] ?? 'Jifunze Zaidi'); ?>">
                </div>
            </div>
            <label>Link URL</label>
            <input type="text" name="link_url" value="<?= htmlspecialchars($editCard['link_url'] ?? '?page=contact-us'); ?>">
            <div class="grid2">
                <div>
                    <label>Sort order</label>
                    <input type="number" name="sort_order" value="<?= (int) ($editCard['sort_order'] ?? 0); ?>">
                </div>
                <div>
                    <label>Card image</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if (!empty($editCard['image_path']) && mediaUrl($editCard['image_path'])): ?>
                        <img class="preview" src="../<?= htmlspecialchars(mediaUrl($editCard['image_path'])); ?>" alt="">
                    <?php endif; ?>
                </div>
            </div>
            <label><input type="checkbox" name="is_active" value="1" <?= !isset($editCard['is_active']) || !empty($editCard['is_active']) ? 'checked' : ''; ?>> Active</label>
            <p style="margin-top:1rem">
                <button class="btn" type="submit">Save card</button>
                <?php if ($editCard): ?><a class="btn dark" href="homepage.php?lang=<?= $lang; ?>">Cancel</a><?php endif; ?>
            </p>
        </form>

        <h3 style="margin-top:1.5rem">All help cards</h3>
        <table>
            <thead><tr><th>Image</th><th>Title</th><th>Order</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($cards as $c): ?>
                <tr>
                    <td><?php if (mediaUrl($c['image_path'] ?? '')): ?><img class="thumb" src="../<?= htmlspecialchars(mediaUrl($c['image_path'])); ?>" alt=""><?php else: ?><span style="color:#999">No image</span><?php endif; ?></td>
                    <td><?= htmlspecialchars($c['title_en']); ?></td>
                    <td><?= (int) $c['sort_order']; ?></td>
                    <td>
                        <a href="?card=<?= (int) $c['id']; ?>&lang=<?= $lang; ?>">Edit</a>
                        <form method="post" style="display:inline" onsubmit="return confirm('Delete this card?')">
                            <input type="hidden" name="action" value="delete_card">
                            <input type="hidden" name="id" value="<?= (int) $c['id']; ?>">
                            <button class="btn danger" type="submit" style="padding:.25rem .5rem;font-size:.75rem">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
