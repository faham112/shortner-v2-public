<?php
if (empty($isAdmin)) { flash_set('bad', 'Admin only'); redirect(base_url('admin')); }
$action = $_POST['action'] ?? '';

if ($action === 'lic_create') {
    $name = trim($_POST['buyer_name'] ?? '');
    $email = trim($_POST['buyer_email'] ?? '');
    $max = max(1, min(50, (int)($_POST['max_domains'] ?? 1)));
    $exp = trim($_POST['expires_at'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    if ($name === '' || $email === '') {
        flash_set('bad', 'Buyer name + email required');
    } else {
        $key = make_license_key();
        db()->prepare('INSERT INTO licenses (license_key,buyer_name,buyer_email,max_domains,expires_at,notes) VALUES (?,?,?,?,?,?)')
            ->execute([$key, $name, $email, $max, $exp !== '' ? $exp : null, $notes]);
        flash_set('ok', 'License created: ' . $key);
    }
    redirect(base_url('admin/license'));
}

if ($action === 'lic_add_domain') {
    $id = (int)$_POST['license_id'];
    $dom = normalize_domain($_POST['domain'] ?? '');
    $st = db()->prepare('SELECT * FROM licenses WHERE id=?');
    $st->execute([$id]);
    $lic = $st->fetch();
    if (!$lic || !$dom) {
        flash_set('bad', 'Invalid license or domain');
    } else {
        $c = db()->prepare('SELECT COUNT(*) c FROM license_domains WHERE license_id=?');
        $c->execute([$id]);
        $n = (int)$c->fetch()['c'];
        if ($n >= (int)$lic['max_domains']) {
            flash_set('bad', 'Max domains reached for this key');
        } else {
            try {
                db()->prepare('INSERT INTO license_domains (license_id,domain) VALUES (?,?)')->execute([$id, $dom]);
                flash_set('ok', 'Domain added: ' . $dom);
            } catch (Throwable $e) {
                flash_set('bad', 'Domain already on this key');
            }
        }
    }
    redirect(base_url('admin/license'));
}

if ($action === 'lic_del_domain') {
    db()->prepare('DELETE FROM license_domains WHERE id=? AND license_id=?')->execute([(int)$_POST['domain_id'], (int)$_POST['license_id']]);
    flash_set('ok', 'Domain removed');
    redirect(base_url('admin/license'));
}

if ($action === 'lic_toggle') {
    db()->prepare('UPDATE licenses SET is_active = 1-is_active WHERE id=?')->execute([(int)$_POST['license_id']]);
    flash_set('ok', 'License status updated');
    redirect(base_url('admin/license'));
}

if ($action === 'lic_save_install_domains') {
    $raw = trim($_POST['install_domains'] ?? '');
    $parts = preg_split('/[\s,]+/', $raw);
    $clean = [];
    foreach ($parts as $p) {
        $p = normalize_domain($p);
        if ($p !== '') $clean[] = $p;
    }
    $clean = array_values(array_unique($clean));
    set_setting('install_domains', json_encode($clean));
    flash_set('ok', 'This install now allows ' . count($clean) . ' domain(s)');
    redirect(base_url('admin/license'));
}
