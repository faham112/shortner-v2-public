<?php
function migrate_mask_columns(): void {
    static $done = false;
    if ($done) return;
    $done = true;
    $alters = [
        'ALTER TABLE links ADD COLUMN description TEXT NULL',
        'ALTER TABLE links ADD COLUMN image_url TEXT NULL',
        'ALTER TABLE clicks ADD COLUMN is_bot TINYINT(1) NOT NULL DEFAULT 0',
    ];
    foreach ($alters as $sql) {
        try { db()->exec($sql); } catch (Throwable $e) {}
    }
}
