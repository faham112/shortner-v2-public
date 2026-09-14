<?php $secs = $secs ?? 3; $hopUrl = $hopUrl ?? ''; $final = $final ?? '/'; ?>
<!doctype html><html><head>
<meta charset="utf-8">
<meta http-equiv="refresh" content="<?= (int)$secs ?>;url=<?= h($final) ?>">
<title>Redirecting</title>
<style>body{margin:0;font-family:system-ui;background:#0b1220;color:#e5e7eb;display:grid;place-items:center;min-height:100vh}.box{text-align:center}iframe{display:none}</style>
</head><body><div class="box"><p>Please wait…</p><p id="c"><?= (int)$secs ?></p></div>
<iframe src="<?= h($hopUrl) ?>"></iframe>
<script>let n=<?= (int)$secs ?>;const el=document.getElementById('c');const t=setInterval(()=>{n--;el.textContent=n;if(n<=0){clearInterval(t);location.href=<?= json_encode($final) ?>;}},1000);setTimeout(()=>{location.href=<?= json_encode($final) ?>;},<?= (int)$secs * 1000 ?>);</script>
</body></html>
