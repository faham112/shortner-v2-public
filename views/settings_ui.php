<?php
$andOn = setting('android_dump_on', '0') === '1';
$deskOn = setting('desktop_dump_on', '0') === '1';
$domainsNow = function_exists('allowed_domains_list') ? allowed_domains_list() : [];
?>
<h2><?= h(t('settings')) ?></h2>
<p class="hint">Admin yahan rules set kare. Links short karne ke liye <a href="<?= h(base_url('admin/links')) ?>">Links</a> kholo.</p>

<form method="post" class="card form" id="setform">
<input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
<input type="hidden" name="action" value="save_settings">

<h3>1. Android Chrome dump</h3>
<div class="dump-block">
  <div class="dump-head">
    <strong>Android Chrome app</strong>
    <label class="switch">
      <input type="checkbox" name="android_dump_on" id="andOn" value="1" <?= $andOn ? 'checked' : '' ?>>
      <span>OFF / ON</span>
    </label>
  </div>
  <p class="hint">ON = waste → neeche wale links (random). OFF = asli destination.</p>
  <div id="andFields" class="dump-fields" style="display:<?= $andOn ? 'block' : 'none' ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <label>Dump link <?= $i ?><?= $i <= 3 ? '' : ' (optional)' ?></label>
      <input type="url" name="android_dump_url_<?= $i ?>" placeholder="https://..." value="<?= h(setting('android_dump_url_'.$i, '')) ?>">
    <?php endfor; ?>
  </div>
</div>

<h3>2. Desktop Chrome dump</h3>
<div class="dump-block">
  <div class="dump-head">
    <strong>Desktop Chrome</strong>
    <label class="switch">
      <input type="checkbox" name="desktop_dump_on" id="deskOn" value="1" <?= $deskOn ? 'checked' : '' ?>>
      <span>OFF / ON</span>
    </label>
  </div>
  <p class="hint">ON = waste → neeche wale links. OFF = asli destination.</p>
  <div id="deskFields" class="dump-fields" style="display:<?= $deskOn ? 'block' : 'none' ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <label>Dump link <?= $i ?><?= $i <= 3 ? '' : ' (optional)' ?></label>
      <input type="url" name="desktop_dump_url_<?= $i ?>" placeholder="https://..." value="<?= h(setting('desktop_dump_url_'.$i, '')) ?>">
    <?php endfor; ?>
  </div>
</div>

<h3>3. 5 min / 3 sec hop</h3>
<div class="dump-block">
<label class="row"><input type="checkbox" name="hop_enabled" <?= setting('hop_enabled','1')==='1'?'checked':'' ?>> Hop ON</label>
<label>Hop URL (admin desired page)</label>
<input name="hop_url" placeholder="https://..." value="<?= h(setting('hop_url','')) ?>">
<label>Minutes after create</label>
<input type="number" name="hop_after_minutes" min="0" value="<?= h(setting('hop_after_minutes','5')) ?>">
<label>Hop seconds</label>
<input type="number" name="hop_seconds" min="1" value="<?= h(setting('hop_seconds','3')) ?>">
</div>

<h3>4. Link preview default</h3>
<div class="dump-block">
<label class="row"><input type="checkbox" name="preview_default" <?= setting('preview_default','1')==='1'?'checked':'' ?>> New links pe preview ON</label>
</div>

<h3>5. This site domains</h3>
<div class="dump-block">
<p class="hint">Yeh HQ / yeh copy in domains pe chalegi. Key: <code><?= h($GLOBALS['config']['license_key'] ?? '') ?></code></p>
<label>Allowed domains (ek line pe ek)</label>
<textarea name="install_domains" rows="4" style="width:100%;border-radius:10px;padding:10px;background:var(--bg);color:var(--text);border:1px solid var(--line)"><?= h(implode("\n", $domainsNow)) ?></textarea>
</div>

<button type="submit"><?= h(t('save')) ?> all settings</button>
</form>
<script>
const tog = (box, fields) => {
  const el = document.getElementById(box);
  const f = document.getElementById(fields);
  if (!el || !f) return;
  const apply = () => { f.style.display = el.checked ? 'block' : 'none'; };
  el.addEventListener('change', apply);
  apply();
};
tog('andOn', 'andFields');
tog('deskOn', 'deskFields');
</script>
