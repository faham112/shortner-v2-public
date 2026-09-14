<?php
$root = __DIR__;
$lock = $root . '/install.lock';
$done = is_file($lock) && is_file($root . '/config.php');
$error = ''; $ok = '';
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
    $domain = preg_replace('/^https?:\/\//', '', $domain);
    $domain = preg_replace('/\/.*$/', '', $domain);
    $domain = preg_replace('/^www\./', '', $domain);
    if ($name === '' || $user === '' || $domain === '' || $aemail === '' || strlen($apass) < 8) {
        $error = 'Fill all fields. Admin password min 8 chars. Domain without http.';
    } else {
        try {
            $pdo = new PDO('mysql:host=' . $host . ';charset=utf8mb4', $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $safe = str_replace('`','',$name);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$safe.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `'.$safe.'`');
            $pdo->exec(file_get_contents($root . '/schema.sql'));
            $st = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,"admin")');
            $st->execute([$aname, $aemail, password_hash($apass, PASSWORD_DEFAULT)]);
            $key = 'SN2-' . strtoupper(substr(hash('sha256', $domain . '|' . random_bytes(16)), 0, 24));
            $ins = $pdo->prepare('INSERT INTO settings (skey,svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)');
            foreach (['waste_url'=>'','hop_url'=>'','hop_enabled'=>'1','hop_after_minutes'=>'5','hop_seconds'=>'3','license_key'=>$key,'allowed_domain'=>$domain] as $k=>$v) $ins->execute([$k,$v]);
            $cfg = "<?php\nreturn [\n  'app_name' => 'Shortner',\n  'base_url' => '".addslashes($base)."',\n  'allowed_domain' => '".addslashes($domain)."',\n  'license_key' => '".addslashes($key)."',\n  'db' => ['host=>'".addslashes($host)."','name=>'".addslashes($name)."','user=>'".addslashes($user)."','pass=>'".addslashes($pass)."','charset'=>'utf8mb4'],\n  'session_name' => 'shortner_sid',\n  'remember_days' => 30,\n];\n";
            // fix quotes in generated config
            $cfg = "<?php\nreturn [\n  'app_name' => 'Shortner',\n  'base_url' => '".addslashes($base)."',\n  'allowed_domain' => '".addslashes($domain)."',\n  'license_key' => '".addslashes($key)."',\n  'db' => ['host'=>'" . addslashes($host) . "','name'=>'" . addslashes($name) . "','user'=>'" . addslashes($user) . "','pass'=>'" . addslashes($pass) . "','charset'=>'utf8mb4'],\n  'session_name' => 'shortner_sid',\n  'remember_days' => 30,\n];\n";
            if (file_put_contents($root . '/config.php', $cfg) === false) throw new RuntimeException('Cannot write config.php');
            file_put_contents($lock, date('c'));
            $done = true; $ok = 'Installed. License key: ' . $key;
        } catch (Throwable $e) { $error = $e->getMessage(); }
    }
}
$hostGuess = preg_replace('/:\d+$/', '', strtolower($_SERVER['HTTP_HOST'] ?? 'localhost'));
$hostGuess = preg_replace('/^www\./', '', $hostGuess);
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$baseGuess = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Install Shortner</title>
<style>body{font-family:system-ui;background:#0b1220;color:#e5e7eb;margin:0}.wrap{max-width:560px;margin:48px auto;padding:24px;background:#111827;border:1px solid #1f2937;border-radius:16px}label{display:block;margin:12px 0 6px;font-size:13px;color:#9ca3af}input{width:100%;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid #374151;background:#0b1220;color:#fff}button{margin-top:18px;width:100%;padding:12px;border:0;border-radius:10px;background:#4f46e5;color:#fff;font-weight:600} .err{background:#7f1d1d;padding:10px;border-radius:8px}.ok{background:#14532d;padding:10px;border-radius:8px}</style></head><body><div class="wrap"><h1>Shortner install</h1><p>Single-domain license. Hostinger PHP + MySQL.</p>
<?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($ok): ?><p class="ok"><?= htmlspecialchars($ok) ?></p><p><a href="/login" style="color:#a5b4fc">Go to login</a></p><?php endif; ?>
<?php if (!$done): ?><form method="post">
<label>MySQL host</label><input name="db_host" value="localhost" required>
<label>Database name</label><input name="db_name" required>
<label>Database user</label><input name="db_user" required>
<label>Database password</label><input name="db_pass" type="password">
<label>Allowed domain (no http)</label><input name="domain" value="<?= htmlspecialchars($hostGuess) ?>" required>
<label>Base URL</label><input name="base_url" value="<?= htmlspecialchars($baseGuess) ?>" required>
<label>Admin name</label><input name="admin_name" value="Admin" required>
<label>Admin email</label><input name="admin_email" type="email" required>
<label>Admin password (min 8)</label><input name="admin_pass" type="password" minlength="8" required>
<button type="submit">Install</button></form><?php endif; ?></div></body></html>
