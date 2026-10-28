<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/requests.php';

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $id = post_int('id');
    $action = post_str('action', 20);
    if ($action === 'update') {
        $status = post_str('status', 20);
        if (!in_array($status, REQUEST_STATUSES, true)) {
            $status = 'new';
        }
        $db->prepare('UPDATE requests SET status = ?, note = ?, updated_at = ? WHERE id = ?')
            ->execute([$status, post_str('note', 5000), now(), $id]);
        flash('success', t('saved'));
        redirect($self . '?id=' . $id);
    }
    if ($action === 'delete') {
        $db->prepare('DELETE FROM requests WHERE id = ?')->execute([$id]);
        flash('success', t('deleted'));
        redirect($self);
    }
    redirect($self);
}

// ---------- Filters ----------
$status = get_str('status', 20);
if (!in_array($status, REQUEST_STATUSES, true)) {
    $status = '';
}
$q = get_str('q', 100);
$where = [];
$args = [];
if ($status !== '') {
    $where[] = 'status = ?';
    $args[] = $status;
} else {
    $where[] = "status <> 'spam'";
}
if ($q !== '') {
    $where[] = '(name LIKE ? OR phone LIKE ? OR company LIKE ? OR message LIKE ? OR service LIKE ?)';
    array_push($args, ...array_fill(0, 5, '%' . $q . '%'));
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

// ---------- CSV export ----------
if (get_str('export', 10) === 'csv') {
    $stmt = $db->prepare("SELECT * FROM requests {$whereSql} ORDER BY id DESC");
    $stmt->execute($args);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ccs-sorgular-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads UTF-8
    $cell = static fn($v) => preg_match('/^[=+\-@\t\r]/', (string)$v) ? "'" . $v : (string)$v; // no formula injection
    fputcsv($out, ['ID', t('field.date'), t('field.name'), t('field.phone'), t('field.company'), t('field.service'), t('field.message'), t('field.status'), t('field.note'), t('field.source')], ';');
    while ($r = $stmt->fetch()) {
        fputcsv($out, array_map($cell, [$r['id'], format_dt($r['created_at']), $r['name'], $r['phone'], $r['company'], $r['service'],
            $r['message'], t('status.' . $r['status']), $r['note'], $r['source']]), ';');
    }
    exit;
}

// ---------- Detail ----------
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $db->prepare('SELECT * FROM requests WHERE id = ?');
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if (!$r) {
        flash('error', t('not_found'));
        redirect($self);
    }
    // International digits for tel:/wa.me; local numbers like 055… get the Azerbaijani code.
    $digits = preg_replace('/\D+/', '', $r['phone']);
    if (str_starts_with($digits, '0')) {
        $digits = '994' . substr($digits, 1);
    }
    admin_header(t('requests.item', ['id' => $r['id']]), 'requests', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>');
    ?>
    <div class="split">
      <section class="card">
        <div class="card__head"><h2><?= e($r['name']) ?></h2><?= status_badge($r['status']) ?></div>
        <dl class="detail">
          <dt><?= e(t('field.phone')) ?></dt><dd><?= e($r['phone']) ?></dd>
          <dt><?= e(t('field.company')) ?></dt><dd><?= e($r['company'] ?: '—') ?></dd>
          <dt><?= e(t('field.service')) ?></dt><dd><?= e($r['service'] ?: '—') ?></dd>
          <dt><?= e(t('field.message')) ?></dt><dd><?= e($r['message'] ?: '—') ?></dd>
          <dt><?= e(t('field.date')) ?></dt><dd><?= e(format_dt($r['created_at'])) ?></dd>
          <dt><?= e(t('field.source')) ?></dt><dd><?= e($r['source'] ?: '—') ?></dd>
          <dt>IP</dt><dd class="muted"><?= e($r['ip'] ?: '—') ?></dd>
        </dl>
        <div class="contact-actions" style="margin-top: 22px">
          <a class="btn btn--primary" href="tel:+<?= e($digits) ?>"><?= icon('phone') ?><?= e(t('requests.call')) ?></a>
          <a class="btn btn--ghost" href="https://wa.me/<?= e($digits) ?>" target="_blank" rel="noopener"><?= icon('chat') ?>WhatsApp</a>
        </div>
      </section>
      <div class="split__side">
        <form class="card stack" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <input type="hidden" name="action" value="update">
          <label class="field">
            <span class="label"><?= e(t('field.status')) ?></span>
            <select name="status">
              <?php foreach (REQUEST_STATUSES as $s): ?>
                <option value="<?= $s ?>"<?= $s === $r['status'] ? ' selected' : '' ?>><?= e(t('status.' . $s)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="field">
            <span class="label"><?= e(t('field.note')) ?></span>
            <textarea name="note" rows="5" placeholder="<?= e(t('requests.note_hint')) ?>"><?= e($r['note']) ?></textarea>
          </label>
          <button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button>
        </form>
        <div class="card">
          <?= action_button($self, ['id' => $r['id'], 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger btn--block', t('confirm.delete')) ?>
        </div>
      </div>
    </div>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$perPage = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$total = $db->prepare("SELECT COUNT(*) FROM requests {$whereSql}");
$total->execute($args);
$total = (int)$total->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);

$stmt = $db->prepare("SELECT * FROM requests {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage));
$stmt->execute($args);
$rows = $stmt->fetchAll();

$counts = $db->query('SELECT status, COUNT(*) FROM requests GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$counts[''] = array_sum($counts) - ($counts['spam'] ?? 0);
$query = static fn(array $over) => $self . '?' . http_build_query(array_filter(array_merge(['status' => $status, 'q' => $q], $over), static fn($v) => $v !== '' && $v !== null));

admin_header(t('nav.requests'), 'requests', $user,
    '<a class="btn btn--ghost" href="' . e($query(['export' => 'csv'])) . '">' . icon('download') . '<span>' . e(t('export_csv')) . '</span></a>');
?>
<div class="toolbar">
  <nav class="tabs" aria-label="<?= e(t('field.status')) ?>">
    <?php foreach (array_merge([''], REQUEST_STATUSES) as $s): ?>
      <a href="<?= e($query(['status' => $s, 'page' => null])) ?>" class="<?= $s === $status ? 'is-active' : '' ?>">
        <?= e($s === '' ? t('all') : t('status.' . $s)) ?><b><?= (int)($counts[$s] ?? 0) ?></b>
      </a>
    <?php endforeach; ?>
  </nav>
  <form class="search" method="get">
    <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('search')) ?>" aria-label="<?= e(t('search')) ?>">
    <button class="btn btn--dark" type="submit"><?= icon('search') ?></button>
  </form>
</div>

<section class="card card--flush">
  <?php if (!$rows): ?>
    <p class="empty"><?= e($q !== '' || $status !== '' ? t('nothing_found') : t('requests.none')) ?></p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr>
          <th><?= e(t('field.name')) ?></th>
          <th class="hide-sm"><?= e(t('field.company')) ?></th>
          <th class="hide-sm"><?= e(t('field.service')) ?></th>
          <th><?= e(t('field.status')) ?></th>
          <th class="hide-sm"><?= e(t('field.date')) ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
            <td><a class="row-link" href="<?= $self ?>?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a><div class="muted small"><?= e($r['phone']) ?></div></td>
            <td class="hide-sm"><?= e($r['company'] ?: '—') ?></td>
            <td class="hide-sm"><?= e($r['service'] ?: '—') ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td class="hide-sm muted"><?= e(format_dt($r['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="pages">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <?php if ($p === $page): ?><span><?= $p ?></span><?php else: ?><a href="<?= e($query(['page' => $p])) ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>
<?php
admin_footer();
