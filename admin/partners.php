<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/upload.php';

ensureMediaSchema();

$lang = currentLang();
$message = '';
$error = '';
$editing = null;

if (isset($_GET['id'])) {
    $stmt = query('SELECT * FROM partners WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete' && !empty($_POST['id'])) {
            $id = (int) $_POST['id'];
            $old = query('SELECT logo_path FROM partners WHERE id = ?', [$id])->fetch_assoc();
            deleteMediaFile($old['logo_path'] ?? null);
            query('DELETE FROM partners WHERE id = ?', [$id]);
            header('Location: partners.php?lang=' . $lang);
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'distributor';
        if (!in_array($category, ['distributor', 'affiliation'], true)) {
            $category = 'distributor';
        }
        $website = trim($_POST['website_url'] ?? '');
        $sort = (int) ($_POST['sort_order'] ?? 0);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            throw new RuntimeException('Partner name is required.');
        }

        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            $logoPath = uploadImage($_FILES['logo'], 'partners');
        }

        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
            if ($logoPath) {
                $old = query('SELECT logo_path FROM partners WHERE id = ?', [$id])->fetch_assoc();
                deleteMediaFile($old['logo_path'] ?? null);
                query(
                    'UPDATE partners SET name=?, category=?, logo_path=?, website_url=?, sort_order=?, is_active=? WHERE id=?',
                    [$name, $category, $logoPath, $website !== '' ? $website : null, $sort, $active, $id]
                );
            } else {
                query(
                    'UPDATE partners SET name=?, category=?, website_url=?, sort_order=?, is_active=? WHERE id=?',
                    [$name, $category, $website !== '' ? $website : null, $sort, $active, $id]
                );
            }
            $message = 'Partner updated.';
        } else {
            if (!$logoPath) {
                throw new RuntimeException('Please upload a logo image.');
            }
            query(
                'INSERT INTO partners (name, category, logo_path, website_url, sort_order, is_active) VALUES (?,?,?,?,?,?)',
                [$name, $category, $logoPath, $website !== '' ? $website : null, $sort, $active]
            );
            $message = 'Partner added.';
        }

        header('Location: partners.php?lang=' . $lang . '&ok=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET['ok'])) {
    $message = 'Saved successfully.';
}

