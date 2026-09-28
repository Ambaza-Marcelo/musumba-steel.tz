<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$adminNavActive = 'services';
$error = '';
$services = [];
$editing = null;

try {
    $services = getServices() ?: [];
} catch (Throwable $e) {
    $error = 'Unable to load services.';
    $services = [];
}

if (isset($_GET['id'])) {
    $editing = adminFetchOne('SELECT * FROM services WHERE id = ?', [(int) $_GET['id']]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payload = [
            trim((string) ($_POST['name_en'] ?? '')),
            trim((string) ($_POST['name_sw'] ?? '')),
            trim((string) ($_POST['description_en'] ?? '')),
            trim((string) ($_POST['description_sw'] ?? '')),
            trim((string) ($_POST['category'] ?? 'residential-roofing')),
        ];

        if ($payload[0] === '' || $payload[1] === '') {
            throw new InvalidArgumentException('Name EN and Name SW are required.');
        }

        $imagePath = '';
        if (!empty($_FILES['image']['name'])) {
            $imagePath = uploadImage($_FILES['image'], 'products') ?: '';
        }

        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
            if ($imagePath !== '') {
                $old = adminFetchOne('SELECT image_path FROM services WHERE id = ?', [$id]);
                if (!empty($old['image_path'])) {
                    deleteMediaFile($old['image_path']);
                }
                try {
                    query(
                        'UPDATE services SET name_en=?, name_sw=?, description_en=?, description_sw=?, category=?, image_path=? WHERE id=?',
                        adminParams([...$payload, $imagePath, $id])
                    );
                } catch (Throwable $e) {
                    query(
                        'UPDATE services SET name_en=?, name_sw=?, description_en=?, description_sw=?, category=? WHERE id=?',
                        adminParams([...$payload, $id])
                    );
                }
            } else {
                query(
                    'UPDATE services SET name_en=?, name_sw=?, description_en=?, description_sw=?, category=? WHERE id=?',
                    adminParams([...$payload, $id])
                );
            }
        } else {
            try {
                query(
                    'INSERT INTO services (name_en, name_sw, description_en, description_sw, category, image_path) VALUES (?,?,?,?,?,?)',
                    adminParams([...$payload, $imagePath])
                );
            } catch (Throwable $e) {
                query(
                    'INSERT INTO services (name_en, name_sw, description_en, description_sw, category) VALUES (?,?,?,?,?)',
                    adminParams($payload)
                );
            }
        }

        header('Location: services.php?lang=' . rawurlencode(currentLang()));
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('admin.services'), ENT_QUOTES, 'UTF-8'); ?> | Musumba Steel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f5f5f5; font-family: 'Montserrat', sans-serif; }
        .admin-header {
            background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
            color: white; padding: 2rem; margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .admin-header h1 { margin: 0 0 1rem 0; font-size: 1.8rem; }
        .admin-header .user-info {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem;
        }
        .admin-nav { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem; }
        .admin-nav a {
            padding: 0.6rem 1.2rem; background: rgba(255, 255, 255, 0.1);
            color: white; text-decoration: none; border-radius: 6px; font-weight: 500;
        }
        .admin-nav a:hover { background: var(--primary); color: #111; }
        .admin-layout { max-width: 1400px; margin: 0 auto; padding: 0 2rem 2rem; }
        .dashboard-section {
            background: white; border-radius: 12px; padding: 1.5rem;
            border: 1px solid #e0e0e0; margin-bottom: 2rem;
        }
        .dashboard-section h2 {
            margin: 0 0 1rem 0; color: #333; font-size: 1.3rem;
            border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.85rem; text-align: left; border-bottom: 1px solid #eee; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
        label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.75rem; }
        input, select, textarea {
            width: 100%; margin-top: 0.35rem; padding: 0.7rem; border: 1px solid #ddd;
            border-radius: 6px; font: inherit; box-sizing: border-box;
        }
        textarea { min-height: 110px; }
        .form-full-width { grid-column: 1 / -1; }
        .btn { padding: 0.85rem 1.3rem; border: 0; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn.primary { background: var(--primary); color: #111; }
        .err { background: #ffebee; color: #b00020; padding: 0.85rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
    </style>
</head>

<body>
    <div class="admin-header">
        <div class="user-info">
            <div>
                <h1><?= htmlspecialchars(t('admin.services'), ENT_QUOTES, 'UTF-8'); ?></h1>
                <p style="margin:0;opacity:.9"><?= htmlspecialchars(t('admin.manage_services'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div style="display:flex;gap:1rem;align-items:center">
                <a href="?lang=en" style="color:#fff;text-decoration:none;padding:.4rem .8rem;background:<?= $lang === 'en' ? 'rgba(255,255,255,.2)' : 'rgba(255,255,255,.1)'; ?>;border-radius:4px">EN</a>
                <a href="?lang=sw" style="color:#fff;text-decoration:none;padding:.4rem .8rem;background:<?= $lang === 'sw' ? 'rgba(255,255,255,.2)' : 'rgba(255,255,255,.1)'; ?>;border-radius:4px">SW</a>
                <a href="../index.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" style="color:#fff;text-decoration:none;padding:.6rem 1.2rem;background:rgba(255,255,255,.1);border-radius:6px"><?= htmlspecialchars(t('admin.view_site'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
        <?php include __DIR__ . '/includes/nav.php'; ?>
    </div>

    <div class="admin-layout">
        <?php if ($error !== ''): ?>
            <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <section class="dashboard-section">
            <h2><?= htmlspecialchars(t('admin.services_list'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= htmlspecialchars(t('admin.name_en'), ENT_QUOTES, 'UTF-8'); ?></th>
                        <th><?= htmlspecialchars(t('admin.category'), ENT_QUOTES, 'UTF-8'); ?></th>
                        <th><?= htmlspecialchars(t('admin.actions'), ENT_QUOTES, 'UTF-8'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$services): ?>
                        <tr><td colspan="3" style="text-align:center;color:#999;padding:2rem"><?= htmlspecialchars(t('admin.no_services'), ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($service['name_en'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars(ucfirst(str_replace('-', ' ', (string) ($service['category'] ?? ''))), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><a href="?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>&id=<?= (int) ($service['id'] ?? 0); ?>"><?= htmlspecialchars(t('admin.edit'), ENT_QUOTES, 'UTF-8'); ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="dashboard-section">
            <h2><?= htmlspecialchars($editing ? t('admin.edit_service') : t('admin.new_service'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <form method="post" enctype="multipart/form-data" action="services.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">
                <div class="form-grid">
                    <label><?= htmlspecialchars(t('admin.name_en'), ENT_QUOTES, 'UTF-8'); ?>
                        <input type="text" name="name_en" required value="<?= htmlspecialchars((string) ($editing['name_en'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label><?= htmlspecialchars(t('admin.name_sw'), ENT_QUOTES, 'UTF-8'); ?>
                        <input type="text" name="name_sw" required value="<?= htmlspecialchars((string) ($editing['name_sw'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                    <label class="form-full-width"><?= htmlspecialchars(t('admin.description_en'), ENT_QUOTES, 'UTF-8'); ?>
                        <textarea name="description_en"><?= htmlspecialchars((string) ($editing['description_en'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                    <label class="form-full-width"><?= htmlspecialchars(t('admin.description_sw'), ENT_QUOTES, 'UTF-8'); ?>
                        <textarea name="description_sw"><?= htmlspecialchars((string) ($editing['description_sw'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                    <label><?= htmlspecialchars(t('admin.category'), ENT_QUOTES, 'UTF-8'); ?>
                        <?php
                        $cats = [
                            'residential-roofing' => 'Residential Roofing',
                            'industrial-roofing' => 'Industrial Roofing',
                            'flashings' => 'Flashings',
                            'pipes-tubes' => 'Pipes & Tubes',
                            'coated-steel' => 'Coated Steel',
                            'roofings' => 'Roofings',
                            'construction-materials' => 'Construction Materials',
                        ];
                        $cur = (string) ($editing['category'] ?? 'residential-roofing');
                        ?>
                        <select name="category" required>
                            <?php foreach ($cats as $val => $label): ?>
                                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>"<?= $cur === $val ? ' selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="form-full-width">Product image
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        <?php
                        $preview = '';
                        try {
                            $preview = mediaUrl($editing['image_path'] ?? '');
                        } catch (Throwable $e) {
                            $preview = '';
                        }
                        if ($preview):
                        ?>
                            <img src="../<?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8'); ?>" alt="" style="max-width:160px;margin-top:.5rem;display:block;border-radius:6px">
                        <?php endif; ?>
                    </label>
                </div>
                <button class="btn primary" type="submit"><?= htmlspecialchars(t('admin.save'), ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </section>
    </div>
</body>
</html>
