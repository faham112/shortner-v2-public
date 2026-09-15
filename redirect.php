<?php
migrate_mask_columns();
$code = $route;
$st = db()->prepare('SELECT * FROM links WHERE code=? AND is_active=1 LIMIT 1');
$st->execute([$code]);
$link = $st->fetch();
if (!$link) { http_response_code(404); echo 'Link not found'; exit; }

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isBot = is_preview_bot($ua);

if ($isBot) {
    $previewOn = !array_key_exists('preview_on', $link) || (int)$link['preview_on'] === 1;
    if (!$previewOn) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta name="robots" content="noindex"><title></title>';
        exit;
    }
    require __DIR__ . '/views/mask.php';
    exit;
}

$isAnd = is_android_chrome_app($ua);
$isDesk = is_desktop_chrome($ua);
$andOn = setting('android_dump_on', '0') === '1';
$deskOn = setting('desktop_dump_on', '0') === '1';

$dump = null;
if ($isAnd && $andOn) {
    $dump = pick_dump_url('android_dump');
    if (!$dump && !empty($link['waste_url'])) $dump = $link['waste_url'];
}
if ($isDesk && $deskOn) {
    $dump = pick_dump_url('desktop_dump');
}

$waste = $dump ? true : false;
[$browser, $os, $device] = parse_ua($ua);
[$country, $city] = geo_country();
$ref = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255);

try {
    db()->prepare('INSERT INTO clicks (link_id,is_waste,ip,country,city,browser,device,os,referrer,user_agent) VALUES (?,?,?,?,?,?,?,?,?,?)')
        ->execute([$link['id'], $waste ? 1 : 0, client_ip(), $country, $city, $browser, $device, $os, $ref, substr($ua, 0, 500)]);
} catch (Throwable $e) {}

if ($waste) db()->prepare('UPDATE links SET waste_clicks=waste_clicks+1 WHERE id=?')->execute([$link['id']]);
else db()->prepare('UPDATE links SET clicks=clicks+1 WHERE id=?')->execute([$link['id']]);

$target = $waste ? $dump : $link['destination'];
$hopOn = setting('hop_enabled', '1') === '1';
$hopUrl = setting('hop_url', '');
$after = (int)setting('hop_after_minutes', '5');
$secs = (int)setting('hop_seconds', '3');
$ageMin = (time() - strtotime($link['created_at'])) / 60;
$doHop = $hopOn && $hopUrl && $ageMin >= $after && !$waste;

if ($doHop) { $final = $target; require __DIR__ . '/views/hop.php'; exit; }
header('Location: ' . $target, true, 302);
exit;
