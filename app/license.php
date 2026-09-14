<?php
function current_host(): string {
    $h = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
    $h = preg_replace('/:\d+$/', '', $h);
    return $h;
}
function license_ok(): bool {
    $cfg = $GLOBALS['config'] ?? [];
    $allowed = strtolower(trim($cfg['allowed_domain'] ?? ''));
    $key = trim($cfg['license_key'] ?? '');
    if ($allowed === '' || $key === '' || $key === 'CHANGE_ME') return false;
    $host = current_host();
    if ($host !== $allowed && $host !== 'www.' . $allowed && 'www.' . $host !== $allowed) {
        if ($host !== 'localhost' && $host !== '127.0.0.1') return false;
    }
    return true;
}
function license_guard(): void {
    if (license_ok()) return;
    http_response_code(403);
    echo '<!doctype html><meta charset="utf-8"><title>License</title>';
    echo '<body style="font-family:sans-serif;padding:40px;background:#0b1220;color:#e5e7eb">';
    echo '<h1>Shortner license lock</h1>';
    echo '<p>This copy is bound to a single domain. Host mismatch or missing license key.</p>';
    echo '<p>Current host: <code>' . h(current_host()) . '</code></p></body>';
    exit;
}
function make_license_key(string $domain): string {
    return 'SN2-' . strtoupper(substr(hash('sha256', $domain . '|' . random_bytes(8)), 0, 24));
}
