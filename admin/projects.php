<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$lang = currentLang();

$projects = getProjects();
$editing = null;

if (isset($_GET['id'])) {
    $stmt = query('SELECT * FROM projects WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        $_POST['title_en'],
        $_POST['title_sw'],
        $_POST['summary_en'],
        $_POST['summary_sw'],
        $_POST['location'],
        $_POST['status'],
        $_POST['launched_on'],
    ];

    if (!empty($_POST['id'])) {
        query(
            'UPDATE projects SET title_en=?, title_sw=?, summary_en=?, summary_sw=?, location=?, status=?, launched_on=? WHERE id=?',
            [...$payload, (int) $_POST['id']]
        );
    } else {
        query(
            'INSERT INTO projects (title_en, title_sw, summary_en, summary_sw, location, status, launched_on) VALUES (?,?,?,?,?,?,?)',
            $payload
        );
    }

    header('Location: projects.php?lang=' . currentLang());
    exit;
}

?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin.projects'); ?> | Musumba Steel</title>
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
                <h1><?= t('admin.projects'); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= t('admin.manage_projects'); ?></p>
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
            <a href="projects.php?lang=<?= $lang; ?>" style="background: var(--primary); color: #111;"><?= t('admin.projects'); ?></a>
            <a href="publications.php?lang=<?= $lang; ?>"><?= t('admin.publications'); ?></a>
            <a href="pictures.php?lang=<?= $lang; ?>"><?= t('admin.pictures'); ?></a>
            <a href="videos.php?lang=<?= $lang; ?>"><?= t('admin.videos'); ?></a>
            <a href="contacts.php?lang=<?= $lang; ?>"><?= t('admin.contacts'); ?></a>
            <a href="users.php?lang=<?= $lang; ?>"><?= t('admin.users'); ?></a>
            <a href="logout.php"><?= t('admin.logout'); ?></a>
        </nav>
    </div>

    <div class="admin-layout">
        <section class="dashboard-section">
            <h2><?= t('admin.projects_list'); ?></h2>
        <table>
            <thead>
                <tr>
                        <th><?= t('admin.title'); ?></th>
                        <th><?= t('admin.status'); ?></th>
                    <th><?= t('admin.date'); ?></th>
                        <th><?= t('admin.actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999; padding: 2rem;">
                                <?= t('admin.no_projects'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= htmlspecialchars($project['title_en']); ?></td>
                        <td><?= htmlspecialchars($project['status']); ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($project['launched_on']))); ?></td>
                                <td><a href="?lang=<?= $lang; ?>&id=<?= $project['id']; ?>"><?= t('admin.edit'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
                    <?php endif; ?>
            </tbody>
        </table>
        </section>

        <section class="dashboard-section">
            <h2><?= $editing ? t('admin.edit_project') : t('admin.new_project'); ?></h2>
        <form method="post">
            <input type="hidden" name="id" value="<?= $editing['id'] ?? ''; ?>">
                <div class="form-grid">
            <label>
                        <?= t('admin.title_en'); ?>
                        <input type="text" name="title_en" value="<?= htmlspecialchars($editing['title_en'] ?? ''); ?>" required>
            </label>
            <label>
                        <?= t('admin.title_sw'); ?>
                        <input type="text" name="title_sw" value="<?= htmlspecialchars($editing['title_sw'] ?? ''); ?>" required>
            </label>
                    <label class="form-full-width">
                        <?= t('admin.summary_en'); ?>
                <textarea name="summary_en"><?= htmlspecialchars($editing['summary_en'] ?? ''); ?></textarea>
            </label>
                    <label class="form-full-width">
                        <?= t('admin.summary_sw'); ?>
                <textarea name="summary_sw"><?= htmlspecialchars($editing['summary_sw'] ?? ''); ?></textarea>
            </label>
            <label>
                        <?= t('admin.location'); ?>
                        <input type="text" name="location" value="<?= htmlspecialchars($editing['location'] ?? ''); ?>" required>
            </label>
            <label>
                        <?= t('admin.status'); ?>
                <select name="status">
                    <?php
                    $statuses = ['Pipeline', 'Ongoing', 'Completed'];
                    foreach ($statuses as $status):
                    ?>
                        <option value="<?= $status; ?>" <?= (($editing['status'] ?? '') === $status) ? 'selected' : ''; ?>><?= $status; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                        <?= t('admin.launch_date'); ?>
                <input type="date" name="launched_on" value="<?= htmlspecialchars($editing['launched_on'] ?? date('Y-m-d')); ?>">
            </label>
                </div>
                <button class="btn primary" type="submit"><?= t('admin.save'); ?></button>
        </form>
        </section>
    </div>
</body>

</html>

