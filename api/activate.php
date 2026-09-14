<?php
header('Content-Type: application/json; charset=utf-8');
if (!function_exists('db')) {
    require dirname(__DIR__) . '/app/bootstrap.php';
}

$key = trim((string)($_POST['key'] ?? $_GET['key'] ?? ''));
$domain = normalize_domain((string)($_POST['domain'] ?? $_GET['domain'] ?? ''));

if ($key === '' || $domain === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'code' => 'MISSING', 'msg' => 'License key and domain required']);
    exit;
}

$st = db()->prepare('SELECT * FROM licenses WHERE license_key=? LIMIT 1');
$st->execute([$key]);
$lic = $st->fetch();
if (!$lic || !$lic['is_active']) {
    echo json_encode(['ok' => false, 'code' => 'INVALID', 'msg' => 'Invalid or disabled key. Contact admin.']);
    exit;
}
if (!empty($lic['expires_at']) && $lic['expires_at'] < date('Y-m-d')) {
    echo json_encode(['ok' => false, 'code' => 'EXPIRED', 'msg' => 'License expired. Contact admin.']);
    exit;
}

$st = db()->prepare('SELECT * FROM license_installs WHERE license_id=? AND is_active=1');
$st->execute([$lic['id']]);
$installs = $st->fetchAll();
$same = null;
foreach ($installs as $in) {
    if ($in['domain'] === $domain) { $same = $in; break; }
}

if (!$same && count($installs) >= (int)$lic['max_domains']) {
    echo json_encode([
        'ok' => false,
        'code' => 'IN_USE',
        'msg' => 'This license key is already used on another site. Contact admin.',
    ]);
    exit;
}

$token = $same['token'] ?? bin2hex(random_bytes(32));
if ($same) {
    db()->prepare('UPDATE license_installs SET last_seen=NOW() WHERE id=?')->execute([$same['id']]);
} else {
    db()->prepare('INSERT INTO license_installs (license_id,domain,token,last_seen) VALUES (?,?,?,NOW())')
        ->execute([$lic['id'], $domain, $token]);
    try {
        db()->prepare('INSERT IGNORE INTO license_domains (license_id,domain) VALUES (?,?)')->execute([$lic['id'], $domain]);
    } catch (Throwable $e) {}
}

$files = [];
$root = dirname(__DIR__) . '/dist/payload';
if (is_dir($root)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile()) continue;
        if (strtolower($f->getFilename()) === 'readme.txt') continue;
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
        if (str_contains($rel, '..')) continue;
        $files[$rel] = base64_encode((string)file_get_contents($f->getPathname()));
    }
}

echo json_encode([
    'ok' => true,
    'code' => 'OK',
    'msg' => 'Activated',
    'token' => $token,
    'domain' => $domain,
    'max_domains' => (int)$lic['max_domains'],
    'files' => $files,
]);
