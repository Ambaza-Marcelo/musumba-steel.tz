<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$lang = currentLang();

// Ensure uploads directory exists
$uploadsDir = __DIR__ . '/../uploads/pictures/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$pictures = getPictures();
$editing = null;

if (isset($_GET['id'])) {
    $stmt = query('SELECT * FROM pictures WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $pictureId = (int) $_GET['delete'];
    $pictureResult = query('SELECT image_path FROM pictures WHERE id = ?', [$pictureId]);
    if ($pictureResult && $picture = $pictureResult->fetch_assoc()) {
        // Delete file
        $filePath = __DIR__ . '/../' . $picture['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        // Delete from database
        query('DELETE FROM pictures WHERE id = ?', [$pictureId]);
        header('Location: pictures.php?lang=' . $lang);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;
    
    try {
        $payload = [
            $_POST['title_en'],
            $_POST['title_sw'],
            $_POST['description_en'] ?? '',
            $_POST['description_sw'] ?? '',
            $_POST['display_order'] ?? 0,
        ];

        if (!empty($_POST['id'])) {
            // Update existing picture
            $pictureId = (int) $_POST['id'];
            
            // Handle image upload if new image provided
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['image']['type'];
                $fileSize = $_FILES['image']['size'];
                $maxSize = 10 * 1024 * 1024; // 10MB
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception('Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
                }
                
                if ($fileSize > $maxSize) {
                    throw new Exception('File size exceeds 10MB limit.');
                }
                
                // Delete old image
                $oldResult = query('SELECT image_path FROM pictures WHERE id = ?', [$pictureId]);
                if ($oldResult && $old = $oldResult->fetch_assoc()) {
                    $oldPath = __DIR__ . '/../' . $old['image_path'];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                
                // Upload new image
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFilename = 'pic_' . $pictureId . '_' . time() . '.' . $extension;
                $uploadPath = $uploadsDir . $newFilename;
                $relativePath = 'uploads/pictures/' . $newFilename;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    throw new Exception('Failed to upload image.');
                }
                
                query(
                    'UPDATE pictures SET title_en=?, title_sw=?, description_en=?, description_sw=?, image_path=?, display_order=? WHERE id=?',
                    [...$payload, $relativePath, $pictureId]
                );
            } else {
                // Update without changing image
                query(
                    'UPDATE pictures SET title_en=?, title_sw=?, description_sw=?, description_en=?, display_order=? WHERE id=?',
                    [...$payload, $pictureId]
                );
            }
        } else {
            // Create new picture(s) - support multiple uploads
            if (empty($_FILES['images']['name'][0]) && empty($_FILES['image']['name'])) {
                throw new Exception('At least one image is required.');
            }
            
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 10 * 1024 * 1024; // 10MB
            
            // Handle multiple images upload
            $filesToProcess = [];
            if (!empty($_FILES['images']['name'][0])) {
                // Multiple files
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $filesToProcess[] = [
                            'name' => $filename,
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'size' => $_FILES['images']['size'][$key],
                        ];
                    }
                }
            } elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Single file (backward compatibility)
                $filesToProcess[] = [
                    'name' => $_FILES['image']['name'],
                    'tmp_name' => $_FILES['image']['tmp_name'],
                    'type' => $_FILES['image']['type'],
                    'size' => $_FILES['image']['size'],
                ];
            }
            
            if (empty($filesToProcess)) {
                throw new Exception('No valid images to upload.');
            }
            
            $uploadedCount = 0;
            $errors = [];
            
            foreach ($filesToProcess as $file) {
                if (!in_array($file['type'], $allowedTypes)) {
                    $errors[] = $file['name'] . ': Invalid file type. Only JPG, PNG, GIF, WEBP allowed.';
                    continue;
                }
                
                if ($file['size'] > $maxSize) {
                    $errors[] = $file['name'] . ': File size exceeds 10MB limit.';
                    continue;
                }
                
                // Insert to get ID
                query(
                    'INSERT INTO pictures (title_en, title_sw, description_en, description_sw, display_order, image_path) VALUES (?,?,?,?,?,"")',
                    $payload
                );
                $connection = db();
                $pictureId = $connection->insert_id;
                
                // Upload image
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFilename = 'pic_' . $pictureId . '_' . time() . '_' . $uploadedCount . '.' . $extension;
                $uploadPath = $uploadsDir . $newFilename;
                $relativePath = 'uploads/pictures/' . $newFilename;
                
                if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    // Update with image path
                    query('UPDATE pictures SET image_path=? WHERE id=?', [$relativePath, $pictureId]);
                    $uploadedCount++;
                } else {
                    query('DELETE FROM pictures WHERE id = ?', [$pictureId]);
                    $errors[] = $file['name'] . ': Failed to upload.';
                }
            }
            
            if ($uploadedCount === 0) {
                throw new Exception('Failed to upload images. ' . implode(' ', $errors));
            }
            
            if (!empty($errors)) {
                $error = 'Some images failed to upload: ' . implode(' ', $errors);
            }
        }

        header('Location: pictures.php?lang=' . $lang);
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
    <title><?= t('admin.pictures'); ?> | Musumba Steel</title>
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

        .pictures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .picture-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .picture-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .picture-item .info {
            padding: 1rem;
        }

        .picture-item .title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .picture-item .actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .picture-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .picture-item .delete {
            color: #dc3545;
        }

        .picture-item .delete:hover {
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
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        input[type="file"] {
            padding: 0.5rem;
        }

        input:focus,
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

        .preview-img {
            max-width: 300px;
            max-height: 300px;
            margin-top: 0.5rem;
            border-radius: 6px;
            border: 1px solid #ddd;
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
                <h1><?= t('admin.pictures'); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= t('admin.manage_pictures'); ?></p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="language-switch" style="display: flex; gap: 0.5rem;">
                    <a class="<?= $lang === 'en' ? 'active' : ''; ?>" href="?lang=en<?= isset($_GET['id']) ? '&id=' . urlencode($_GET['id']) : ''; ?>" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'en' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px; font-size: 0.9rem;">EN</a>
                    <a class="<?= $lang === 'sw' ? 'active' : ''; ?>" href="?lang=sw<?= isset($_GET['id']) ? '&id=' . urlencode($_GET['id']) : ''; ?>" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'sw' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px; font-size: 0.9rem;">SW</a>
                </div>
                <a href="../index.php?lang=<?= $lang; ?>" style="color: white; text-decoration: none; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.1); border-radius: 6px;"><?= t('admin.view_site'); ?></a>
            </div>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php?lang=<?= $lang; ?>"><?= t('admin.dashboard'); ?></a>
            <a href="pages.php?lang=<?= $lang; ?>"><?= t('admin.pages'); ?></a>
            <a href="services.php?lang=<?= $lang; ?>"><?= t('admin.services'); ?></a>
            <a href="projects.php?lang=<?= $lang; ?>"><?= t('admin.projects'); ?></a>
            <a href="publications.php?lang=<?= $lang; ?>"><?= t('admin.publications'); ?></a>
            <a href="pictures.php?lang=<?= $lang; ?>" style="background: var(--primary); color: #111;"><?= t('admin.pictures'); ?></a>
            <a href="videos.php?lang=<?= $lang; ?>"><?= t('admin.videos'); ?></a>
            <a href="contacts.php?lang=<?= $lang; ?>"><?= t('admin.contacts'); ?></a>
            <a href="users.php?lang=<?= $lang; ?>"><?= t('admin.users'); ?></a>
            <a href="logout.php"><?= t('admin.logout'); ?></a>
        </nav>
    </div>

    <div class="admin-layout">
        <?php if (isset($error)): ?>
            <div style="background: #fee; color: #c00; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <section class="dashboard-section">
            <h2><?= t('admin.pictures_list'); ?></h2>
            <?php if (empty($pictures)): ?>
                <p style="color: #999; padding: 2rem; text-align: center;"><?= t('admin.no_pictures'); ?></p>
            <?php else: ?>
                <div class="pictures-grid">
                    <?php foreach ($pictures as $pic): ?>
                        <div class="picture-item">
                            <img src="../<?= htmlspecialchars($pic['image_path']); ?>" alt="<?= htmlspecialchars($pic['title_en']); ?>">
                            <div class="info">
                                <div class="title"><?= htmlspecialchars($pic['title_en']); ?></div>
                                <div class="actions">
                                    <a href="?lang=<?= $lang; ?>&id=<?= $pic['id']; ?>"><?= t('admin.edit'); ?></a>
                                    <a href="?lang=<?= $lang; ?>&delete=<?= $pic['id']; ?>" class="delete" onclick="return confirm('<?= t('admin.delete_image'); ?>');"><?= t('admin.delete'); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <h2><?= $editing ? t('admin.edit_picture') : t('admin.new_picture'); ?></h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $editing['id'] ?? ''; ?>">
                <div class="form-grid">
                    <?php if (!$editing): ?>
                        <p style="grid-column: 1 / -1; background: #e3f2fd; padding: 1rem; border-radius: 6px; color: #1565c0; font-size: 0.95rem;">
                            <strong>💡 Tip:</strong> You can select multiple images at once. Each image will be saved with the same title and description.
                        </p>
                    <?php endif; ?>
                    <label>
                        <?= t('admin.title_en'); ?>
                        <input type="text" name="title_en" value="<?= htmlspecialchars($editing['title_en'] ?? ''); ?>" <?= $editing ? 'required' : ''; ?>>
                        <?php if (!$editing): ?>
                            <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">Will be applied to all selected images</span>
                        <?php endif; ?>
                    </label>
                    <label>
                        <?= t('admin.title_sw'); ?>
                        <input type="text" name="title_sw" value="<?= htmlspecialchars($editing['title_sw'] ?? ''); ?>" <?= $editing ? 'required' : ''; ?>>
                        <?php if (!$editing): ?>
                            <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">Will be applied to all selected images</span>
                        <?php endif; ?>
                    </label>
                    <label class="form-full-width">
                        <?= t('admin.description_en'); ?>
                        <textarea name="description_en"><?= htmlspecialchars($editing['description_en'] ?? ''); ?></textarea>
                        <?php if (!$editing): ?>
                            <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">Will be applied to all selected images</span>
                        <?php endif; ?>
                    </label>
                    <label class="form-full-width">
                        <?= t('admin.description_sw'); ?>
                        <textarea name="description_sw"><?= htmlspecialchars($editing['description_sw'] ?? ''); ?></textarea>
                        <?php if (!$editing): ?>
                            <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">Will be applied to all selected images</span>
                        <?php endif; ?>
                    </label>
                    <label class="form-full-width">
                        <?= t('admin.image'); ?> <?= !$editing ? '(You can select multiple images)' : ''; ?>
                        <input type="file" name="images[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" <?= !$editing ? 'required multiple' : ''; ?>>
                        <?php if ($editing): ?>
                            <input type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" style="margin-top: 0.5rem;">
                        <?php endif; ?>
                        <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">
                            <?= t('admin.images_accepted'); ?> <?= !$editing ? 'You can select multiple images at once (hold Ctrl/Cmd to select multiple).' : 'Leave empty to keep current image.'; ?>
                        </span>
                        <?php if ($editing && !empty($editing['image_path'])): ?>
                            <img src="../<?= htmlspecialchars($editing['image_path']); ?>" alt="Current" class="preview-img">
                        <?php endif; ?>
                    </label>
                    <label>
                        <?= t('admin.display_order'); ?>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($editing['display_order'] ?? 0); ?>" min="0">
                        <?php if (!$editing): ?>
                            <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">Starting order for uploaded images</span>
                        <?php endif; ?>
                    </label>
                </div>
                <button class="btn primary" type="submit"><?= t('admin.save'); ?></button>
            </form>
        </section>
    </div>
</body>

</html>

