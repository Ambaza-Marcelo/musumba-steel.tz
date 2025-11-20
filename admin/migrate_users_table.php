<?php
/**
 * Migration script to add role column to users table
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
        
        // Check if role column exists
        $checkResult = $connection->query("SHOW COLUMNS FROM users LIKE 'role'");
        
        if ($checkResult->num_rows === 0) {
            // Column doesn't exist, add it
            $connection->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password_hash");
            $success = "Colonne 'role' ajoutée avec succès.";
            
            // Update existing users to admin
            $connection->query("UPDATE users SET role = 'admin' WHERE username = 'marcellin@gmail.com'");
            $success .= " Utilisateurs existants mis à jour.";
        } else {
            $success = "La colonne 'role' existe déjà. Aucune migration nécessaire.";
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
    <title>Migration - Utilisateurs</title>
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
        <h1>Migration de la table utilisateurs</h1>
        <p>Ce script ajoute la colonne 'role' à la table 'users' si elle n'existe pas déjà.</p>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
            <p><a href="dashboard.php">Retour au dashboard</a></p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="run_migration" value="1">
                <button type="submit" class="btn">Exécuter la migration</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

