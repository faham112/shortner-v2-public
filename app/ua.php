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
