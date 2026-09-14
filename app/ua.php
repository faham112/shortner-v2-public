<?php
function is_android_chrome_app(?string $ua = null): bool {
    $ua = strtolower($ua ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($ua === '') return false;
    if (!str_contains($ua, 'android')) return false;
    if (!str_contains($ua, 'chrome/')) return false;
    if (str_contains($ua, 'crios')) return false;
    if (str_contains($ua, '; wv')) return false;
    $block = ['edga', 'edg/', 'samsungbrowser', 'opr/', 'opera', 'firefox', 'fxios', 'focus/', 'brave', 'vivaldi', 'duckduckgo', 'instagram', 'fbav', 'yaandroid'];
    foreach ($block as $b) {
        if (str_contains($ua, $b)) return false;
    }
    return true;
}
