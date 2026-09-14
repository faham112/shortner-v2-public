<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$configFile = $root . '/config.php';

$pathInfo = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
$first = explode('/', $pathInfo)[0] ?? '';
$installing = ($first === 'install');
$activating = ($first === 'activate');
$apiCall = ($first === 'api');

if (!is_file($configFile) && !$installing) {
    header('Location: /install');
    exit;
}

if (is_file($configFile)) {
    $GLOBALS['config'] = require $configFile;
} else {
    $GLOBALS['config'] = ['app_name' => 'Shortner', 'base_url' => '', 'session_name' => 'shortner_sid'];
}

$sessionName = $GLOBALS['config']['session_name'] ?? 'shortner_sid';
session_name($sessionName);
session_start();

require $root . '/app/helpers.php';
require $root . '/app/license.php';
require $root . '/app/ua.php';
require $root . '/app/i18n.php';

if (is_file($configFile) && !$installing && !$activating && !$apiCall) {
    require $root . '/app/db.php';
    require $root . '/app/auth.php';
    license_guard();
}

if ($apiCall && is_file($configFile)) {
    require $root . '/app/db.php';
}
