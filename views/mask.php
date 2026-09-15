<?php
$title = !empty($link['title']) ? $link['title'] : 'Breaking News';
$description = !empty($link['description']) ? $link['description'] : 'Latest updates and full story';
$image = $link['image_url'] ?? '';
$shortUrl = base_url($link['code']);
$siteName = setting('mask_site_name', 'News Daily');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
header('Referrer-Policy: no-referrer');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?></title>
<meta property="og:type" content="article">
<meta property="og:site_name" content="<?= h($siteName) ?>">
<meta property="og:url" content="<?= h($shortUrl) ?>">
<meta property="og:title" content="<?= h($title) ?>">
<meta property="og:description" content="<?= h($description) ?>">
<?php if ($image): ?>
<meta property="og:image" content="<?= h($image) ?>">
<meta property="og:image:secure_url" content="<?= h($image) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($title) ?>">
<meta name="twitter:description" content="<?= h($description) ?>">
<?php if ($image): ?><meta name="twitter:image" content="<?= h($image) ?>"><?php endif; ?>
<meta name="robots" content="noindex, nofollow">
</head>
<body style="font-family:system-ui;background:linear-gradient(135deg,#4c1d95,#7c3aed);color:#fff;min-height:100vh;margin:0;display:grid;place-items:center">
<div style="max-width:520px;padding:24px;background:rgba(255,255,255,.14);border-radius:18px">
<h1><?= h($title) ?></h1>
<p><?= h($description) ?></p>
<?php if ($image): ?><img src="<?= h($image) ?>" alt="" style="max-width:100%;border-radius:12px"><?php endif; ?>
</div>
</body>
</html>
