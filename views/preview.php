<?php $final = $final ?? $target ?? $link['destination']; $title = $link['title'] ?: 'Shortner link'; ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title) ?></title>
<style>body{margin:0;font-family:Inter,system-ui;background:#0b1220;color:#e5e7eb;display:grid;place-items:center;min-height:100vh}.card{background:#111827;border:1px solid #1f2937;border-radius:16px;padding:28px;max-width:480px}a.btn{display:inline-block;margin-top:16px;background:#4f46e5;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px}code{word-break:break-all;color:#93c5fd}</style></head>
<body><div class="card"><h2><?= h($title) ?></h2><p>You are opening:</p><p><code><?= h($final) ?></code></p><a class="btn" href="<?= h($final) ?>">Continue</a></div></body></html>
