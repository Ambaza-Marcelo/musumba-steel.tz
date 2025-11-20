<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$user = currentUser();
$lang = currentLang();

// Helper function to safely get count
function getCount($table) {
    // Whitelist of allowed table names for security
    $allowedTables = ['pages', 'services', 'projects', 'publications', 'contacts'];
    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }
    
    try {
        $result = query('SELECT COUNT(*) as total FROM `' . $table . '`');
        if ($result && $row = $result->fetch_assoc()) {
            return (int) $row['total'];
        }
    } catch (Exception $e) {
        // Table might not exist yet, return 0
        return 0;
    }
    return 0;
}

$stats = [
    'pages' => getCount('pages'),
    'services' => getCount('services'),
    'projects' => getCount('projects'),
    'publications' => getCount('publications'),
    'contacts' => getCount('contacts'),
];

// Get recent publications
$recentPubs = [];
try {
    $recentPublications = query('SELECT title_en, published_on, type FROM publications ORDER BY published_on DESC LIMIT 5');
    if ($recentPublications && is_object($recentPublications)) {
        $data = $recentPublications->fetch_all(MYSQLI_ASSOC);
        $recentPubs = $data ?: [];
    }
} catch (Exception $e) {
    // Table might not exist yet, use empty array
    $recentPubs = [];
}

