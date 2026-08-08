<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $error = 'Invalid email or password.';
        } elseif (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            $error = 'Too many failed attempts. Please try again in a few minutes.';
        } elseif (!password_verify($password, $admin['password_hash'])) {
            $attempts = (int) $admin['failed_attempts'] + 1;
            $lockUntil = null;
            if ($attempts >= LOGIN_MAX_ATTEMPTS) {
                $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_MINUTES * 60);
                $attempts = 0;
            }
            $upd = db()->prepare('UPDATE admins SET failed_attempts = ?, locked_until = ? WHERE id = ?');
            $upd->execute([$attempts, $lockUntil, $admin['id']]);
            $error = 'Invalid email or password.';
        } else {
            db()->prepare('UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login_at = NOW() WHERE id = ?')
                ->execute([$admin['id']]);
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Admin Login — <?= e(setting('company_name')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body>
<div class="auth-screen">
  <div class="auth-card">
    <div class="auth-card__brand">
      <span class="brand__mark">A</span>
      <span class="brand__name"><?= e(setting('company_name')) ?></span>
    </div>
    <h1>Admin Login</h1>
    <p class="sub">Sign in to manage your website</p>
    <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input class="form-control" type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn--gradient btn--block">Sign In</button>
    </form>
  </div>
</div>
</body>
</html>
