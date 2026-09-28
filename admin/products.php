<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$editing = null;
$editVariants = [];
$error = '';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $editing = adminFetchOne('SELECT * FROM products WHERE id = ?', [$id]);
    if ($editing) {
        try {
            $editVariants = getProductVariants($id) ?: [];
        } catch (Throwable $e) {
            $editVariants = [];
        }
    }
}

$products = adminFetchAll(
    'SELECT p.*, (SELECT COUNT(*) FROM product_variants v WHERE v.product_id = p.id) AS variant_count
     FROM products p ORDER BY p.sort_order, p.id'
);
if ($products === []) {
    // Fallback if product_variants table missing
    $products = adminFetchAll('SELECT p.*, 0 AS variant_count FROM products p ORDER BY p.sort_order, p.id');
}

$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Products | Musumba Steel Admin</title>
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
        .hint { color:#666; font-size:.9rem; margin:0 0 1rem; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        label { display:block; font-weight:600; font-size:.88rem; margin-bottom:.75rem; }
        input, select { width:100%; margin-top:.35rem; padding:.65rem .75rem; border:1px solid #ddd; border-radius:6px; font:inherit; box-sizing:border-box; }
        .full { grid-column:1/-1; }
        .btn { display:inline-block; border:0; cursor:pointer; padding:.7rem 1.15rem; border-radius:6px; font-weight:700; text-decoration:none; font:inherit; }
        .btn.primary { background:var(--primary); color:#111; }
        .btn.dark { background:var(--dark); color:#fff; }
        .btn.danger { background:#b00020; color:#fff; }
        .btn.ghost { background:#eee; color:#333; }
        .msg { color:#0a7a32; margin-bottom:1rem; }
        .err { color:#b00020; margin-bottom:1rem; }
        table { width:100%; border-collapse:collapse; }
        th, td { text-align:left; padding:.7rem .5rem; border-bottom:1px solid #eee; font-size:.92rem; vertical-align:top; }
        .variants-table { width:100%; margin-top:.5rem; }
        .variants-table th { font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; color:#666; }
        .variants-table input { margin-top:0; }
        .color-cell { display:flex; gap:.4rem; align-items:center; }
        .color-cell input[type=color] { width:44px; padding:0; height:36px; }
        @media (max-width:800px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="admin-header">
    <h1>Quote Configurator — Products</h1>
    <p style="margin:0;opacity:.85">Manage profiles, gauges, finishes, colours &amp; price coefficients</p>
    <nav class="admin-nav">
        <a href="dashboard.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>
        <a class="active" href="products.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">Quote Products</a>
        <a href="services.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">Catalogue</a>
        <a href="../index.php?page=roof-designs" target="_blank">View configurator</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="wrap">
    <p class="msg" id="flashMsg" hidden></p>
    <p class="err" id="flashErr" hidden></p>

    <section class="card">
        <h2><?= $editing ? 'Edit product #' . (int) $editing['id'] : 'Create product'; ?></h2>
        <p class="hint">Add variants dynamically below, then save via AJAX. Formula: <strong>base price × coefficient × length</strong>.</p>
        <form id="productForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0); ?>">
            <div class="grid">
                <label>Product name
                    <input type="text" name="name" required value="<?= htmlspecialchars($editing['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>Profile type
                    <input type="text" name="profile_type" required placeholder="e.g. Versatile, IT-5 Tekdek" value="<?= htmlspecialchars($editing['profile_type'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>Base price per metre (TZS)
                    <input type="number" name="base_price_per_meter" min="0" step="0.01" required value="<?= htmlspecialchars((string) ($editing['base_price_per_meter'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>Sort order
                    <input type="number" name="sort_order" value="<?= (int) ($editing['sort_order'] ?? 0); ?>">
                </label>
                <label class="full">Image URL (optional)
                    <input type="text" name="image_url" value="<?= htmlspecialchars($editing['image_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="uploads/... or https://">
                </label>
                <label>
                    <input type="checkbox" name="is_active" value="1" <?= !isset($editing['is_active']) || (int) $editing['is_active'] === 1 ? 'checked' : ''; ?>>
                    Active on configurator
                </label>
            </div>

            <h3 style="margin:1.25rem 0 .5rem;font-size:1rem">Variants</h3>
            <table class="variants-table" id="variantsTable">
                <thead>
                    <tr>
                        <th>Gauge</th>
                        <th>Colour name</th>
                        <th>Hex</th>
                        <th>Finish</th>
                        <th>Coefficient</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="variantsBody"></tbody>
            </table>
            <p style="margin-top:.75rem">
                <button type="button" class="btn ghost" id="addVariantBtn">+ Add variant</button>
            </p>
            <p style="margin-top:1rem">
                <button type="submit" class="btn primary" id="saveBtn">Save product</button>
                <?php if ($editing): ?>
                    <a class="btn dark" href="products.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">New product</a>
                <?php endif; ?>
            </p>
        </form>
    </section>

    <section class="card">
        <h2>All products (<?= count($products); ?>)</h2>
        <?php if (!$products): ?>
            <p class="hint">No products yet — create the first profile above.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Profile</th>
                        <th>Base / m</th>
                        <th>Variants</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['profile_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= number_format((float) $row['base_price_per_meter'], 0); ?> TZS</td>
                        <td><?= (int) $row['variant_count']; ?></td>
                        <td><?= (int) $row['is_active'] ? 'Live' : 'Hidden'; ?></td>
                        <td>
                            <a href="?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>&id=<?= (int) $row['id']; ?>">Edit</a>
                            <button type="button" class="btn danger delete-product" data-id="<?= (int) $row['id']; ?>" style="padding:.3rem .55rem;font-size:.8rem;margin-left:.35rem">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

<script>
(() => {
  const csrf = <?= json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
  const existing = <?= json_encode($editVariants, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
  const body = document.getElementById('variantsBody');
  const form = document.getElementById('productForm');
  const msg = document.getElementById('flashMsg');
  const err = document.getElementById('flashErr');

  function flash(ok, text) {
    msg.hidden = !ok;
    err.hidden = ok;
    (ok ? msg : err).textContent = text;
  }

  function rowHtml(data = {}) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input name="gauge" placeholder="G28" value="${esc(data.gauge || '')}" required></td>
      <td><input name="color_name" placeholder="Charcoal Grey" value="${esc(data.color_name || '')}" required></td>
      <td class="color-cell">
        <input type="color" name="color_hex_picker" value="${esc(data.color_hex || '#888888')}">
        <input name="color_hex" value="${esc(data.color_hex || '#888888')}" maxlength="7" style="width:90px">
      </td>
      <td>
        <select name="finish">
          <option value="Glossy" ${data.finish === 'Matte' ? '' : 'selected'}>Glossy</option>
          <option value="Matte" ${data.finish === 'Matte' ? 'selected' : ''}>Matte</option>
        </select>
      </td>
      <td><input type="number" name="price_coefficient" min="0.01" step="0.01" value="${esc(String(data.price_coefficient ?? '1'))}" required></td>
      <td><button type="button" class="btn danger remove-row" style="padding:.35rem .55rem;font-size:.8rem">×</button></td>
    `;
    const picker = tr.querySelector('[name=color_hex_picker]');
    const hex = tr.querySelector('[name=color_hex]');
    picker.addEventListener('input', () => { hex.value = picker.value; });
    hex.addEventListener('input', () => {
      if (/^#[0-9A-Fa-f]{6}$/.test(hex.value)) picker.value = hex.value;
    });
    tr.querySelector('.remove-row').addEventListener('click', () => {
      if (body.children.length > 1) tr.remove();
    });
    return tr;
  }

  function esc(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    })[c]);
  }

  function addRow(data) {
    body.appendChild(rowHtml(data));
  }

  if (Array.isArray(existing) && existing.length) {
    existing.forEach(addRow);
  } else {
    addRow({ gauge: 'G28', color_name: '', color_hex: '#888888', finish: 'Glossy', price_coefficient: 1 });
  }

  document.getElementById('addVariantBtn').addEventListener('click', () => addRow());

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const variants = [];
    body.querySelectorAll('tr').forEach((tr) => {
      variants.push({
        gauge: tr.querySelector('[name=gauge]').value.trim(),
        color_name: tr.querySelector('[name=color_name]').value.trim(),
        color_hex: tr.querySelector('[name=color_hex]').value.trim(),
        finish: tr.querySelector('[name=finish]').value,
        price_coefficient: Number(tr.querySelector('[name=price_coefficient]').value),
      });
    });

    const payload = {
      action: 'save',
      csrf_token: csrf,
      id: Number(fd.get('id') || 0),
      name: fd.get('name'),
      profile_type: fd.get('profile_type'),
      base_price_per_meter: Number(fd.get('base_price_per_meter')),
      image_url: fd.get('image_url'),
      sort_order: Number(fd.get('sort_order') || 0),
      is_active: form.querySelector('[name=is_active]').checked ? 1 : 0,
      variants,
    };

    try {
      const res = await fetch('api/save_product.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (!data.ok) {
        flash(false, data.error || 'Save failed');
        return;
      }
      flash(true, data.message || 'Saved');
      window.location.href = 'products.php?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>&id=' + data.id;
    } catch (ex) {
      flash(false, 'Network error');
    }
  });

  document.querySelectorAll('.delete-product').forEach((btn) => {
    btn.addEventListener('click', async () => {
      if (!confirm('Delete this product and all variants?')) return;
      try {
        const res = await fetch('api/save_product.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'delete', id: Number(btn.dataset.id), csrf_token: csrf }),
        });
        const data = await res.json();
        if (!data.ok) {
          flash(false, data.error || 'Delete failed');
          return;
        }
        window.location.reload();
      } catch (_) {
        flash(false, 'Network error');
      }
    });
  });
})();
</script>
</body>
</html>