// Get visitor statistics
$visitorsOnline = 0;
$visitorsByCountry = [];
$visitorsByRegion = [];
try {
    // Count visitors online (active in last 5 minutes)
    $onlineResult = query('SELECT COUNT(DISTINCT ip_address) as total FROM visitors WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)');
    if ($onlineResult && $row = $onlineResult->fetch_assoc()) {
        $visitorsOnline = (int) $row['total'];
    }
    
    // Get visitors by country
    $countryResult = query('SELECT country, COUNT(*) as count FROM visitors WHERE country IS NOT NULL AND country != "" GROUP BY country ORDER BY count DESC LIMIT 10');
    if ($countryResult) {
        $visitorsByCountry = $countryResult->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get visitors by region
    $regionResult = query('SELECT region, country, COUNT(*) as count FROM visitors WHERE region IS NOT NULL AND region != "" GROUP BY region, country ORDER BY count DESC LIMIT 10');
    if ($regionResult) {
        $visitorsByRegion = $regionResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    // Table might not exist yet
    error_log('Visitor stats error: ' . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="<?= $lang === 'sw' ? 'sw' : 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Musumba Steel</title>
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

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .stat-card .label {
            color: #999;
            font-size: 0.85rem;
            margin-top: 0.25rem;
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

        .recent-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .recent-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .recent-list li:last-child {
            border-bottom: none;
        }

        .recent-list a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }

        .recent-list a:hover {
            color: var(--primary);
        }

        .recent-list .meta {
            color: #999;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            display: block;
            padding: 1rem;
            background: var(--primary);
            color: #111;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .action-btn:hover {
            background: var(--primary-dark);
        }

        .action-btn.secondary {
            background: #5d5d5d;
            color: white;
        }

        .action-btn.secondary:hover {
            background: #444;
        }

        .visitor-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .visitor-stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8f8f8;
            border-radius: 6px;
        }

        .visitor-stat-item .country {
            font-weight: 500;
            color: #333;
        }

        .visitor-stat-item .count {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .visitor-stat-item .region-info {
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .admin-layout {
                padding: 0 1rem 1rem;
            }

            .admin-header {
                padding: 1.5rem;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <div class="user-info">
            <div>
                <h1><?= t('admin.welcome'); ?>, <?= htmlspecialchars($user['username']); ?></h1>
                <p style="margin: 0; opacity: 0.9;"><?= t('admin.panel'); ?></p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="language-switch" style="display: flex; gap: 0.5rem;">
                    <a class="<?= $lang === 'en' ? 'active' : ''; ?>" href="?lang=en" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'en' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px; font-size: 0.9rem;">EN</a>
                    <a class="<?= $lang === 'sw' ? 'active' : ''; ?>" href="?lang=sw" style="color: white; text-decoration: none; padding: 0.4rem 0.8rem; background: <?= $lang === 'sw' ? 'rgba(255,255,255,0.2)' : 'rgba(255,255,255,0.1)'; ?>; border-radius: 4px; font-size: 0.9rem;">SW</a>
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
            <a href="users.php?lang=<?= $lang; ?>"><?= t('admin.users'); ?></a>
            <a href="logout.php"><?= t('admin.logout'); ?></a>
        </nav>
    </div>

    <div class="admin-layout">
        <section class="stat-grid">
            <article class="stat-card">
                <h3><?= t('admin.pages'); ?></h3>
                <p class="number"><?= $stats['pages']; ?></p>
                <p class="label"><?= t('admin.total_pages'); ?></p>
            </article>
            <article class="stat-card">
                <h3><?= t('admin.services'); ?></h3>
                <p class="number"><?= $stats['services']; ?></p>
                <p class="label"><?= t('admin.available_services'); ?></p>
            </article>
            <article class="stat-card">
                <h3><?= t('admin.projects'); ?></h3>
                <p class="number"><?= $stats['projects']; ?></p>
                <p class="label"><?= t('admin.registered_projects'); ?></p>
            </article>
            <article class="stat-card">
                <h3><?= t('admin.publications'); ?></h3>
                <p class="number"><?= $stats['publications']; ?></p>
                <p class="label"><?= t('admin.publications_count'); ?></p>
            </article>
            <article class="stat-card">
                <h3><?= t('admin.contacts'); ?></h3>
                <p class="number"><?= $stats['contacts']; ?></p>
                <p class="label"><?= t('admin.contacts_count'); ?></p>
            </article>
            <article class="stat-card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: #111;">
                <h3 style="color: rgba(0,0,0,0.7);"><?= t('admin.online_visitors'); ?></h3>
                <p class="number" style="color: #111;"><?= $visitorsOnline; ?></p>
                <p class="label" style="color: rgba(0,0,0,0.6);"><?= t('admin.active_now'); ?></p>
            </article>
        </section>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <section class="dashboard-section">
                <h2><?= t('admin.quick_actions'); ?></h2>
                <div class="quick-actions">
                    <a href="pages.php?lang=<?= $lang; ?>" class="action-btn"><?= t('admin.manage_pages'); ?></a>
                    <a href="services.php?lang=<?= $lang; ?>" class="action-btn"><?= t('admin.manage_services'); ?></a>
                    <a href="projects.php?lang=<?= $lang; ?>" class="action-btn"><?= t('admin.manage_projects'); ?></a>
                    <a href="publications.php?lang=<?= $lang; ?>" class="action-btn"><?= t('admin.manage_publications'); ?></a>
                    <a href="contacts.php?lang=<?= $lang; ?>" class="action-btn secondary"><?= t('admin.manage_contacts'); ?></a>
                </div>
            </section>

            <section class="dashboard-section">
                <h2><?= t('admin.recent_publications'); ?></h2>
                <?php if (empty($recentPubs)): ?>
                    <p style="color: #999;"><?= t('admin.no_recent_publications'); ?></p>
                <?php else: ?>
                    <ul class="recent-list">
                        <?php foreach ($recentPubs as $pub): ?>
                            <li>
                                <a href="publications.php?lang=<?= $lang; ?>"><?= htmlspecialchars($pub['title_en']); ?></a>
                                <div class="meta">
                                    <?= date('d M Y', strtotime($pub['published_on'])); ?> · <?= htmlspecialchars($pub['type']); ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
            <section class="dashboard-section">
                <h2><?= t('admin.visitors_by_country'); ?></h2>
                <?php if (empty($visitorsByCountry)): ?>
                    <p style="color: #999;"><?= t('admin.no_data_available'); ?></p>
                <?php else: ?>
                    <div class="visitor-stats">
                        <?php foreach ($visitorsByCountry as $stat): ?>
                            <div class="visitor-stat-item">
                                <div>
                                    <div class="country"><?= htmlspecialchars($stat['country']); ?></div>
                                </div>
                                <div class="count"><?= (int) $stat['count']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-section">
                <h2><?= t('admin.visitors_by_region'); ?></h2>
                <?php if (empty($visitorsByRegion)): ?>
                    <p style="color: #999;"><?= t('admin.no_data_available'); ?></p>
                <?php else: ?>
                    <div class="visitor-stats">
                        <?php foreach ($visitorsByRegion as $stat): ?>
                            <div class="visitor-stat-item">
                                <div>
                                    <div class="country"><?= htmlspecialchars($stat['region']); ?></div>
                                    <div class="region-info"><?= htmlspecialchars($stat['country']); ?></div>
                                </div>
                                <div class="count"><?= (int) $stat['count']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>

</html>
