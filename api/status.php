<?php
header('Content-Type: application/json; charset=utf-8');
if (!function_exists('db')) {
    require dirname(__DIR__) . '/app/bootstrap.php';
}

$key = trim((string)($_POST['key'] ?? $_GET['key'] ?? ''));
$domain = normalize_domain((string)($_POST['domain'] ?? $_GET['domain'] ?? ''));
$token = trim((string)($_POST['token'] ?? $_GET['token'] ?? ''));

$st = db()->prepare('SELECT * FROM licenses WHERE license_key=? LIMIT 1');
$st->execute([$key]);
$lic = $st->fetch();
if (!$lic || !$lic['is_active']) {
    echo json_encode(['ok' => false, 'code' => 'INVALID', 'msg' => 'Key blocked. Contact admin.']);
    exit;
}

$st = db()->prepare('SELECT * FROM license_installs WHERE license_id=? AND domain=? AND token=? AND is_active=1');
$st->execute([$lic['id'], $domain, $token]);
$row = $st->fetch();
if (!$row) {
    echo json_encode(['ok' => false, 'code' => 'IN_USE', 'msg' => 'This key is not active on this domain. Contact admin.']);
    exit;
}
db()->prepare('UPDATE license_installs SET last_seen=NOW() WHERE id=?')->execute([$row['id']]);
echo json_encode(['ok' => true, 'code' => 'OK']);
