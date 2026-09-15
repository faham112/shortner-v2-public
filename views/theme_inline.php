<style>
html,body{margin:0;min-height:100%;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;color:#fff;background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 50%,#a78bfa 100%);background-attachment:fixed}
*{box-sizing:border-box}a{color:#efe9ff;text-decoration:none}
.app{display:grid;grid-template-columns:240px 1fr;min-height:100vh}
aside{background:rgba(76,29,149,.45);padding:20px}
.brand{font-weight:800;font-size:22px;margin-bottom:16px}
aside nav a{display:block;padding:10px 12px;border-radius:12px;color:#fff;margin-bottom:4px}
aside nav a.on,aside nav a:hover{background:rgba(255,255,255,.22)}
main{padding:24px 24px 96px}
.card,.stat,table{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);border-radius:16px}
.card{padding:18px;margin:16px 0}.stat{padding:18px}.stat b{display:block;font-size:28px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
input,select,textarea{width:100%;padding:11px;margin:6px 0 12px;border-radius:12px;border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.12);color:#fff}
button{background:linear-gradient(135deg,#6d28d9,#7c3aed);border:0;color:#fff;padding:11px 16px;border-radius:12px;font-weight:700;cursor:pointer}
.auth{min-height:100vh;display:grid;place-items:center;padding:20px}.auth .card{width:min(420px,92vw)}
.bottom-nav{position:fixed;left:0;right:0;bottom:0;display:flex;background:rgba(76,29,149,.8);padding:8px}
.bottom-nav a{flex:1;text-align:center;color:#fff;font-size:11px;font-weight:700;padding:8px;border-radius:12px}
.bottom-nav a.on{background:rgba(255,255,255,.22)}
.alert{padding:10px;border-radius:12px;margin-bottom:12px}.alert.ok{background:rgba(22,163,74,.3)}.alert.bad{background:rgba(185,28,28,.35)}
.dump-block{border:1px solid rgba(255,255,255,.25);border-radius:14px;padding:14px;margin-bottom:16px}
@media(max-width:800px){.app{grid-template-columns:1fr}aside{display:none}}
</style>
