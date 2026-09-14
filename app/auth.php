<?php
function auth_user(): ?array {
    if (!empty($_SESSION['uid'])) {
        $st = db()->prepare('SELECT * FROM users WHERE id=? AND is_active=1');
        $st->execute([$_SESSION['uid']]);
        $u = $st->fetch();
        return $u ?: null;
    }
    if (!empty($_COOKIE['sn_remember'])) {
        $raw = $_COOKIE['sn_remember'];
        $hash = hash('sha256', $raw);
        $st = db()->prepare('SELECT u.* FROM remember_tokens t JOIN users u ON u.id=t.user_id WHERE t.token_hash=? AND t.expires_at > NOW() AND u.is_active=1');
        $st->execute([$hash]);
        $u = $st->fetch();
        if ($u) { $_SESSION['uid'] = $u['id']; return $u; }
    }
    return null;
}
function require_login(): array {
    $u = auth_user();
    if (!$u) redirect(base_url('login'));
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') { http_response_code(403); exit('Admin only'); }
    return $u;
}
function login_user(array $user, bool $remember): void {
    session_regenerate_id(true);
    $_SESSION['uid'] = $user['id'];
    if ($remember) {
        $raw = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $days = (int)($GLOBALS['config']['remember_days'] ?? 30);
        $st = db()->prepare('INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?,?, DATE_ADD(NOW(), INTERVAL ? DAY))');
        $st->execute([$user['id'], $hash, $days]);
        setcookie('sn_remember', $raw, ['expires' => time() + $days * 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    }
}
function logout_user(): void {
    if (!empty($_COOKIE['sn_remember'])) {
        $hash = hash('sha256', $_COOKIE['sn_remember']);
        db()->prepare('DELETE FROM remember_tokens WHERE token_hash=?')->execute([$hash]);
        setcookie('sn_remember', '', time() - 3600, '/');
    }
    $_SESSION = [];
    session_destroy();
}
