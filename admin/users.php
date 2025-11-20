<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$lang = currentLang();

$user = currentUser();
$users = [];
$editing = null;
$error = null;
$success = null;

// Get all users
try {
    $result = query('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC');
    if ($result) {
        $users = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = t('admin.error_fetching_users');
}

// Handle edit request
if (isset($_GET['id'])) {
    $stmt = query('SELECT * FROM users WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $userId = $_POST['id'] ?? null;
        
        // Validate role
        if (!in_array($role, ['user', 'admin'], true)) {
            $role = 'user';
        }
        
        // Validate username
        if (empty($username) || !filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $error = t('admin.invalid_username');
        } else {
            try {
                if ($action === 'create') {
                    // Create new user
                    if (empty($password)) {
                        $error = t('admin.password_required');
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        query(
                            'INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)',
                            [$username, $passwordHash, $role]
                        );
                        $success = t('admin.user_created');
                        header('Location: users.php?lang=' . currentLang());
                        exit;
                    }
                } else {
                    // Update existing user
                    $updateData = [$role, $username];
                    $updateSql = 'UPDATE users SET role = ?, username = ?';
                    
                    // Update password only if provided
                    if (!empty($password)) {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $updateSql .= ', password_hash = ?';
                        $updateData[] = $passwordHash;
                    }
                    
                    $updateSql .= ' WHERE id = ?';
                    $updateData[] = (int) $userId;
                    
                    query($updateSql, $updateData);
                    $success = t('admin.user_updated');
                    header('Location: users.php');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        // Only admin can delete
        if (!canDelete()) {
                $error = t('admin.no_permission_delete');
        } else {
            $userId = (int) ($_POST['id'] ?? 0);
            $currentUserId = $user['id'] ?? 0;
            
            // Prevent self-deletion
            if ($userId === $currentUserId) {
                $error = t('admin.cannot_delete_self');
            } else {
                try {
                    query('DELETE FROM users WHERE id = ?', [$userId]);
                    $success = t('admin.user_deleted');
                    header('Location: users.php');
                    exit;
                } catch (Exception $e) {
                    $error = t('admin.error_delete') . ' ' . $e->getMessage();
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs | Musumba Steel</title>
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

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert.error {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .alert.success {
            background: #efe;
            color: #060;
            border: 1px solid #cfc;
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

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge.admin {
            background: var(--primary);
            color: #111;
        }

        .badge.user {
            background: #5d5d5d;
            color: white;
        }

        table a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-right: 1rem;
        }

        table a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-danger:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(244, 176, 0, 0.1);
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

        .form-note {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
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
                <h1><?= t('admin.users'); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= t('admin.manage_users'); ?></p>
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
            <a href="videos.php?lang=<?= $lang; ?>"><?= t('admin.videos'); ?></a>
            <a href="contacts.php?lang=<?= $lang; ?>"><?= t('admin.contacts'); ?></a>
            <a href="users.php?lang=<?= $lang; ?>" style="background: var(--primary); color: #111;"><?= t('admin.users'); ?></a>
            <a href="logout.php"><?= t('admin.logout'); ?></a>
        </nav>
    </div>

    <div class="admin-layout">
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <section class="dashboard-section">
            <h2><?= t('admin.users_list'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= t('admin.username_email'); ?></th>
                        <th><?= t('admin.role'); ?></th>
                        <th><?= t('admin.created_at'); ?></th>
                        <th><?= t('admin.actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999; padding: 2rem;">
                                <?= t('admin.no_users'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <span class="badge <?= $u['role']; ?>">
                                        <?= $u['role'] === 'admin' ? 'Admin' : 'User'; ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <a href="?lang=<?= $lang; ?>&id=<?= $u['id']; ?>"><?= t('admin.edit'); ?></a>
                                    <?php if (canDelete() && $u['id'] != $user['id']): ?>
                                        <form method="post" style="display: inline;" onsubmit="return confirm('<?= t('admin.confirm_delete_user'); ?>');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id']; ?>">
                                            <button type="submit" class="btn-danger"><?= t('admin.delete'); ?></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="dashboard-section">
            <h2><?= $editing ? t('admin.edit_user') : t('admin.new_user'); ?></h2>
            <form method="post">
                <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= $editing['id']; ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <label>
                        <?= t('admin.username_email'); ?>
                        <input type="email" name="username" value="<?= htmlspecialchars($editing['username'] ?? ''); ?>" required>
                    </label>
                    <label>
                        <?= t('admin.role'); ?>
                        <select name="role" required>
                            <option value="user" <?= (($editing['role'] ?? 'user') === 'user') ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?= (($editing['role'] ?? 'user') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </label>
                    <label class="form-full-width">
                        <?= t('admin.password'); ?>
                        <input type="password" name="password" <?= !$editing ? 'required' : ''; ?>>
                        <span class="form-note"><?= $editing ? t('admin.password_optional') : t('admin.password_required'); ?></span>
                    </label>
                </div>
                <button class="btn primary" type="submit"><?= $editing ? t('admin.update') : t('admin.create'); ?></button>
                <?php if ($editing): ?>
                    <a href="users.php?lang=<?= $lang; ?>" style="margin-left: 1rem; color: #666;"><?= t('form.cancel'); ?></a>
                <?php endif; ?>
            </form>
        </section>
    </div>
</body>

</html>

