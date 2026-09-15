<?php $final = $final ?? $target ?? $link['destination']; $title = $link['title'] ?: 'Shortner link'; ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?></title>
<style>body{margin:0;font-family:system-ui;color:#fff;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#4c1d95,#7c3aed,#a78bfa)}.card{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);border-radius:18px;padding:28px;max-width:480px}a.btn{display:inline-block;margin-top:16px;background:linear-gradient(135deg,#6d28d9,#7c3aed);color:#fff;text-decoration:none;padding:10px 16px;border-radius:12px;font-weight:700}code{word-break:break-all;color:#ede9fe}</style></head>
<body><div class="card"><h2><?= h($title) ?></h2><p>You are opening:</p><p><code><?= h($final) ?></code></p><a class="btn" href="<?= h($final) ?>">Continue</a></div></body></html>