$partners = [];
try {
    $result = query('SELECT * FROM partners ORDER BY category, sort_order, id');
    $partners = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $e) {
    $partners = [];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distributors &amp; Affiliations | Musumba Steel Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <style>
        :root { --primary:#f4b000; --dark:#1a1a1a; }
        body { margin:0; background:#f5f5f5; font-family:Montserrat,sans-serif; color:#222; }
        .admin-header { background:linear-gradient(135deg,#2d2d2d,#1a1a1a); color:#fff; padding:1.5rem 2rem; }
        .admin-header h1 { margin:0 0 .35rem; font-size:1.5rem; }
        .admin-nav { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1rem; }
        .admin-nav a { color:#fff; text-decoration:none; padding:.45rem .8rem; background:rgba(255,255,255,.12); border-radius:4px; font-size:.88rem; }
        .admin-nav a.active { background:var(--primary); color:#111; font-weight:700; }
        .wrap { max-width:1100px; margin:0 auto; padding:1.5rem; }
        .card { background:#fff; border-radius:8px; padding:1.25rem 1.4rem; margin-bottom:1.25rem; box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .card h2 { margin:0 0 1rem; font-size:1.15rem; }
        .hint { color:#666; font-size:.9rem; margin:0 0 1rem; line-height:1.45; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        label { display:block; font-weight:600; font-size:.88rem; margin-bottom:.75rem; }
        input, select { width:100%; margin-top:.35rem; padding:.65rem .75rem; border:1px solid #ddd; border-radius:6px; font:inherit; box-sizing:border-box; }
        .full { grid-column:1/-1; }
        .btn { display:inline-block; border:0; cursor:pointer; padding:.7rem 1.15rem; border-radius:6px; font-weight:700; text-decoration:none; font:inherit; }
        .btn.primary { background:var(--primary); color:#111; }
        .btn.dark { background:var(--dark); color:#fff; }
        .btn.danger { background:#b00020; color:#fff; }
        .msg { color:#0a7a32; margin-bottom:1rem; }
        .err { color:#b00020; margin-bottom:1rem; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:.7rem .5rem; border-bottom:1px solid #eee; vertical-align:middle; font-size:.92rem; }
        .thumb { height:48px; max-width:120px; object-fit:contain; background:#f7f7f7; padding:4px; border-radius:4px; }
        .badge { display:inline-block; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:.2rem .45rem; border-radius:4px; }
        .badge.distributor { background:#fff3cd; color:#7a5a00; }
        .badge.affiliation { background:#e8e8e8; color:#333; }
        @media (max-width:800px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>Distributors &amp; Affiliations</h1>
    <p style="margin:0;opacity:.85">Upload partner logos shown on the homepage (Mabati-style rows)</p>
    <nav class="admin-nav">
        <a href="dashboard.php?lang=<?= $lang; ?>">Dashboard</a>
        <a href="homepage.php?lang=<?= $lang; ?>">Homepage Media</a>
        <a class="active" href="partners.php?lang=<?= $lang; ?>">Partners</a>
        <a href="testimonials.php?lang=<?= $lang; ?>">Google Reviews</a>
        <a href="../index.php?lang=<?= $lang; ?>">View site</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="wrap">
    <?php if ($message): ?><p class="msg"><?= htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="err"><?= htmlspecialchars($error); ?></p><?php endif; ?>

    <section class="card">
        <h2><?= $editing ? 'Edit partner' : 'Add distributor / affiliation'; ?></h2>
        <p class="hint">Choose <strong>Distributor</strong> or <strong>Affiliation</strong>, upload the logo, and it will appear in the matching row on the homepage. Only logos uploaded here are shown.</p>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">
            <div class="grid">
                <label>Name
                    <input type="text" name="name" required value="<?= htmlspecialchars($editing['name'] ?? ''); ?>" placeholder="e.g. Company Ltd">
                </label>
                <label>Category
                    <select name="category" required>
                        <option value="distributor" <?= (($editing['category'] ?? '') === 'distributor') ? 'selected' : ''; ?>>Distributors</option>
                        <option value="affiliation" <?= (($editing['category'] ?? '') === 'affiliation') ? 'selected' : ''; ?>>Affiliations</option>
                    </select>
                </label>
                <label>Website (optional)
                    <input type="url" name="website_url" value="<?= htmlspecialchars($editing['website_url'] ?? ''); ?>" placeholder="https://">
                </label>
                <label>Sort order
                    <input type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0); ?>">
                </label>
                <label class="full">Logo image (PNG/JPG/WEBP<?= $editing ? ' — leave empty to keep current' : ''; ?>)
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif" <?= $editing ? '' : 'required'; ?>>
                    <?php if (!empty($editing['logo_path']) && mediaUrl($editing['logo_path'])): ?>
                        <img class="thumb" src="../<?= htmlspecialchars(mediaUrl($editing['logo_path'])); ?>" alt="" style="display:block;margin-top:.5rem">
                    <?php endif; ?>
                </label>
                <label>
                    <input type="checkbox" name="is_active" value="1" <?= !isset($editing['is_active']) || (int) $editing['is_active'] === 1 ? 'checked' : ''; ?>>
                    Published on website
                </label>
            </div>
            <button class="btn primary" type="submit"><?= $editing ? 'Update' : 'Add partner'; ?></button>
            <?php if ($editing): ?>
                <a class="btn dark" href="partners.php?lang=<?= $lang; ?>">Cancel</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>All partners (<?= count($partners); ?>)</h2>
        <?php if (!$partners): ?>
            <p class="hint">No partners yet. Add your first distributor or affiliation logo above.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($partners as $row): ?>
                    <tr>
                        <td>
                            <?php if (mediaUrl($row['logo_path'] ?? '')): ?>
                                <img class="thumb" src="../<?= htmlspecialchars(mediaUrl($row['logo_path'])); ?>" alt="">
                            <?php else: ?>
                                <span style="color:#999">Missing</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['name']); ?></strong>
                            <?php if (!empty($row['website_url'])): ?>
                                <br><small><a href="<?= htmlspecialchars($row['website_url']); ?>" target="_blank" rel="noopener">Website</a></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= htmlspecialchars($row['category']); ?>"><?= $row['category'] === 'affiliation' ? 'Affiliations' : 'Distributors'; ?></span></td>
                        <td><?= (int) $row['is_active'] ? 'Live' : 'Hidden'; ?></td>
                        <td>
                            <a href="?lang=<?= $lang; ?>&id=<?= (int) $row['id']; ?>">Edit</a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this partner?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $row['id']; ?>">
                                <button class="btn danger" type="submit" style="padding:.3rem .55rem;font-size:.8rem;margin-left:.35rem">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
</body>
</html>
