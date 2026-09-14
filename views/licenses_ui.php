<?php
$domainsNow = allowed_domains_list();
$sold = [];
try {
    $sold = db()->query('SELECT * FROM licenses ORDER BY id DESC')->fetchAll();
} catch (Throwable $e) {
    echo '<div class="alert bad">Run schema update — licenses tables missing. Import schema.sql or the licenses CREATE TABLE block.</div>';
}
$map = [];
try {
    foreach (db()->query('SELECT * FROM license_domains')->fetchAll() as $d) {
        $map[$d['license_id']][] = $d;
    }
} catch (Throwable $e) {}
?>
<h2>License + domains</h2>

<div class="card form">
  <h3>This install (multi-domain lock)</h3>
  <p class="hint">Yeh copy in domains pe chalegi. Key: <code><?= h($GLOBALS['config']['license_key'] ?? '') ?></code></p>
  <p>Current host: <code><?= h(current_host()) ?></code> — <?= license_ok() ? '<b class="good">OK</b>' : '<b class="bad">LOCKED</b>' ?></p>
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="lic_save_install_domains">
    <label>Allowed domains (comma or new line)</label>
    <textarea name="install_domains" rows="4" style="width:100%;border-radius:10px;padding:10px;background:var(--bg);color:var(--text);border:1px solid var(--line)"><?= h(implode("\n", $domainsNow)) ?></textarea>
    <button type="submit">Save domains</button>
  </form>
</div>

<div class="card form">
  <h3>Sell a license key</h3>
  <p class="hint">Customer ko key do. Us key pe max domains bind karo.</p>
  <form method="post">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="lic_create">
    <label>Buyer name</label><input name="buyer_name" required>
    <label>Buyer email</label><input type="email" name="buyer_email" required>
    <label>Max domains</label><input type="number" name="max_domains" value="1" min="1" max="50">
    <label>Expiry (optional)</label><input type="date" name="expires_at">
    <label>Notes</label><input name="notes" placeholder="Plan / WhatsApp">
    <button type="submit">Generate key</button>
  </form>
</div>

<?php foreach ($sold as $lic): $doms = $map[$lic['id']] ?? []; ?>
<div class="card">
  <p><b><?= h($lic['buyer_name']) ?></b> · <?= h($lic['buyer_email']) ?>
    · <?= $lic['is_active'] ? '<span class="good">active</span>' : '<span class="bad">off</span>' ?>
    · <?= count($doms) ?>/<?= (int)$lic['max_domains'] ?> domains
    <?php if ($lic['expires_at']): ?> · exp <?= h($lic['expires_at']) ?><?php endif; ?>
  </p>
  <p><code><?= h($lic['license_key']) ?></code></p>
  <ul>
    <?php foreach ($doms as $d): ?>
      <li><?= h($d['domain']) ?>
        <form method="post" class="inline">
          <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="action" value="lic_del_domain">
          <input type="hidden" name="license_id" value="<?= (int)$lic['id'] ?>">
          <input type="hidden" name="domain_id" value="<?= (int)$d['id'] ?>">
          <button class="linkish" type="submit">remove</button>
        </form>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php if (count($doms) < (int)$lic['max_domains'] && $lic['is_active']): ?>
  <form method="post" class="row" style="gap:8px;align-items:flex-end">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="lic_add_domain">
    <input type="hidden" name="license_id" value="<?= (int)$lic['id'] ?>">
    <input name="domain" placeholder="customer.com" required>
    <button type="submit">Add domain</button>
  </form>
  <?php endif; ?>
  <form method="post" class="inline">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="action" value="lic_toggle">
    <input type="hidden" name="license_id" value="<?= (int)$lic['id'] ?>">
    <button type="submit"><?= $lic['is_active'] ? 'Disable key' : 'Enable key' ?></button>
  </form>
</div>
<?php endforeach; ?>
