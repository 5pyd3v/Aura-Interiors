<?php
/**
 * One-time web installer:
 *  1. Imports database/database.sql (creates the database + demo data)
 *  2. Creates your real admin account with a securely hashed password
 *
 * Delete this file (or leave it — it self-locks) once installation is complete.
 * Uses the DB_* constants from config/config.php, so edit that file first if
 * your hosting credentials differ from the defaults.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

$lockFile = __DIR__ . '/config/installed.lock';
$alreadyInstalled = file_exists($lockFile);

$error = null;
$success = null;

function run_sql_file(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    // Strip full-line comments, then split into individual statements on ";".
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', explode(";\n", $sql)));
    foreach ($statements as $statement) {
        $statement = trim($statement, "; \t\n\r\0\x0B");
        if ($statement === '') continue;
        $pdo->exec($statement);
    }
}

if (!$alreadyInstalled && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $adminName = trim((string) ($_POST['admin_name'] ?? ''));
    $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
    $adminPassword = (string) ($_POST['admin_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($adminName === '' || $adminEmail === '' || $adminPassword === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($adminPassword) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($adminPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Connect without selecting a database yet (database.sql creates it).
            $rootPdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            run_sql_file($rootPdo, __DIR__ . '/database/database.sql');

            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, password_hash = ?, failed_attempts = 0, locked_until = NULL WHERE id = 1');
            $stmt->execute([$adminName, $adminEmail, $hash]);

            file_put_contents($lockFile, 'Installed on ' . date('Y-m-d H:i:s') . "\n");
            $success = true;
        } catch (Throwable $e) {
            $error = 'Installation failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install — Aura Interiors</title>
<style>
  body{ font-family:-apple-system,Segoe UI,sans-serif; background:#14120E; color:#fff; min-height:100vh; display:flex; align-items:center; justify-content:center; margin:0; padding:24px; }
  .card{ max-width:480px; width:100%; background:#221C15; border-radius:16px; padding:40px; box-shadow:0 20px 60px rgba(0,0,0,.4); }
  h1{ font-size:1.4rem; margin:0 0 8px; }
  p.sub{ color:rgba(255,255,255,.6); font-size:.9rem; margin:0 0 28px; }
  label{ display:block; font-size:.82rem; font-weight:600; margin-bottom:6px; margin-top:16px; }
  input{ width:100%; padding:12px 14px; border-radius:8px; border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.06); color:#fff; font-size:.92rem; box-sizing:border-box; }
  button{ margin-top:26px; width:100%; padding:14px; border-radius:999px; border:none; background:linear-gradient(120deg,#8C3822,#A8432A,#B9852D); color:#fff; font-weight:700; font-size:.95rem; cursor:pointer; }
  .msg{ padding:14px 16px; border-radius:8px; font-size:.88rem; margin-bottom:10px; }
  .msg--error{ background:rgba(224,69,63,.15); border:1px solid rgba(224,69,63,.4); }
  .msg--success{ background:rgba(46,193,140,.15); border:1px solid rgba(46,193,140,.4); }
  a{ color:#D2A24C; }
  code{ background:rgba(255,255,255,.08); padding:2px 6px; border-radius:4px; }
</style>
</head>
<body>
<div class="card">
  <h1>Aura Interiors — Setup</h1>

  <?php if ($alreadyInstalled): ?>
    <div class="msg msg--success">This site is already installed.</div>
    <p class="sub">To reinstall (this will reset your database), delete <code>config/installed.lock</code> and run this page again.</p>
    <p class="sub"><a href="admin/index.php">Go to Admin Login →</a></p>

  <?php elseif ($success): ?>
    <div class="msg msg--success">Installation complete! Your database has been created and your admin account is ready.</div>
    <p class="sub">For security, this installer is now locked. You may delete <code>install.php</code> entirely.</p>
    <p class="sub"><a href="admin/index.php">Go to Admin Login →</a> &nbsp;|&nbsp; <a href="index.php">View Website →</a></p>

  <?php else: ?>
    <p class="sub">This will create the database <code><?= e(DB_NAME) ?></code> on <code><?= e(DB_HOST) ?></code> using the credentials in <code>config/config.php</code>, load demo content, and set up your admin login.</p>
    <?php if ($error): ?><div class="msg msg--error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label for="admin_name">Your Name</label>
      <input type="text" id="admin_name" name="admin_name" required value="<?= e($_POST['admin_name'] ?? '') ?>">
      <label for="admin_email">Admin Email (used to log in)</label>
      <input type="email" id="admin_email" name="admin_email" required value="<?= e($_POST['admin_email'] ?? '') ?>">
      <label for="admin_password">Password (min. 8 characters)</label>
      <input type="password" id="admin_password" name="admin_password" required minlength="8">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
      <button type="submit">Install Website</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
<?php
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
