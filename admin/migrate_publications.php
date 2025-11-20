<?php
/**
 * Migration script to create publications table and publication_images table
 * Run this once to update your database
 */

require_once __DIR__ . '/includes/auth.php';

// Only allow admins to run migration
if (empty($_SESSION['user_id'])) {
    die('You must be logged in to run this migration.');
}

$user = currentUser();
if (!$user || ($user['role'] ?? 'admin') !== 'admin') {
    die('Only admins can run this migration.');
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $connection = db();
        
        // Check if publications table exists
        $checkResult = $connection->query("SHOW TABLES LIKE 'publications'");
        
        if ($checkResult->num_rows === 0) {
            // Create publications table
            $connection->query("CREATE TABLE publications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                type ENUM('events','training','national-holidays','international-holidays','calls-for-tenders','communicates') NOT NULL,
                title_en VARCHAR(255) NOT NULL,
                title_sw VARCHAR(255) NOT NULL,
                body_en TEXT,
                body_sw TEXT,
                attachment VARCHAR(255),
                published_on DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");
            $success = "Table 'publications' créée avec succès.";
        } else {
            $success = "La table 'publications' existe déjà.";
        }
        
        // Check if publication_images table exists
        $checkResult2 = $connection->query("SHOW TABLES LIKE 'publication_images'");
        
        if ($checkResult2->num_rows === 0) {
            // Create publication_images table
            $connection->query("CREATE TABLE publication_images (
                id INT AUTO_INCREMENT PRIMARY KEY,
                publication_id INT NOT NULL,
                image_path VARCHAR(500) NOT NULL,
                image_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
                INDEX idx_publication_id (publication_id),
                INDEX idx_image_order (image_order)
            ) ENGINE=InnoDB");
            $success .= " Table 'publication_images' créée avec succès.";
        } else {
            $success .= " La table 'publication_images' existe déjà.";
        }
        
        // Create uploads directory
        $uploadsDir = __DIR__ . '/../uploads/publications/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
            $success .= " Dossier uploads créé.";
        }
        
    } catch (Exception $e) {
        $error = "Erreur lors de la migration: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration - Publications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Montserrat', sans-serif;
            padding: 2rem;
        }
        .migration-card {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
        .btn {
            padding: 0.9rem 1.4rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            background: var(--primary);
            color: #111;
        }
        .btn:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="migration-card">
        <h1>Migration des tables publications</h1>
        <p>Ce script crée les tables 'publications' et 'publication_images' si elles n'existent pas déjà.</p>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
            <p><a href="publications.php">Aller aux publications</a> | <a href="dashboard.php">Retour au dashboard</a></p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="run_migration" value="1">
                <button type="submit" class="btn">Exécuter la migration</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

