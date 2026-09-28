<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$adminNavActive = 'users';

$user = currentUser() ?: ['id' => 0, 'username' => '', 'role' => 'admin'];
$users = [];
$editing = null;
$error = null;
$success = null;

$users = adminFetchAll('SELECT id, username, role, created_at FROM users ORDER BY created_at DESC');

foreach ($users as &$row) {
    $r = strtolower(trim((string) ($row['role'] ?? '')));
    $row['role'] = $r === 'user' ? 'user' : 'admin';
}
unset($row);

if (isset($_GET['id'])) {
    $editing = adminFetchOne('SELECT * FROM users WHERE id = ?', [(int) $_GET['id']]);
    if ($editing) {
        $r = strtolower(trim((string) ($editing['role'] ?? '')));
        $editing['role'] = $r === 'user' ? 'user' : 'admin';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? 'user');
        $userId = (int) ($_POST['id'] ?? 0);

        if (!in_array($role, ['user', 'admin'], true)) {
            $role = 'user';
        }

        if ($username === '' || !filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $error = t('admin.invalid_username');
        } else {
            try {
                if ($action === 'create') {
                    if ($password === '') {
                        $error = t('admin.password_required');
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        query(
                            'INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)',
                            [$username, $passwordHash, $role]
                        );
                        header('Location: users.php?lang=' . rawurlencode(currentLang()) . '&ok=1');
                        exit;
                    }
                } else {
                    if ($userId < 1) {
                        throw new InvalidArgumentException('Invalid user id');
                    }
                    if ($password !== '') {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        query(
                            'UPDATE users SET role = ?, username = ?, password_hash = ? WHERE id = ?',
                            [$role, $username, $passwordHash, $userId]
                        );
                    } else {
                        query(
                            'UPDATE users SET role = ?, username = ? WHERE id = ?',
                            [$role, $username, $userId]
                        );
                    }
                    header('Location: users.php?lang=' . rawurlencode(currentLang()) . '&ok=1');
                    exit;
                }
            } catch (Throwable $e) {
                $error = 'Erreur: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        if (!canDelete()) {
            $error = t('admin.no_permission_delete');
        } else {
            $userId = (int) ($_POST['id'] ?? 0);
            $currentUserId = (int) ($user['id'] ?? 0);

            if ($userId < 1) {
                $error = t('admin.error_delete');
            } elseif ($userId === $currentUserId) {
                $error = t('admin.cannot_delete_self');
            } else {
                try {
                    query('DELETE FROM users WHERE id = ?', [$userId]);
                    header('Location: users.php?lang=' . rawurlencode(currentLang()) . '&deleted=1');
                    exit;
                } catch (Throwable $e) {
                    $error = t('admin.error_delete') . ' ' . $e->getMessage();
                }
            }
        }
    }
}

if (isset($_GET['ok'])) {
    $success = t('admin.user_updated');
}
if (isset($_GET['deleted'])) {
    $success = t('admin.user_deleted');
}

