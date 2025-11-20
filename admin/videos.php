<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$lang = currentLang();

/**
 * Normalize a YouTube ID or URL into the 11-character video ID.
 */
function normalizeYoutubeId(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $value, $matches)) {
        return $matches[1];
    }

    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) {
        return $value;
    }

    return null;
}

$videos = getVideos();
$editing = null;

if (isset($_GET['id'])) {
    $stmt = query('SELECT * FROM videos WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $videoId = (int) $_GET['delete'];
    query('DELETE FROM videos WHERE id = ?', [$videoId]);
    header('Location: videos.php?lang=' . $lang);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = null;
    
    try {
        $youtubeIdInput = trim($_POST['youtube_id'] ?? '');
        $baseOrder = (int) ($_POST['display_order'] ?? 0);
        
        if (empty($_POST['id']) && $youtubeIdInput === '') {
            throw new Exception('YouTube Video ID or URL is required.');
        }

        if (!empty($_POST['id'])) {
            // Update existing video
            $videoId = (int) $_POST['id'];
            $youtubeId = normalizeYoutubeId($youtubeIdInput);
            if ($youtubeId === null) {
                throw new Exception('Invalid YouTube ID or URL format.');
            }

            query(
                'UPDATE videos SET youtube_id=?, display_order=? WHERE id=?',
                [$youtubeId, $baseOrder, $videoId]
            );
        } else {
            // Create new video(s)
            $youtubeIds = [];
            $errors = [];
            
            $ids = preg_split('/[,\n\r]+/', $youtubeIdInput);
            foreach ($ids as $id) {
                $normalized = normalizeYoutubeId($id);
                if ($normalized) {
                    $youtubeIds[] = $normalized;
                } else {
                    $errors[] = trim($id) . ': invalid YouTube ID or URL.';
                }
            }
            
            if (empty($youtubeIds)) {
                throw new Exception('No valid YouTube IDs found. ' . implode(' ', $errors));
            }
            
            $youtubeCount = 0;
            foreach ($youtubeIds as $ytId) {
                query(
                    'INSERT INTO videos (youtube_id, display_order) VALUES (?, ?)',
                    [$ytId, $baseOrder + $youtubeCount]
                );
                $youtubeCount++;
            }
            
            if ($youtubeCount === 0) {
                throw new Exception('Failed to create videos. ' . implode(' ', $errors));
            }
            
            if (!empty($errors)) {
                $error = 'Some videos failed: ' . implode(' ', $errors);
            }
        }

        header('Location: videos.php?lang=' . $lang);
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
    <title><?= t('admin.videos'); ?> | Musumba Steel</title>
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

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .video-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .video-item .video-preview {
            width: 100%;
            height: 200px;
            background: #000;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-item .video-preview iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .video-item .video-preview video {
            width: 100%;
            height: 100%;
        }

        .video-item .info {
            padding: 1rem;
        }

        .video-item .title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .video-item .type-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: var(--primary);
            color: #111;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .video-item .actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .video-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .video-item .delete {
            color: #dc3545;
        }

        .video-item .delete:hover {
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

        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
            display: block;
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
                <h1><?= t('admin.videos'); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= t('admin.manage_videos'); ?></p>
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
            <a href="pictures.php?lang=<?= $lang; ?>"><?= t('admin.pictures'); ?></a>
            <a href="videos.php?lang=<?= $lang; ?>" style="background: var(--primary); color: #111;"><?= t('admin.videos'); ?></a>
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
            <h2><?= t('admin.videos_list'); ?></h2>
            <?php if (empty($videos)): ?>
                <p style="color: #999; padding: 2rem; text-align: center;"><?= t('admin.no_videos'); ?></p>
            <?php else: ?>
                <div class="videos-grid">
                    <?php foreach ($videos as $video): ?>
                        <div class="video-item">
                            <div class="video-preview">
                                <?php if (!empty($video['youtube_id'])): ?>
                                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($video['youtube_id']); ?>" allowfullscreen></iframe>
                                <?php else: ?>
                                    <div style="color: white; text-align: center;">No preview available</div>
                                <?php endif; ?>
                            </div>
                            <div class="info">
                                <span class="type-badge">YouTube</span>
                                <p style="margin: 0; color: #666;">ID: <?= htmlspecialchars($video['youtube_id']); ?></p>
                                <p style="margin: 0.25rem 0 0; font-size: 0.9rem; color: #999;">Display order: <?= (int) $video['display_order']; ?></p>
                                <div class="actions">
                                    <a href="?lang=<?= $lang; ?>&id=<?= $video['id']; ?>"><?= t('admin.edit'); ?></a>
                                    <a href="?lang=<?= $lang; ?>&delete=<?= $video['id']; ?>" class="delete" onclick="return confirm('<?= t('admin.confirm_delete_user'); ?>');"><?= t('admin.delete'); ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <h2><?= $editing ? t('admin.edit_video') : t('admin.new_video'); ?></h2>
        <section class="dashboard-section">
            <h2><?= $editing ? t('admin.edit_video') : t('admin.new_video'); ?></h2>
            <form method="post">
                <input type="hidden" name="id" value="<?= $editing['id'] ?? ''; ?>">
                <div class="form-grid">
                    <?php if (!$editing): ?>
                        <p style="grid-column: 1 / -1; background: #e3f2fd; padding: 1rem; border-radius: 6px; color: #1565c0; font-size: 0.95rem;">
                            <strong>💡 Tip:</strong> Add multiple YouTube videos at once by:
                            <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                                <li>Adding one ID per line</li>
                                <li>Or separating IDs with commas</li>
                                <li>You can also paste full YouTube URLs (they'll be converted automatically)</li>
                            </ul>
                        </p>
                    <?php endif; ?>
                    <label class="form-full-width">
                        <?= t('admin.youtube_id'); ?> <?= !$editing ? '(multiple IDs supported)' : ''; ?> <span style="color: #c00;">*</span>
                        <?php if (!$editing): ?>
                            <textarea name="youtube_id" rows="6" placeholder="https://www.youtube.com/watch?v=dQw4w9WgXcQ&#10;https://youtu.be/abc123xyz45&#10;dQw4w9WgXcQ, abc123xyz45" required></textarea>
                        <?php else: ?>
                            <input type="text" name="youtube_id" value="<?= htmlspecialchars($editing['youtube_id'] ?? ''); ?>" placeholder="dQw4w9WgXcQ or full URL" required>
                        <?php endif; ?>
                        <span class="help-text"><?= t('admin.youtube_id_help'); ?></span>
                    </label>
                    <label>
                        <?= t('admin.display_order'); ?>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($editing['display_order'] ?? 0); ?>" min="0">
                        <?php if (!$editing): ?>
                            <span style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; display: block;">Starting order applied to the uploaded videos</span>
                        <?php endif; ?>
                    </label>
                </div>
                <button class="btn primary" type="submit"><?= t('admin.save'); ?></button>
            </form>
        </section>
    </div>
</body>

</html>

