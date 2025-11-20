<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$lang = currentLang();

// Ensure uploads directory exists
$uploadsDir = __DIR__ . '/../uploads/publications/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$typeFilter = $_GET['type'] ?? null;
$publications = getPublications($typeFilter);
$editing = null;
$editingImages = [];

if (isset($_GET['id'])) {
    $stmt = query('SELECT * FROM publications WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
    
    if ($editing) {
        // Get existing images
        $imagesResult = query('SELECT * FROM publication_images WHERE publication_id = ? ORDER BY image_order, id', [(int) $_GET['id']]);
        if ($imagesResult) {
            $editingImages = $imagesResult->fetch_all(MYSQLI_ASSOC);
        }
    }
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $imageId = (int) $_GET['delete_image'];
    $imageResult = query('SELECT image_path, publication_id FROM publication_images WHERE id = ?', [$imageId]);
    if ($imageResult && $image = $imageResult->fetch_assoc()) {
        // Delete file
        $filePath = __DIR__ . '/../' . $image['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        // Delete from database
        query('DELETE FROM publication_images WHERE id = ?', [$imageId]);
        header('Location: publications.php?lang=' . currentLang() . '&id=' . $image['publication_id']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;
    
    try {
    $payload = [
        $_POST['type'],
        $_POST['title_en'],
        $_POST['title_sw'],
        $_POST['body_en'],
        $_POST['body_sw'],
        $_POST['published_on'],
        $_POST['attachment'] ?? null,
    ];

    if (!empty($_POST['id'])) {
        query(
            'UPDATE publications SET type=?, title_en=?, title_sw=?, body_en=?, body_sw=?, published_on=?, attachment=? WHERE id=?',
            [...$payload, (int) $_POST['id']]
        );
            $publicationId = (int) $_POST['id'];
    } else {
        query(
            'INSERT INTO publications (type, title_en, title_sw, body_en, body_sw, published_on, attachment) VALUES (?,?,?,?,?,?,?)',
            $payload
        );
            $connection = db();
            $publicationId = $connection->insert_id;
        }

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            foreach ($_FILES['images']['name'] as $key => $filename) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$key];
                    $fileType = $_FILES['images']['type'][$key];
                    $fileSize = $_FILES['images']['size'][$key];
                    
                    if (!in_array($fileType, $allowedTypes)) {
                        continue; // Skip invalid file types
                    }
                    
                    if ($fileSize > $maxSize) {
                        continue; // Skip files that are too large
                    }
                    
                    // Generate unique filename
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $newFilename = 'pub_' . $publicationId . '_' . time() . '_' . $key . '.' . $extension;
                    $uploadPath = $uploadsDir . $newFilename;
                    $relativePath = 'uploads/publications/' . $newFilename;
                    
                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        // Get max order for this publication
                        $orderResult = query('SELECT MAX(image_order) as max_order FROM publication_images WHERE publication_id = ?', [$publicationId]);
                        $maxOrder = 0;
                        if ($orderResult && $row = $orderResult->fetch_assoc()) {
                            $maxOrder = (int) ($row['max_order'] ?? 0);
                        }
                        
                        query(
                            'INSERT INTO publication_images (publication_id, image_path, image_order) VALUES (?, ?, ?)',
                            [$publicationId, $relativePath, $maxOrder + 1]
                        );
                    }
                }
            }
        }

        header('Location: publications.php?lang=' . currentLang());
    exit;
    } catch (Exception $e) {
        $error = t('admin.error_occurred') . ' ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publications | Musumba Steel</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Montserrat', sans-serif;
        }

        .admin-header {
            background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .admin-header h1 {
            margin: 0 0 1rem 0;
            font-size: 1.8rem;
        }

        .admin-header .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .admin-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .admin-nav a {
            padding: 0.6rem 1.2rem;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        .admin-nav a:hover {
            background: var(--primary);
            color: #111;
        }

        .admin-layout {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem 2rem;
        }

        .dashboard-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .dashboard-section h2 {
            margin: 0 0 1rem 0;
            color: #333;
            font-size: 1.3rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
        }

        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-section label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            color: #333;
        }

        .filter-section select {
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            background: white;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        table thead {
            background: #f8f8f8;
        }

        table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }

        table tbody tr:hover {
            background: #fafafa;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        table a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        table a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(244, 176, 0, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 0.9rem 1.4rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            transition: background 0.2s ease;
        }

        .btn.primary {
            background: var(--primary);
            color: #111;
        }

        .btn.primary:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .admin-layout {
                padding: 0 1rem 1rem;
            }

            .admin-header {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <div class="user-info">
            <div>
                <h1><?= t('admin.publications'); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= t('admin.manage_publications'); ?></p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="language-switch" style="display: flex; gap: 0.5rem;">
                    <a class="<?= $lang === 'en' ? 'active' : ''; ?>" href="?lang=en<?= isset($_GET['id']) ? '&id=' . urlencode($_GET['id']) : ''; ?><?= isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'en' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px; font-size: 0.9rem;">EN</a>
                    <a class="<?= $lang === 'sw' ? 'active' : ''; ?>" href="?lang=sw<?= isset($_GET['id']) ? '&id=' . urlencode($_GET['id']) : ''; ?><?= isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'sw' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px; font-size: 0.9rem;">SW</a>
                </div>
                <a href="../index.php?lang=<?= $lang; ?>" style="color: white; text-decoration: none; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.1); border-radius: 6px;"><?= t('admin.view_site'); ?></a>
            </div>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php?lang=<?= $lang; ?>"><?= t('admin.dashboard'); ?></a>
            <a href="pages.php?lang=<?= $lang; ?>"><?= t('admin.pages'); ?></a>
            <a href="services.php?lang=<?= $lang; ?>"><?= t('admin.services'); ?></a>
            <a href="projects.php?lang=<?= $lang; ?>"><?= t('admin.projects'); ?></a>
            <a href="publications.php?lang=<?= $lang; ?>" style="background: var(--primary); color: #111;"><?= t('admin.publications'); ?></a>
            <a href="pictures.php?lang=<?= $lang; ?>"><?= t('admin.pictures'); ?></a>
            <a href="videos.php?lang=<?= $lang; ?>"><?= t('admin.videos'); ?></a>
            <a href="contacts.php?lang=<?= $lang; ?>"><?= t('admin.contacts'); ?></a>
            <a href="users.php?lang=<?= $lang; ?>"><?= t('admin.users'); ?></a>
            <a href="logout.php"><?= t('admin.logout'); ?></a>
        </nav>
    </div>

    <div class="admin-layout">

        <section class="dashboard-section">
            <h2><?= t('admin.publications_list'); ?></h2>
            <div class="filter-section">
                <form method="get">
                    <input type="hidden" name="lang" value="<?= $lang; ?>">
                    <label>
                        <?= t('admin.filter_by_type'); ?>
                <select name="type" onchange="this.form.submit()">
                            <option value=""><?= t('admin.all'); ?></option>
                    <?php
                    $types = ['news', 'training', 'national-holidays', 'international-holidays', 'calls-for-tenders', 'communicates'];
                    foreach ($types as $type):
                    ?>
                        <option value="<?= $type; ?>" <?= $type === $typeFilter ? 'selected' : ''; ?>><?= ucfirst(str_replace('-', ' ', $type)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
            </div>

        <table>
            <thead>
                <tr>
                    <th><?= t('admin.type'); ?></th>
                        <th><?= t('admin.title'); ?></th>
                    <th><?= t('admin.date'); ?></th>
                        <th><?= t('admin.actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                    <?php if (empty($publications)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999; padding: 2rem;">
                                <?= t('admin.no_publications'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                <?php foreach ($publications as $pub): ?>
                    <tr>
                                <td><?= htmlspecialchars(ucfirst(str_replace('-', ' ', $pub['type']))); ?></td>
                        <td><?= htmlspecialchars($pub['title_en']); ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($pub['published_on']))); ?></td>
                                <td><a href="?lang=<?= $lang; ?>&id=<?= $pub['id']; ?>"><?= t('admin.edit'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
                    <?php endif; ?>
            </tbody>
        </table>
        </section>

        <section class="dashboard-section">
            <h2><?= $editing ? t('admin.edit_publication') : t('admin.new_publication'); ?></h2>
            <?php if (isset($error)): ?>
                <div style="background: #fee; color: #c00; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $editing['id'] ?? ''; ?>">
                <div class="form-grid">
            <label>
                <?= t('admin.type'); ?>
                <select name="type" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type; ?>" <?= (($editing['type'] ?? '') === $type) ? 'selected' : ''; ?>><?= ucfirst(str_replace('-', ' ', $type)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                        <?= t('admin.title_en'); ?>
                        <input type="text" name="title_en" value="<?= htmlspecialchars($editing['title_en'] ?? ''); ?>" required>
            </label>
            <label>
                        <?= t('admin.title_sw'); ?>
                        <input type="text" name="title_sw" value="<?= htmlspecialchars($editing['title_sw'] ?? ''); ?>" required>
            </label>
                    <label class="form-full-width">
                        <?= t('admin.body_en'); ?>
                <textarea name="body_en"><?= htmlspecialchars($editing['body_en'] ?? ''); ?></textarea>
            </label>
                    <label class="form-full-width">
                        <?= t('admin.body_sw'); ?>
                <textarea name="body_sw"><?= htmlspecialchars($editing['body_sw'] ?? ''); ?></textarea>
            </label>
            <label>
                        <?= t('admin.publish_date'); ?>
                <input type="date" name="published_on" value="<?= htmlspecialchars($editing['published_on'] ?? date('Y-m-d')); ?>">
            </label>
            <label>
                        <?= t('admin.attachment_url'); ?>
                        <input type="text" name="attachment" value="<?= htmlspecialchars($editing['attachment'] ?? ''); ?>" placeholder="https://...">
                    </label>
                    <label class="form-full-width">
                        <?= t('admin.images'); ?>
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">
                            <?= t('admin.images_accepted'); ?>
                        </span>
            </label>
                </div>
                
                <?php if (!empty($editingImages)): ?>
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e0e0e0;">
                        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;"><?= t('admin.existing_images'); ?></h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                            <?php foreach ($editingImages as $img): ?>
                                <div style="position: relative; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;">
                                    <img src="../<?= htmlspecialchars($img['image_path']); ?>" alt="Image" style="width: 100%; height: 150px; object-fit: cover; display: block;">
                                    <a href="?lang=<?= $lang; ?>&delete_image=<?= $img['id']; ?>" onclick="return confirm('<?= t('admin.delete_image'); ?>');" style="position: absolute; top: 0.5rem; right: 0.5rem; background: #dc3545; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">×</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <button class="btn primary" type="submit" style="margin-top: 1.5rem;"><?= t('admin.save'); ?></button>
        </form>
        </section>
    </div>
</body>

</html>