?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('admin.users'), ENT_QUOTES, 'UTF-8'); ?> | Musumba Steel</title>
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
            border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 2rem;
        }
        .dashboard-section h2 {
            margin: 0 0 1rem 0; color: #333; font-size: 1.3rem;
            border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;
        }
        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert.error { background: #fee; color: #c00; border: 1px solid #fcc; }
        .alert.success { background: #efe; color: #060; border: 1px solid #cfc; }
        table { width: 100%; border-collapse: collapse; }
        table thead { background: #f8f8f8; }
        table th, table td { padding: 1rem; text-align: left; border-bottom: 1px solid #f0f0f0; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; }
        .badge.admin { background: var(--primary); color: #111; }
        .badge.user { background: #5d5d5d; color: white; }
        table a { color: var(--primary); text-decoration: none; font-weight: 500; margin-right: 1rem; }
        .btn-danger {
            background: #dc3545; color: white; border: none; padding: 0.5rem 1rem;
            border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 0.9rem;
        }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        input, select {
            width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;
            font-size: 0.95rem; font-family: inherit; box-sizing: border-box;
        }
        .btn { padding: 0.9rem 1.4rem; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; font-size: 0.95rem; }
        .btn.primary { background: var(--primary); color: #111; }
        .form-note { font-size: 0.85rem; color: #666; margin-top: 0.25rem; }
        .role-pill { font-size: 0.75rem; margin-left: 0.5rem; background: rgba(255,255,255,.15); padding: .2rem .55rem; border-radius: 999px; }
    </style>
</head>

<body>
    <div class="admin-header">
        <div class="user-info">
            <div>
                <h1><?= htmlspecialchars(t('admin.users'), ENT_QUOTES, 'UTF-8'); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= htmlspecialchars(t('admin.manage_users'), ENT_QUOTES, 'UTF-8'); ?>
                    <span class="role-pill"><?= htmlspecialchars(ucfirst((string) ($user['role'] ?? 'admin')), ENT_QUOTES, 'UTF-8'); ?></span>
                </p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="display: flex; gap: 0.5rem;">
                    <a href="?lang=en<?= isset($_GET['id']) ? '&id=' . (int) $_GET['id'] : ''; ?>" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'en' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px;">EN</a>
                    <a href="?lang=sw<?= isset($_GET['id']) ? '&id=' . (int) $_GET['id'] : ''; ?>" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'sw' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px;">SW</a>
                </div>
                <a href="../index.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" style="color: white; text-decoration: none; padding: 0.6rem 1.2rem; background: rgba(255,255,255,0.1); border-radius: 6px;"><?= htmlspecialchars(t('admin.view_site'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
        <?php include __DIR__ . '/includes/nav.php'; ?>
    </div>

    <div class="admin-layout">
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <section class="dashboard-section">
            <h2><?= htmlspecialchars(t('admin.users_list'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th><?= htmlspecialchars(t('admin.username_email'), ENT_QUOTES, 'UTF-8'); ?></th>
                        <th><?= htmlspecialchars(t('admin.role'), ENT_QUOTES, 'UTF-8'); ?></th>
                        <th><?= htmlspecialchars(t('admin.created_at'), ENT_QUOTES, 'UTF-8'); ?></th>
                        <th><?= htmlspecialchars(t('admin.actions'), ENT_QUOTES, 'UTF-8'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$users): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999; padding: 2rem;">
                                <?= htmlspecialchars(t('admin.no_users'), ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <?php $role = (($u['role'] ?? '') === 'user') ? 'user' : 'admin'; ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $u['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="badge <?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= $role === 'admin' ? 'Admin' : 'User'; ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) ($u['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>&id=<?= (int) $u['id']; ?>"><?= htmlspecialchars(t('admin.edit'), ENT_QUOTES, 'UTF-8'); ?></a>
                                    <?php if (canDelete() && (int) $u['id'] !== (int) ($user['id'] ?? 0)): ?>
                                        <form method="post" action="users.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" style="display: inline;" onsubmit="return confirm('<?= htmlspecialchars(t('admin.confirm_delete_user'), ENT_QUOTES, 'UTF-8'); ?>');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $u['id']; ?>">
                                            <button type="submit" class="btn-danger"><?= htmlspecialchars(t('admin.delete'), ENT_QUOTES, 'UTF-8'); ?></button>
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
            <h2><?= htmlspecialchars($editing ? t('admin.edit_user') : t('admin.new_user'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <form method="post" action="users.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create'; ?>">
                <?php if ($editing): ?>
                    <input type="hidden" name="id" value="<?= (int) $editing['id']; ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <label>
                        <?= htmlspecialchars(t('admin.username_email'), ENT_QUOTES, 'UTF-8'); ?>
                        <input type="email" name="username" value="<?= htmlspecialchars((string) ($editing['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <label>
                        <?= htmlspecialchars(t('admin.role'), ENT_QUOTES, 'UTF-8'); ?>
                        <select name="role" required>
                            <option value="admin" <?= (($editing['role'] ?? 'admin') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?= (($editing['role'] ?? '') === 'user') ? 'selected' : ''; ?>>User</option>
                        </select>
                    </label>
                    <label>
                        <?= htmlspecialchars(t('admin.password'), ENT_QUOTES, 'UTF-8'); ?>
                        <input type="password" name="password" <?= $editing ? '' : 'required'; ?> autocomplete="new-password">
                        <?php if ($editing): ?>
                            <span class="form-note">Leave blank to keep current password</span>
                        <?php endif; ?>
                    </label>
                </div>
                <button class="btn primary" type="submit"><?= htmlspecialchars(t('admin.save'), ENT_QUOTES, 'UTF-8'); ?></button>
                <?php if ($editing): ?>
                    <a href="users.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>" style="background:#eee;margin-left:.5rem;display:inline-block;padding:0.9rem 1.4rem;border-radius:6px;font-weight:600;text-decoration:none;color:#111;">Cancel</a>
                <?php endif; ?>
            </form>
        </section>
    </div>
</body>
</html>
