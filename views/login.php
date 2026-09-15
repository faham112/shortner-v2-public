<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass = (string)($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);
    $st = db()->prepare('SELECT * FROM users WHERE email=? AND is_active=1');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($pass, $u['password_hash'])) {
        login_user($u, $remember);
        redirect(base_url($u['role'] === 'admin' ? 'admin' : 'user'));
    }
    $error = 'Invalid email or password';
}
$theme = $_SESSION['theme'] ?? 'dark';
?>
<!doctype html>
<html lang="<?= h(lang()) ?>" data-theme="<?= h($theme) ?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h(t('login')) ?> — Shortner</title>
<link rel="stylesheet" href="<?= h(base_url('assets/app.css')) ?>">
<?php require __DIR__ . '/theme_inline.php'; ?>
</head>
<body class="auth">
<form class="card form" method="post">
  <h1>Shortner</h1>
  <p><?= h(t('login')) ?></p>
  <?php if ($error): ?><div class="alert bad"><?= h($error) ?></div><?php endif; ?>
  <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
  <label><?= h(t('email')) ?></label>
  <input type="email" name="email" required>
  <label><?= h(t('password')) ?></label>
  <input type="password" name="password" required>
  <label class="row"><input type="checkbox" name="remember" value="1"> <?= h(t('remember')) ?></label>
  <button type="submit"><?= h(t('login')) ?></button>
</form>
</body></html>
