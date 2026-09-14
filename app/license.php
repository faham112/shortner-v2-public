<?php
function current_host(): string {
    $h = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
    return preg_replace('/:\d+$/', '', $h);
}

function normalize_domain(string $d): string {
    $d = strtolower(trim($d));
    $d = preg_replace('/^https?:\/\//', '', $d);
    $d = preg_replace('/\/.*$/', '', $d);
    $d = preg_replace('/^www\./', '', $d);
    return $d;
}

function allowed_domains_list(): array {
    $cfg = $GLOBALS['config'] ?? [];
    $out = [];
    if (!empty($cfg['allowed_domains']) && is_array($cfg['allowed_domains'])) {
        foreach ($cfg['allowed_domains'] as $d) $out[] = normalize_domain((string)$d);
    }
    if (!empty($cfg['allowed_domain'])) $out[] = normalize_domain((string)$cfg['allowed_domain']);
    try {
        $json = setting('install_domains', '');
        if ($json) {
            $arr = json_decode($json, true);
            if (is_array($arr)) foreach ($arr as $d) $out[] = normalize_domain((string)$d);
        }
    } catch (Throwable $e) {}
    return array_values(array_unique(array_filter($out)));
}

function host_allowed(string $host, array $domains): bool {
    $host = normalize_domain($host);
    if ($host === 'localhost' || $host === '127.0.0.1') return true;
    foreach ($domains as $d) {
        if ($host === $d || $host === 'www.'.$d) return true;
    }
    return false;
}

function remote_license_ok(): ?bool {
    $cfg = $GLOBALS['config'] ?? [];
    $hq = rtrim((string)($cfg['license_server'] ?? ''), '/');
    if ($hq === '') return null;
    $key = trim((string)($cfg['license_key'] ?? ''));
    $token = '';
    $domain = current_host();
    try {
        $token = (string)setting('license_token', '');
        $saved = (string)setting('license_domain', '');
        if ($saved) $domain = $saved;
    } catch (Throwable $e) {}
    if ($key === '' || $token === '') return false;
    $url = $hq . '/api/status.php';
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query(['key' => $key, 'domain' => $domain, 'token' => $token]),
        'timeout' => 8,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    $j = json_decode((string)$raw, true);
    if (!is_array($j)) return true;
    return !empty($j['ok']);
}

function license_ok(): bool {
    $cfg = $GLOBALS['config'] ?? [];
    $key = trim((string)($cfg['license_key'] ?? ''));
    if ($key === '' || $key === 'CHANGE_ME') {
        $remote = remote_license_ok();
        return $remote === true;
    }
    $remote = remote_license_ok();
    if ($remote === false) return false;
    $domains = allowed_domains_list();
    if (!$domains) return $remote === true;
    return host_allowed(current_host(), $domains);
}

function license_guard(): void {
    $cfg = $GLOBALS['config'] ?? [];
    $hq = trim((string)($cfg['license_server'] ?? ''));
    $activated = false;
    try { $activated = setting('license_activated', '0') === '1'; } catch (Throwable $e) {}
    if ($hq !== '' && !$activated) {
        header('Location: /activate');
        exit;
    }
    if (license_ok()) return;
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>License</title>';
    echo '<body style="font-family:sans-serif;padding:40px;background:#0b1220;color:#e5e7eb">';
    echo '<h1>License blocked</h1>';
    echo '<p>This license key is not valid on this site. Contact admin.</p>';
    echo '<p>Host: <code>' . h(current_host()) . '</code></p>';
    echo '<p><a href="/activate" style="color:#a5b4fc">Try activate again</a></p></body>';
    exit;
}

function make_license_key(): string {
    return 'SN2-' . strtoupper(bin2hex(random_bytes(10)));
}
