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
    $stmt = query('SELECT * FROM testimonials WHERE id = ?', [(int) $_GET['id']]);
    $editing = $stmt ? $stmt->fetch_assoc() : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'save_settings') {
            setSetting('google_reviews_url', trim($_POST['google_reviews_url'] ?? ''));
            $message = 'Google reviews link saved.';
        } elseif ($action === 'delete' && !empty($_POST['id'])) {
            query('DELETE FROM testimonials WHERE id = ?', [(int) $_POST['id']]);
            header('Location: testimonials.php?lang=' . $lang);
            exit;
        } else {
            $author = trim($_POST['author'] ?? '');
            $quote = trim($_POST['quote'] ?? '');
            $quoteSw = trim($_POST['quote_sw'] ?? '');
            if ($quoteSw === '') {
                $quoteSw = $quote;
            }
            $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
            $reviewedOn = trim($_POST['reviewed_on'] ?? '');
            $sort = (int) ($_POST['sort_order'] ?? 0);
            $active = isset($_POST['is_active']) ? 1 : 0;

            if ($author === '' || $quote === '') {
                throw new RuntimeException('Author name and review text are required.');
            }

            $payload = [
                $author,
                $author,
                $quote,
                $quoteSw,
                $rating,
                'google',
                $reviewedOn !== '' ? $reviewedOn : null,
                $sort,
                $active,
            ];

            if (!empty($_POST['id'])) {
                query(
                    'UPDATE testimonials SET name_en=?, name_sw=?, quote_en=?, quote_sw=?, rating=?, source=?, reviewed_on=?, sort_order=?, is_active=? WHERE id=?',
                    [...$payload, (int) $_POST['id']]
                );
            } else {
                query(
                    'INSERT INTO testimonials (name_en, name_sw, quote_en, quote_sw, rating, source, reviewed_on, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?)',
                    $payload
                );
            }

            header('Location: testimonials.php?lang=' . $lang);
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$reviews = [];
try {
    $result = query('SELECT * FROM testimonials ORDER BY sort_order, id DESC');
    $reviews = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
} catch (Throwable $e) {
    $reviews = [];
}

$googleUrl = getSetting('google_reviews_url');
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Reviews | Musumba Steel Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap">
    <style>
        :root { --primary: #f4b000; --primary-dark: #c98900; --dark: #1a1a1a; }
        body { margin: 0; background: #f5f5f5; font-family: Montserrat, sans-serif; color: #222; }
        .admin-header { background: linear-gradient(135deg, #2d2d2d, #1a1a1a); color: #fff; padding: 1.5rem 2rem; }
        .admin-header h1 { margin: 0 0 .35rem; font-size: 1.5rem; }
        .admin-nav { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
        .admin-nav a { color: #fff; text-decoration: none; padding: .45rem .8rem; background: rgba(255,255,255,.12); border-radius: 4px; font-size: .88rem; }
        .admin-nav a.active { background: var(--primary); color: #111; font-weight: 700; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 1.5rem; }
        .card { background: #fff; border-radius: 8px; padding: 1.25rem 1.4rem; margin-bottom: 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .card h2 { margin: 0 0 1rem; font-size: 1.15rem; }
        .hint { color: #666; font-size: .9rem; margin: 0 0 1rem; line-height: 1.45; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        label { display: block; font-weight: 600; font-size: .88rem; margin-bottom: .75rem; }
        input, textarea, select { width: 100%; margin-top: .35rem; padding: .65rem .75rem; border: 1px solid #ddd; border-radius: 6px; font: inherit; box-sizing: border-box; }
        textarea { min-height: 110px; resize: vertical; }
        .full { grid-column: 1 / -1; }
        .btn { display: inline-block; border: 0; cursor: pointer; padding: .7rem 1.15rem; border-radius: 6px; font-weight: 700; text-decoration: none; font: inherit; }
        .btn.primary { background: var(--primary); color: #111; }
        .btn.dark { background: var(--dark); color: #fff; }
        .btn.danger { background: #b00020; color: #fff; }
        .msg { color: #0a7a32; margin-bottom: 1rem; }
        .err { color: #b00020; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .7rem .5rem; border-bottom: 1px solid #eee; vertical-align: top; font-size: .92rem; }
        .stars { color: #f4b000; letter-spacing: 1px; }
        .badge { display: inline-block; background: #eef3ff; color: #4285F4; font-size: .75rem; font-weight: 700; padding: .15rem .45rem; border-radius: 4px; }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>Google Reviews</h1>
    <p style="margin:0;opacity:.85">Publish real customer reviews from your Google Business Profile</p>
    <nav class="admin-nav">
        <a href="dashboard.php?lang=<?= $lang; ?>">Dashboard</a>
        <a href="homepage.php?lang=<?= $lang; ?>">Homepage Media</a>
        <a class="active" href="testimonials.php?lang=<?= $lang; ?>">Google Reviews</a>
        <a href="services.php?lang=<?= $lang; ?>">Services</a>
        <a href="projects.php?lang=<?= $lang; ?>">Projects</a>
        <a href="../index.php?lang=<?= $lang; ?>">View site</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="wrap">
    <?php if ($message): ?><p class="msg"><?= htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="err"><?= htmlspecialchars($error); ?></p><?php endif; ?>

    <section class="card">
        <h2>Google Business link</h2>
        <p class="hint">Paste your Google Maps / Business Profile reviews URL. The homepage buttons “See all Google reviews” and “Leave a Google review” will open this link.</p>
        <form method="post">
            <input type="hidden" name="action" value="save_settings">
            <label>Google reviews URL
                <input type="url" name="google_reviews_url" value="<?= htmlspecialchars($googleUrl); ?>" placeholder="https://maps.google.com/... or https://g.page/.../review">
            </label>
            <button class="btn primary" type="submit">Save link</button>
        </form>
    </section>

    <section class="card">
        <h2><?= $editing ? 'Edit Google review' : 'Add Google review'; ?></h2>
        <p class="hint">Copy reviews from your Google Business Profile (name, stars, text, date) and paste them here. Only real Google reviews should be published.</p>
        <form method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">
            <div class="grid">
                <label>Reviewer name
                    <input type="text" name="author" required value="<?= htmlspecialchars($editing['name_en'] ?? ''); ?>" placeholder="e.g. John M.">
                </label>
                <label>Star rating
                    <select name="rating">
                        <?php for ($r = 5; $r >= 1; $r--): ?>
                            <option value="<?= $r; ?>" <?= ((int) ($editing['rating'] ?? 5) === $r) ? 'selected' : ''; ?>><?= $r; ?> ★</option>
                        <?php endfor; ?>
                    </select>
                </label>
                <label>Review date
                    <input type="date" name="reviewed_on" value="<?= htmlspecialchars($editing['reviewed_on'] ?? ''); ?>">
                </label>
                <label>Sort order
                    <input type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0); ?>">
                </label>
                <label class="full">Review text (as on Google)
                    <textarea name="quote" required><?= htmlspecialchars($editing['quote_en'] ?? ''); ?></textarea>
                </label>
                <label class="full">Swahili translation (optional — leave blank to reuse English)
                    <textarea name="quote_sw"><?= htmlspecialchars(($editing && ($editing['quote_sw'] ?? '') !== ($editing['quote_en'] ?? '')) ? ($editing['quote_sw'] ?? '') : ''); ?></textarea>
                </label>
                <label>
                    <input type="checkbox" name="is_active" value="1" <?= !isset($editing['is_active']) || (int) $editing['is_active'] === 1 ? 'checked' : ''; ?>>
                    Published on website
                </label>
            </div>
            <button class="btn primary" type="submit"><?= $editing ? 'Update review' : 'Add review'; ?></button>
            <?php if ($editing): ?>
                <a class="btn dark" href="testimonials.php?lang=<?= $lang; ?>">Cancel</a>
            <?php endif; ?>
        </form>
    </section>

    <section class="card">
        <h2>Published Google reviews (<?= count($reviews); ?>)</h2>
        <?php if (!$reviews): ?>
            <p class="hint">No reviews yet. Add the first one from your Google Business Profile.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Quote</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reviews as $row): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($row['name_en']); ?></strong><br>
                            <span class="badge">Google</span>
                            <?php if (!empty($row['reviewed_on'])): ?>
                                <small><?= htmlspecialchars($row['reviewed_on']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="stars"><?= str_repeat('★', (int) ($row['rating'] ?? 5)); ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($row['quote_en'], 0, 120, '…')); ?></td>
                        <td><?= (int) $row['is_active'] ? 'Live' : 'Hidden'; ?></td>
                        <td>
                            <a href="?lang=<?= $lang; ?>&id=<?= (int) $row['id']; ?>">Edit</a>
                            <form method="post" style="display:inline" onsubmit="return confirm('Delete this review?');">
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
