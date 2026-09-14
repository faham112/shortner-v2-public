<?php
$code = $route;
$st = db()->prepare('SELECT * FROM links WHERE code=? AND is_active=1 LIMIT 1');
$st->execute([$code]);
$link = $st->fetch();
if (!$link) { http_response_code(404); echo 'Link not found'; exit; }
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$waste = is_android_chrome_app($ua);
[$browser, $os, $device] = parse_ua($ua);
[$country, $city] = geo_country();
$ref = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 255);
$ins = db()->prepare('INSERT INTO clicks (link_id,is_waste,ip,country,city,browser,device,os,referrer,user_agent) VALUES (?,?,?,?,?,?,?,?,?,?)');
$ins->execute([$link['id'], $waste ? 1 : 0, client_ip(), $country, $city, $browser, $device, $os, $ref, substr($ua, 0, 500)]);
if ($waste) db()->prepare('UPDATE links SET waste_clicks=waste_clicks+1 WHERE id=?')->execute([$link['id']]);
else db()->prepare('UPDATE links SET clicks=clicks+1 WHERE id=?')->execute([$link['id']]);
$dump = $link['waste_url'] ?: setting('waste_url', '');
$dest = $link['destination'];
$target = ($waste && $dump) ? $dump : $dest;
$hopOn = setting('hop_enabled', '1') === '1';
$hopUrl = setting('hop_url', '');
$after = (int)setting('hop_after_minutes', '5');
$secs = (int)setting('hop_seconds', '3');
$ageMin = (time() - strtotime($link['created_at'])) / 60;
$doHop = $hopOn && $hopUrl && $ageMin >= $after && !$waste;
if ($doHop) { $final = $target; require __DIR__ . '/views/hop.php'; exit; }
if (!empty($link['preview_on']) && !$waste) { $final = $target; require __DIR__ . '/views/preview.php'; exit; }
header('Location: ' . $target, true, 302);
exit;
