<?php
/** @var string $prefix */
/** @var string $page */
/** @var bool $isAdmin */
$on = function(string $p) use ($page) { return $page === $p ? 'on' : ''; };
?>
<nav class="bottom-nav" aria-label="Bottom">
<?php if ($isAdmin): ?>
  <a class="<?= $on('dashboard') ?>" href="<?= h(base_url('admin/dashboard')) ?>"><span>⌂</span>Home</a>
  <a class="<?= $on('links') ?>" href="<?= h(base_url('admin/links')) ?>"><span>↗</span>Links</a>
  <a class="<?= $on('analytics') ?>" href="<?= h(base_url('admin/analytics')) ?>"><span>◷</span>Stats</a>
  <a class="<?= $on('users') ?>" href="<?= h(base_url('admin/users')) ?>"><span>☺</span>Users</a>
  <a class="<?= $on('settings') ?>" href="<?= h(base_url('admin/settings')) ?>"><span>⚙</span>Set</a>
<?php else: ?>
  <a class="<?= $on('dashboard') ?>" href="<?= h(base_url('user/dashboard')) ?>"><span>⌂</span>Home</a>
  <a class="<?= $on('links') ?>" href="<?= h(base_url('user/links')) ?>"><span>↗</span>Links</a>
  <a class="<?= $on('analytics') ?>" href="<?= h(base_url('user/analytics')) ?>"><span>◷</span>Stats</a>
  <a class="<?= $on('password') ?>" href="<?= h(base_url('user/password')) ?>"><span>☰</span>Account</a>
<?php endif; ?>
</nav>
