<?php $secs = $secs ?? 3; $hopUrl = $hopUrl ?? ''; $final = $final ?? '/'; ?>
<!doctype html><html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="<?= (int)$secs ?>;url=<?= h($final) ?>">
<title>Redirecting</title>
<style>body{margin:0;font-family:system-ui;color:#fff;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#4c1d95,#7c3aed,#a78bfa)}.box{text-align:center;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);padding:28px 36px;border-radius:18px}iframe{display:none}#c{font-size:36px;font-weight:800}</style>
</head><body><div class="box"><p>Please wait…</p><p id="c"><?= (int)$secs ?></p></div>
<iframe src="<?= h($hopUrl) ?>"></iframe>
<script>let n=<?= (int)$secs ?>;const el=document.getElementById('c');const t=setInterval(()=>{n--;el.textContent=n;if(n<=0){clearInterval(t);location.href=<?= json_encode($final) ?>;}},1000);setTimeout(()=>{location.href=<?= json_encode($final) ?>;},<?= (int)$secs * 1000 ?>);</script>
</body></html>
