<?php

require_once __DIR__ . '/../includes/helpers.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = query('SELECT id, password_hash FROM users WHERE username = ?', [$username]);
    $user = $stmt ? $stmt->fetch_assoc() : null;

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    }

    $error = t('admin.error');
}

?>
<!DOCTYPE html>
<html lang="<?= currentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('admin.login'); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #111;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }

        .login-card {
            width: min(90vw, 420px);
            background: white;
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
            text-align: center;
        }

        .error {
            color: #c00;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <form method="post" class="login-card">
        <h2><?= t('admin.login'); ?></h2>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <label for="username"><?= t('admin.username'); ?></label>
        <input id="username" name="username" type="email" required>

        <label for="password"><?= t('admin.password'); ?></label>
        <input id="password" name="password" type="password" required>

        <button class="btn primary" type="submit"><?= t('admin.login'); ?></button>
    </form>
</body>

</html>

