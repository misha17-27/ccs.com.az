<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();

$count = static function (string $sql, array $args = []) use ($db): int {
    $stmt = $db->prepare($sql);
    $stmt->execute($args);
    return (int)$stmt->fetchColumn();
};

$stats = [
    ['new', $count("SELECT COUNT(*) FROM requests WHERE status = 'new'"), 'requests.php?status=new'],
    ['month', $count("SELECT COUNT(*) FROM requests WHERE status <> 'spam' AND created_at >= ?", [date('Y-m-01 00:00:00')]), 'requests.php'],
    ['total', $count("SELECT COUNT(*) FROM requests WHERE status <> 'spam'"), 'requests.php'],
    ['services', $count('SELECT COUNT(*) FROM services WHERE is_active = 1'), 'services.php'],
    ['partners', $count('SELECT COUNT(*) FROM partners WHERE is_active = 1'), 'partners.php'],
];

$recent = $db->query("SELECT * FROM requests WHERE status <> 'spam' ORDER BY id DESC LIMIT 6")->fetchAll();
$byService = $db->query("SELECT COALESCE(NULLIF(service, ''), '—') AS name, COUNT(*) AS n FROM requests
    WHERE status <> 'spam' GROUP BY name ORDER BY n DESC LIMIT 8")->fetchAll();
$maxService = max(array_column($byService, 'n') ?: [1]);

$disk = @disk_free_space(BASE_PATH);
$server = [
    'PHP' => PHP_VERSION,
    'SQLite' => (string)$db->query('SELECT sqlite_version()')->fetchColumn(),
    t('server.db') => is_file(DB_PATH) ? format_bytes((float)filesize(DB_PATH)) : '—',
    t('server.uploads') => format_bytes((float)dir_size(UPLOADS_DIR)),
    t('server.disk') => $disk ? format_bytes($disk) : '—',
    t('server.max_upload') => ini_get('upload_max_filesize') ?: '—',
];

admin_header(t('nav.dashboard'), 'dashboard', $user);
?>
<div class="stats">
  <?php foreach ($stats as $i => [$key, $value, $href]): ?>
    <a class="stat<?= $i === 0 ? ' stat--accent' : '' ?>" href="<?= ADMIN_URL . '/' . e($href) ?>">
      <strong><?= $value ?></strong>
      <span><?= e(t('stat.' . $key)) ?></span>
    </a>
  <?php endforeach; ?>
</div>

<div class="dash">
  <section class="card card--flush">
    <div class="card__head">
      <h2><?= e(t('dash.recent')) ?></h2>
      <a class="btn btn--ghost btn--sm" href="<?= ADMIN_URL ?>/requests.php"><?= e(t('dash.all')) ?> →</a>
    </div>
    <?php if (!$recent): ?>
      <p class="empty"><?= e(t('requests.none')) ?></p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th><?= e(t('field.name')) ?></th><th class="hide-sm"><?= e(t('field.service')) ?></th><th><?= e(t('field.status')) ?></th><th class="hide-sm"><?= e(t('field.date')) ?></th></tr></thead>
          <tbody>
          <?php foreach ($recent as $r): ?>
            <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
              <td><a class="row-link" href="<?= ADMIN_URL ?>/requests.php?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a><div class="muted small"><?= e($r['phone']) ?></div></td>
              <td class="hide-sm"><?= e($r['service'] ?: '—') ?></td>
              <td><?= status_badge($r['status']) ?></td>
              <td class="hide-sm muted"><?= e(format_dt($r['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>

  <div class="dash__side">
    <section class="card">
      <div class="card__head"><h2><?= e(t('dash.by_service')) ?></h2></div>
      <?php if (!$byService): ?>
        <p class="empty"><?= e(t('requests.none')) ?></p>
      <?php else: ?>
        <div class="bars">
          <?php foreach ($byService as $row): ?>
            <div class="bars__row">
              <div class="bars__label"><span><?= e($row['name']) ?></span><b><?= (int)$row['n'] ?></b></div>
              <div class="bars__track"><div class="bars__fill" style="width: <?= round($row['n'] / $maxService * 100) ?>%"></div></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <section class="card">
      <div class="card__head"><h2><?= e(t('dash.quick')) ?></h2></div>
      <div class="quick">
        <a href="<?= ADMIN_URL ?>/services.php"><?= icon('briefcase') ?><?= e(t('nav.services')) ?></a>
        <a href="<?= ADMIN_URL ?>/partners.php?action=new"><?= icon('plus') ?><?= e(t('partners.add')) ?></a>
        <a href="<?= ADMIN_URL ?>/texts.php"><?= icon('sliders') ?><?= e(t('nav.texts')) ?></a>
        <a href="<?= ADMIN_URL ?>/instagram.php"><?= icon('instagram') ?><?= e(t('nav.instagram')) ?></a>
        <?php if (is_admin($user)): ?>
          <a href="<?= ADMIN_URL ?>/settings.php"><?= icon('settings') ?><?= e(t('nav.settings')) ?></a>
        <?php endif; ?>
        <a href="/" target="_blank" rel="noopener"><?= icon('external') ?><?= e(t('view_site')) ?></a>
      </div>
    </section>

    <section class="card">
      <div class="card__head"><h2><?= e(t('dash.server')) ?></h2></div>
      <dl class="kv">
        <?php foreach ($server as $k => $v): ?>
          <dt><?= e($k) ?></dt><dd><?= e($v) ?></dd>
        <?php endforeach; ?>
      </dl>
    </section>
  </div>
</div>
<?php
admin_footer();
