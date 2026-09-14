<?php
$root = __DIR__;
$lock = $root . '/install.lock';
$done = is_file($lock) && is_file($root . '/config.php');
$error = '';
$ok = '';
$keyShown = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$done) {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = (string)($_POST['db_pass'] ?? '');
    $domain = strtolower(trim($_POST['domain'] ?? ''));
    $base = rtrim(trim($_POST['base_url'] ?? ''), '/');
    $aname = trim($_POST['admin_name'] ?? 'Admin');
    $aemail = trim($_POST['admin_email'] ?? '');
    $apass = (string)($_POST['admin_pass'] ?? '');
    $domain = preg_replace('~^https?://~', '', $domain);
    $domain = preg_replace('~/.*$~', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    $domain = preg_replace('/:\d+$/', '', $domain);

    if ($name === '' || $user === '' || $domain === '' || $aemail === '' || strlen($apass) < 8 || $base === '') {
        $error = 'Saari fields bharao. Admin password kam az kam 8 letters.';
    } else {
        try {
            $pdo = new PDO(
                'mysql:host=' . $host . ';charset=utf8mb4',
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $safe = str_replace(['`', '"'], '', $name);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $safe . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . $safe . '`');
            $sql = file_get_contents($root . '/schema.sql');
            if ($sql === false) throw new RuntimeException('schema.sql missing');
            $pdo->exec($sql);

            $st = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,"admin")');
            $st->execute([$aname, $aemail, password_hash($apass, PASSWORD_DEFAULT)]);

            $key = 'SN2-' . strtoupper(bin2hex(random_bytes(10)));
            $ins = $pdo->prepare('INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)');
            $seed = [
                'waste_url' => '',
                'hop_url' => '',
                'hop_enabled' => '1',
                'hop_after_minutes' => '5',
                'hop_seconds' => '3',
                'android_dump_on' => '0',
                'desktop_dump_on' => '0',
                'license_key' => $key,
                'allowed_domain' => $domain,
                'install_domains' => json_encode([$domain]),
                'license_activated' => '1',
                'license_domain' => $domain,
            ];
            foreach ($seed as $k => $v) $ins->execute([$k, $v]);

            $cfg = [
                'app_name' => 'Shortner',
                'base_url' => $base,
                'allowed_domain' => $domain,
                'allowed_domains' => [$domain],
                'license_key' => $key,
                'license_server' => '',
                'db' => [
                    'host' => $host,
                    'name' => $name,
                    'user' => $user,
                    'pass' => $pass,
                    'charset' => 'utf8mb4',
                ],
                'session_name' => 'shortner_sid',
                'remember_days' => 30,
            ];
            $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
            if (file_put_contents($root . '/config.php', $export) === false) {
                throw new RuntimeException('config.php nahi likh saka. Folder permission 755 do.');
            }
            file_put_contents($lock, date('c'));
            $done = true;
            $keyShown = $key;
            $ok = 'Install complete.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$hostGuess = preg_replace('/:\d+$/', '', strtolower($_SERVER['HTTP_HOST'] ?? 'localhost'));
$hostGuess = preg_replace('/^www\./', '', $hostGuess);
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$baseGuess = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install Shortner HQ</title>
<style>
body{font-family:system-ui,sans-serif;background:#0b1220;color:#e5e7eb;margin:0}
.wrap{max-width:560px;margin:40px auto;padding:24px;background:#111827;border:1px solid #1f2937;border-radius:16px}
label{display:block;margin:12px 0 6px;font-size:13px;color:#9ca3af}
input{width:100%;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid #374151;background:#0b1220;color:#fff}
button{margin-top:18px;width:100%;padding:12px;border:0;border-radius:10px;background:#4f46e5;color:#fff;font-weight:600}
.err{background:#7f1d1d;padding:10px;border-radius:8px}
.ok{background:#14532d;padding:10px;border-radius:8px}
code{background:#0b1220;padding:2px 6px;border-radius:6px}
a{color:#a5b4fc}
</style>
</head>
<body>
<div class="wrap">
<h1>Shortner install</h1>
<p>Yeh tumhara HQ admin panel install karta hai (Hostinger PHP + MySQL).</p>
<?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($ok): ?>
  <p class="ok"><?= htmlspecialchars($ok) ?></p>
  <?php if ($keyShown): ?><p>HQ license key: <code><?= htmlspecialchars($keyShown) ?></code></p><?php endif; ?>
  <p><a href="<?= htmlspecialchars($baseGuess) ?>/login">Admin login kholo</a></p>
  <p>Install ke baad <code>install.php</code> delete kar dena.</p>
<?php endif; ?>
<?php if (!$done): ?>
<form method="post">
<label>MySQL host</label><input name="db_host" value="localhost" required>
<label>Database name</label><input name="db_name" required>
<label>Database user</label><input name="db_user" required>
<label>Database password</label><input name="db_pass" type="password">
<label>Domain (bina http)</label><input name="domain" value="<?= htmlspecialchars($hostGuess) ?>" required>
<label>Base URL</label><input name="base_url" value="<?= htmlspecialchars($baseGuess) ?>" required>
<label>Admin name</label><input name="admin_name" value="Admin" required>
<label>Admin email</label><input name="admin_email" type="email" required>
<label>Admin password (min 8)</label><input name="admin_pass" type="password" minlength="8" required>
<button type="submit">Full install</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
