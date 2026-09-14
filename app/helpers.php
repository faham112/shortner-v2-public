<?php
function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect(string $to): void { header('Location: ' . $to); exit; }
function base_url(string $path = ''): string {
    $cfg = $GLOBALS['config'] ?? [];
    $base = rtrim($cfg['base_url'] ?? '', '/');
    if ($base === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $base = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . '/' . ltrim($path, '/');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    $t = $_POST['_csrf'] ?? '';
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) { http_response_code(419); exit('Invalid CSRF token'); }
}
function setting(string $key, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $rows = db()->query('SELECT skey, svalue FROM settings')->fetchAll();
            foreach ($rows as $r) $cache[$r['skey']] = $r['svalue'];
        } catch (Throwable $e) {}
    }
    return $cache[$key] ?? $default;
}
function set_setting(string $key, string $value): void {
    $st = db()->prepare('INSERT INTO settings (skey, svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue=VALUES(svalue)');
    $st->execute([$key, $value]);
}
function random_code(int $len = 7): string {
    $chars = 'abcdefghjkmnpqrstuvwxyz23456789'; $out = '';
    for ($i = 0; $i < $len; $i++) $out .= $chars[random_int(0, strlen($chars) - 1)];
    return $out;
}
function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? ''; }
function parse_ua(string $ua): array {
    $browser = 'Other'; $os = 'Other'; $device = 'Desktop';
    $l = strtolower($ua);
    if (str_contains($l, 'android')) { $os = 'Android'; $device = 'Mobile'; }
    elseif (str_contains($l, 'iphone') || str_contains($l, 'ipad')) { $os = 'iOS'; $device = str_contains($l, 'ipad') ? 'Tablet' : 'Mobile'; }
    elseif (str_contains($l, 'windows')) $os = 'Windows';
    elseif (str_contains($l, 'mac os')) $os = 'macOS';
    elseif (str_contains($l, 'linux')) $os = 'Linux';
    if (str_contains($l, 'edg/') || str_contains($l, 'edga')) $browser = 'Edge';
    elseif (str_contains($l, 'samsungbrowser')) $browser = 'Samsung Internet';
    elseif (str_contains($l, 'opr/') || str_contains($l, 'opera')) $browser = 'Opera';
    elseif (str_contains($l, 'firefox') || str_contains($l, 'fxios')) $browser = 'Firefox';
    elseif (str_contains($l, 'crios')) $browser = 'Chrome iOS';
    elseif (str_contains($l, 'chrome')) $browser = 'Chrome';
    elseif (str_contains($l, 'safari')) $browser = 'Safari';
    return [$browser, $os, $device];
}
function geo_country(): array {
    $c = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if ($c && $c !== 'XX') return [$c, ''];
    return ['Unknown', ''];
}
function flash_set(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function flash_get(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
