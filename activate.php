<?php
$err = '';
$ok = '';
$hq = $GLOBALS['config']['license_server'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim($_POST['key'] ?? '');
    $domain = normalize_domain($_POST['domain'] ?? current_host());
    $hq = rtrim(trim($_POST['hq'] ?? $hq), '/');
    if ($key === '' || $hq === '') {
        $err = 'HQ URL and license key required';
    } else {
        $url = $hq . '/api/activate.php';
        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query(['key' => $key, 'domain' => $domain]),
            'timeout' => 20,
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        $j = json_decode((string)$raw, true);
        if (!is_array($j) || empty($j['ok'])) {
            $err = $j['msg'] ?? 'HQ did not accept this key. Contact admin.';
        } else {
            set_setting('license_activated', '1');
            set_setting('license_token', $j['token']);
            set_setting('license_domain', $domain);
            if (is_file(__DIR__ . '/config.php')) {
                $cfg = require __DIR__ . '/config.php';
                $cfg['license_key'] = $key;
                $cfg['license_server'] = $hq;
                $cfg['allowed_domain'] = $domain;
                $cfg['allowed_domains'] = [$domain];
                $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
                file_put_contents(__DIR__ . '/config.php', $export);
            }
            $ok = 'Site activated';
        }
    }
}
$host = current_host();
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Activate Shortner</title>
<link rel="stylesheet" href="<?= h(base_url('assets/app.css')) ?>"></head>
<body class="auth"><div class="card form">
<h1>Activate license</h1>
<p class="hint">Key admin se lo. Same key doosri site pe nahi chalegi.</p>
<?php if ($err): ?><div class="alert bad"><?= h($err) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert ok"><?= h($ok) ?></div><p><a href="<?= h(base_url('login')) ?>">Login</a></p><?php endif; ?>
<form method="post">
<label>Admin HQ URL</label>
<input name="hq" required placeholder="https://your-admin-domain.com" value="<?= h($hq) ?>">
<label>License key</label>
<input name="key" required placeholder="SN2-..." value="<?= h($_POST['key'] ?? '') ?>">
<label>This domain</label>
<input name="domain" value="<?= h($_POST['domain'] ?? $host) ?>" required>
<button type="submit">Activate site</button>
</form>
</div></body></html>
