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
    $out = array_values(array_unique(array_filter($out)));
    return $out;
}

function host_allowed(string $host, array $domains): bool {
    $host = normalize_domain($host);
    if ($host === 'localhost' || $host === '127.0.0.1') return true;
    foreach ($domains as $d) {
        if ($host === $d || $host === 'www.'.$d) return true;
    }
    return false;
}

function license_ok(): bool {
    $cfg = $GLOBALS['config'] ?? [];
    $key = trim((string)($cfg['license_key'] ?? ''));
    if ($key === '' || $key === 'CHANGE_ME') return false;
    $domains = allowed_domains_list();
    if (!$domains) return false;
    return host_allowed(current_host(), $domains);
}

function license_guard(): void {
    if (license_ok()) return;
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>License</title>';
    echo '<body style="font-family:sans-serif;padding:40px;background:#0b1220;color:#e5e7eb">';
    echo '<h1>Shortner license lock</h1>';
    echo '<p>License key missing or this domain is not in the allowed list.</p>';
    echo '<p>Current host: <code>' . h(current_host()) . '</code></p></body>';
    exit;
}

function make_license_key(): string {
    return 'SN2-' . strtoupper(bin2hex(random_bytes(10)));
}
