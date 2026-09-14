<?php
require __DIR__ . '/app/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = '/' . trim($uri, '/');
if ($uri === '/') $route = '';
else {
    $parts = explode('/', trim($uri, '/'));
    $route = $parts[0] ?? '';
    $param = $parts[1] ?? null;
}

if ($route === 'install') { require __DIR__ . '/install.php'; exit; }
if ($route === 'activate') { require __DIR__ . '/activate.php'; exit; }
if ($route === 'api' && $param === 'activate.php') { require __DIR__ . '/api/activate.php'; exit; }
if ($route === 'api' && $param === 'status.php') { require __DIR__ . '/api/status.php'; exit; }

$reserved = ['login','logout','admin','user','assets','install','password','theme','lang','activate','api'];

if ($route === 'theme') {
    $_SESSION['theme'] = (($_GET['v'] ?? '') === 'light') ? 'light' : 'dark';
    redirect($_SERVER['HTTP_REFERER'] ?? base_url('admin'));
}
if ($route === 'lang') {
    lang();
    redirect($_SERVER['HTTP_REFERER'] ?? base_url('admin'));
}

if ($route === 'login') { require __DIR__ . '/views/login.php'; exit; }
if ($route === 'logout') { logout_user(); redirect(base_url('login')); }

if ($route === 'admin' || $route === 'user') {
    $u = require_login();
    $page = $param ?? 'dashboard';
    if ($route === 'admin' && $u['role'] !== 'admin' && in_array($page, ['users','settings','license'], true)) {
        http_response_code(403); exit('Admin only');
    }
    require __DIR__ . '/views/app.php';
    exit;
}

if ($route === '' || $route === 'index.php') {
    $u = auth_user();
    if ($u) redirect(base_url($u['role'] === 'admin' ? 'admin' : 'user'));
    redirect(base_url('login'));
}

if (!in_array($route, $reserved, true)) {
    require __DIR__ . '/redirect.php';
    exit;
}

http_response_code(404);
echo 'Not found';
