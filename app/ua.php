<?php
function chrome_exclude(string $ua): bool {
    $block = ['edga', 'edg/', 'samsungbrowser', 'opr/', 'opera', 'firefox', 'fxios', 'focus/', 'brave', 'vivaldi', 'duckduckgo', 'instagram', 'fbav', 'yaandroid'];
    foreach ($block as $b) {
        if (str_contains($ua, $b)) return true;
    }
    return false;
}

function is_android_chrome_app(?string $ua = null): bool {
    $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') return false;
    if (!str_contains($ua, 'android')) return false;
    if (!str_contains($ua, 'chrome/')) return false;
    if (str_contains($ua, 'crios')) return false;
    if (str_contains($ua, '; wv')) return false;
    if (chrome_exclude($ua)) return false;
    return true;
}

function is_desktop_chrome(?string $ua = null): bool {
    $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') return false;
    if (str_contains($ua, 'android') || str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'mobile')) return false;
    if (!str_contains($ua, 'chrome/')) return false;
    if (str_contains($ua, 'crios')) return false;
    if (chrome_exclude($ua)) return false;
    return true;
}

function is_preview_bot(?string $ua = null): bool {
    $raw = $ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ua = strtolower($raw);
    if (strlen($raw) < 25) return true;
    $bots = ['whatsapp','facebookexternalhit','facebot','twitterbot','telegrambot','linkedinbot','slackbot','discordbot','pinterest','googlebot','bingbot','yandex','baiduspider','embedly','vkshare','redditbot','applebot','skypeuripreview','viber','meta-externalagent','meta-externalfetcher','preview'];
    foreach ($bots as $b) {
        if (str_contains($ua, $b)) return true;
    }
    return false;
}

function dump_urls_for(string $prefix): array {
    $out = [];
    for ($i = 1; $i <= 5; $i++) {
        $v = trim((string)setting($prefix . '_url_' . $i, ''));
        if ($v !== '') $out[] = $v;
    }
    return $out;
}

function pick_dump_url(string $prefix): ?string {
    $urls = dump_urls_for($prefix);
    if (!$urls) return null;
    return $urls[random_int(0, count($urls) - 1)];
}

function save_preview_image(): string {
    if (empty($_FILES['image_file']['name']) || ($_FILES['image_file']['error'] ?? 1) !== UPLOAD_ERR_OK) {
        return trim($_POST['image_url'] ?? '');
    }
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) return trim($_POST['image_url'] ?? '');
    if (($_FILES['image_file']['size'] ?? 0) > 5 * 1024 * 1024) return trim($_POST['image_url'] ?? '');
    $name = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $dir . '/' . $name)) {
        return trim($_POST['image_url'] ?? '');
    }
    return rtrim(base_url('uploads/' . $name), '/');
}
