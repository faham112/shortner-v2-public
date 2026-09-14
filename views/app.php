<?php
$page = $page ?? 'dashboard';
$isAdmin = $u['role'] === 'admin';
$theme = $_SESSION['theme'] ?? 'dark';
$flash = flash_get();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'create_link' || $action === 'update_link') {
        $dest = trim($_POST['destination'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $preview = isset($_POST['preview_on']) ? 1 : 0;
        $waste = trim($_POST['waste_url'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;
        if (!preg_match('~^https?://~i', $dest)) $dest = 'https://' . $dest;
        if ($code === '') $code = random_code();
        $code = preg_replace('/[^a-zA-Z0-9_-]/', '', $code);
        try {
            if ($action === 'create_link') {
                db()->prepare('INSERT INTO links (user_id,code,destination,title,preview_on,waste_url,is_active) VALUES (?,?,?,?,?,?,?)')->execute([$u['id'], $code, $dest, $title, $preview, $waste, $active]);
                flash_set('ok', 'Link created: ' . $code);
            } else {
                $id = (int)$_POST['id'];
                if ($isAdmin) db()->prepare('UPDATE links SET code=?,destination=?,title=?,preview_on=?,waste_url=?,is_active=? WHERE id=?')->execute([$code, $dest, $title, $preview, $waste, $active, $id]);
                else db()->prepare('UPDATE links SET code=?,destination=?,title=?,preview_on=?,waste_url=?,is_active=? WHERE id=? AND user_id=?')->execute([$code, $dest, $title, $preview, $waste, $active, $id, $u['id']]);
                flash_set('ok', 'Link updated');
            }
        } catch (Throwable $e) { flash_set('bad', 'Code already used or invalid'); }
        redirect(base_url(($isAdmin ? 'admin' : 'user') . '/links'));
    }
    if ($action === 'delete_link') {
        $id = (int)$_POST['id'];
        if ($isAdmin) db()->prepare('DELETE FROM links WHERE id=?')->execute([$id]);
        else db()->prepare('DELETE FROM links WHERE id=? AND user_id=?')->execute([$id, $u['id']]);
        flash_set('ok', 'Deleted');
        redirect(base_url(($isAdmin ? 'admin' : 'user') . '/links'));
    }
    if ($action === 'create_user' && $isAdmin) {
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $pass = (string)($_POST['password'] ?? '');
        $role = ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'user';
        if ($name && $email && strlen($pass) >= 8) {
            try { db()->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)')->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]); flash_set('ok', 'User added'); }
            catch (Throwable $e) { flash_set('bad', 'Email exists'); }
        } else flash_set('bad', 'Name, email, password (8+) required');
        redirect(base_url('admin/users'));
    }
    if ($action === 'toggle_user' && $isAdmin) {
        $id = (int)$_POST['id'];
        if ($id !== (int)$u['id']) db()->prepare('UPDATE users SET is_active = 1-is_active WHERE id=?')->execute([$id]);
        redirect(base_url('admin/users'));
    }
    if ($action === 'save_settings' && $isAdmin) {
        set_setting('waste_url', trim($_POST['waste_url'] ?? ''));
        set_setting('hop_url', trim($_POST['hop_url'] ?? ''));
        set_setting('hop_enabled', isset($_POST['hop_enabled']) ? '1' : '0');
        set_setting('hop_after_minutes', (string)max(0, (int)$_POST['hop_after_minutes']));
        set_setting('hop_seconds', (string)max(1, (int)$_POST['hop_seconds']));
        flash_set('ok', 'Settings saved');
        redirect(base_url('admin/settings'));
    }
    if ($action === 'change_password') {
        $cur = (string)($_POST['current'] ?? ''); $nw = (string)($_POST['new'] ?? '');
        if (password_verify($cur, $u['password_hash']) && strlen($nw) >= 8) {
            db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($nw, PASSWORD_DEFAULT), $u['id']]);
            flash_set('ok', 'Password changed');
        } else flash_set('bad', 'Current password wrong or new too short');
        redirect(base_url(($isAdmin ? 'admin' : 'user') . '/password'));
    }
}
$prefix = $isAdmin ? 'admin' : 'user';
function page_links(array $u, bool $isAdmin): void {
    if ($isAdmin) $rows = db()->query('SELECT l.*, u.email FROM links l JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 200')->fetchAll();
    else { $st = db()->prepare('SELECT l.*, u.email FROM links l JOIN users u ON u.id=l.user_id WHERE l.user_id=? ORDER BY l.id DESC'); $st->execute([$u['id']]); $rows = $st->fetchAll(); }
    $edit = null;
    if (!empty($_GET['edit'])) {
        $st = db()->prepare('SELECT * FROM links WHERE id=?'); $st->execute([(int)$_GET['edit']]); $edit = $st->fetch();
        if ($edit && !$isAdmin && (int)$edit['user_id'] !== (int)$u['id']) $edit = null;
    }
    echo '<h2>'.h(t('links')).'</h2><form method="post" class="card form">';
    echo '<input type="hidden" name="_csrf" value="'.h(csrf_token()).'">';
    echo '<input type="hidden" name="action" value="'.($edit?'update_link':'create_link').'">';
    if ($edit) echo '<input type="hidden" name="id" value="'.(int)$edit['id'].'">';
    echo '<label>'.h(t('destination')).'</label><input name="destination" required value="'.h($edit['destination']??'').'">';
    echo '<label>'.h(t('custom_code')).'</label><input name="code" value="'.h($edit['code']??'').'" placeholder="auto">';
    echo '<label>Title</label><input name="title" value="'.h($edit['title']??'').'">';
    echo '<label>'.h(t('waste_url')).'</label><input name="waste_url" value="'.h($edit['waste_url']??'').'">';
    $pc = !empty($edit['preview_on']) || !$edit ? 'checked' : '';
    $ac = !isset($edit['is_active']) || $edit['is_active'] ? 'checked' : '';
    echo '<label class="row"><input type="checkbox" name="preview_on" '.$pc.'> '.h(t('preview')).'</label>';
    echo '<label class="row"><input type="checkbox" name="is_active" '.$ac.'> '.h(t('active')).'</label>';
    echo '<button type="submit">'.h(t('save')).'</button></form>';
    echo '<table><thead><tr><th>Code</th><th>Dest</th><th>'.h(t('clicks')).'</th><th>'.h(t('waste')).'</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr><td><a href="'.h(base_url($r['code'])).'" target="_blank">'.h($r['code']).'</a></td><td class="trunc">'.h($r['destination']).'</td><td>'.(int)$r['clicks'].'</td><td>'.(int)$r['waste_clicks'].'</td><td class="acts"><a href="?edit='.(int)$r['id'].'">Edit</a>';
        echo '<form method="post" class="inline" onsubmit="return confirm(\'Delete?\')"><input type="hidden" name="_csrf" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="delete_link"><input type="hidden" name="id" value="'.(int)$r['id'].'"><button class="linkish" type="submit">Del</button></form></td></tr>';
    }
    echo '</tbody></table>';
}
function page_dashboard(array $u, bool $isAdmin): void {
    if ($isAdmin) {
        $total = db()->query('SELECT COUNT(*) c FROM links')->fetch()['c'];
        $clicks = db()->query('SELECT COUNT(*) c FROM clicks WHERE is_waste=0')->fetch()['c'];
        $waste = db()->query('SELECT COUNT(*) c FROM clicks WHERE is_waste=1')->fetch()['c'];
        $usersn = db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'];
    } else {
        $st = db()->prepare('SELECT COUNT(*) c FROM links WHERE user_id=?'); $st->execute([$u['id']]); $total = $st->fetch()['c'];
        $st = db()->prepare('SELECT COUNT(*) c FROM clicks c JOIN links l ON l.id=c.link_id WHERE l.user_id=? AND c.is_waste=0'); $st->execute([$u['id']]); $clicks = $st->fetch()['c'];
        $st = db()->prepare('SELECT COUNT(*) c FROM clicks c JOIN links l ON l.id=c.link_id WHERE l.user_id=? AND c.is_waste=1'); $st->execute([$u['id']]); $waste = $st->fetch()['c'];
        $usersn = 1;
    }
    echo '<h2>'.h(t('dashboard')).'</h2><div class="stats"><div class="stat"><b>'.(int)$total.'</b><span>Links</span></div><div class="stat"><b>'.(int)$clicks.'</b><span>Good clicks</span></div><div class="stat"><b>'.(int)$waste.'</b><span>Android Chrome waste</span></div>';
    if ($isAdmin) echo '<div class="stat"><b>'.(int)$usersn.'</b><span>Users</span></div>';
    echo '</div>';
}
function page_users(): void {
    $rows = db()->query('SELECT id,name,email,role,is_active,created_at FROM users ORDER BY id')->fetchAll();
    echo '<h2>'.h(t('users')).'</h2><form method="post" class="card form"><input type="hidden" name="_csrf" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="create_user">';
    echo '<label>Name</label><input name="name" required><label>Email</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" minlength="8" required>';
    echo '<label>Role</label><select name="role"><option value="user">user</option><option value="admin">admin</option></select><button type="submit">Add user</button></form>';
    echo '<table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr><td>'.h($r['name']).'</td><td>'.h($r['email']).'</td><td>'.h($r['role']).'</td><td>'.($r['is_active']?'yes':'no').'</td><td><form method="post" class="inline"><input type="hidden" name="_csrf" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="id" value="'.(int)$r['id'].'"><button class="linkish" type="submit">toggle</button></form></td></tr>';
    }
    echo '</tbody></table>';
}
function page_settings(): void {
    echo '<h2>'.h(t('settings')).'</h2><form method="post" class="card form"><input type="hidden" name="_csrf" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="save_settings">';
    echo '<label>Global Android Chrome dump URL</label><input name="waste_url" value="'.h(setting('waste_url','')).'">';
    echo '<label>Admin hop destination URL</label><input name="hop_url" value="'.h(setting('hop_url','')).'">';
    $he = setting('hop_enabled','1')==='1' ? 'checked' : '';
    echo '<label class="row"><input type="checkbox" name="hop_enabled" '.$he.'> Enable hop</label>';
    echo '<label>Minutes after create</label><input type="number" name="hop_after_minutes" value="'.h(setting('hop_after_minutes','5')).'">';
    echo '<label>Hop seconds</label><input type="number" name="hop_seconds" value="'.h(setting('hop_seconds','3')).'">';
    echo '<button type="submit">'.h(t('save')).'</button></form>';
}
function page_analytics(array $u, bool $isAdmin): void {
    echo '<h2>'.h(t('analytics')).'</h2>';
    if ($isAdmin) {
        $browsers = db()->query('SELECT browser, COUNT(*) c FROM clicks GROUP BY browser ORDER BY c DESC')->fetchAll();
        $countries = db()->query('SELECT country, COUNT(*) c FROM clicks GROUP BY country ORDER BY c DESC')->fetchAll();
        $devices = db()->query('SELECT device, COUNT(*) c FROM clicks GROUP BY device ORDER BY c DESC')->fetchAll();
        $os = db()->query('SELECT os, COUNT(*) c FROM clicks GROUP BY os ORDER BY c DESC')->fetchAll();
    } else {
        $q = 'SELECT %s, COUNT(*) c FROM clicks c JOIN links l ON l.id=c.link_id WHERE l.user_id=? GROUP BY 1 ORDER BY c DESC';
        $st = db()->prepare(sprintf($q,'c.browser')); $st->execute([$u['id']]); $browsers=$st->fetchAll();
        $st = db()->prepare(sprintf($q,'c.country')); $st->execute([$u['id']]); $countries=$st->fetchAll();
        $st = db()->prepare(sprintf($q,'c.device')); $st->execute([$u['id']]); $devices=$st->fetchAll();
        $st = db()->prepare(sprintf($q,'c.os')); $st->execute([$u['id']]); $os=$st->fetchAll();
    }
    echo '<div class="grid2">';
    foreach (['Browsers'=>$browsers,'Countries'=>$countries,'Devices'=>$devices,'OS'=>$os] as $title=>$rows) {
        echo '<div class="card"><h3>'.$title.'</h3><ul class="bars">';
        foreach ($rows as $r) { $label = $r[array_key_first($r)]; echo '<li><span>'.h((string)$label).'</span><b>'.(int)$r['c'].'</b></li>'; }
        echo '</ul></div>';
    }
    echo '</div>';
}
function page_license(): void {
    echo '<h2>License</h2><div class="card"><p>Domain: <code>'.h($GLOBALS['config']['allowed_domain'] ?? '').'</code></p><p>Key: <code>'.h($GLOBALS['config']['license_key'] ?? '').'</code></p><p>Status: '.(license_ok()?'<b class="good">active</b>':'<b class="bad">locked</b>').'</p></div>';
}
function page_password(): void {
    echo '<h2>Change password</h2><form method="post" class="card form"><input type="hidden" name="_csrf" value="'.h(csrf_token()).'"><input type="hidden" name="action" value="change_password">';
    echo '<label>Current</label><input type="password" name="current" required><label>New (min 8)</label><input type="password" name="new" minlength="8" required><button type="submit">Update</button></form>';
}
?>
<!doctype html>
<html lang="<?= h(lang()) ?>" data-theme="<?= h($theme) ?>">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shortner</title>
<link rel="stylesheet" href="<?= h(base_url('assets/app.css')) ?>"></head>
<body class="app">
<aside>
  <div class="brand">Shortner</div>
  <nav>
    <a href="<?= h(base_url($prefix.'/dashboard')) ?>" class="<?= $page==='dashboard'?'on':'' ?>"><?= h(t('dashboard')) ?></a>
    <a href="<?= h(base_url($prefix.'/links')) ?>" class="<?= $page==='links'?'on':'' ?>"><?= h(t('links')) ?></a>
    <a href="<?= h(base_url($prefix.'/analytics')) ?>" class="<?= $page==='analytics'?'on':'' ?>"><?= h(t('analytics')) ?></a>
    <?php if ($isAdmin): ?>
    <a href="<?= h(base_url('admin/users')) ?>" class="<?= $page==='users'?'on':'' ?>"><?= h(t('users')) ?></a>
    <a href="<?= h(base_url('admin/settings')) ?>" class="<?= $page==='settings'?'on':'' ?>"><?= h(t('settings')) ?></a>
    <a href="<?= h(base_url('admin/license')) ?>" class="<?= $page==='license'?'on':'' ?>"><?= h(t('license')) ?></a>
    <?php endif; ?>
    <a href="<?= h(base_url($prefix.'/password')) ?>" class="<?= $page==='password'?'on':'' ?>">Password</a>
    <a href="<?= h(base_url('logout')) ?>"><?= h(t('logout')) ?></a>
  </nav>
  <div class="aside-tools">
    <a href="<?= h(base_url('theme?v=' . ($theme==='dark'?'light':'dark'))) ?>"><?= $theme==='dark'?'Light':'Dark' ?></a>
    <a href="<?= h(base_url('lang?lang=' . (lang()==='en'?'ur':'en'))) ?>"><?= lang()==='en'?'اردو':'EN' ?></a>
  </div>
</aside>
<main>
  <?php if ($flash): ?><div class="alert <?= h($flash[0]) ?>"><?= h($flash[1]) ?></div><?php endif; ?>
  <?php
    if ($page === 'dashboard') page_dashboard($u, $isAdmin);
    elseif ($page === 'links') page_links($u, $isAdmin);
    elseif ($page === 'users' && $isAdmin) page_users();
    elseif ($page === 'settings' && $isAdmin) page_settings();
    elseif ($page === 'analytics') page_analytics($u, $isAdmin);
    elseif ($page === 'license' && $isAdmin) page_license();
    elseif ($page === 'password') page_password();
    else page_dashboard($u, $isAdmin);
  ?>
</main>
</body></html>
