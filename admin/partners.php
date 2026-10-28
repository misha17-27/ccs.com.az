<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/partners.php';

$find = static function (int $id) use ($db): ?array {
    $stmt = $db->prepare('SELECT * FROM partners WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
};

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);
    $id = post_int('id');

    if ($action === 'delete' && ($partner = $find($id))) {
        delete_upload($partner['logo']);
        $db->prepare('DELETE FROM partners WHERE id = ?')->execute([$id]);
        flash('success', t('deleted'));
        redirect($self);
    }

    if ($action === 'toggle') {
        $db->prepare('UPDATE partners SET is_active = 1 - is_active, updated_at = ? WHERE id = ?')->execute([now(), $id]);
        redirect($self);
    }

    if ($action === 'save') {
        $existing = $id ? $find($id) : null;
        $name = post_str('name', 160);
        $url = post_str('url', 300);
        $errors = [];
        if ($name === '') {
            $errors[] = t('error.name_required');
        }
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = t('error.url_invalid');
        }
        $uploadFailed = false;
        try {
            $logo = store_upload('logo', 'partners');
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
            $logo = null;
            $uploadFailed = true;
        }
        if (!$logo && !$existing && !$uploadFailed) {
            $errors[] = t('partners.logo_required');
        }
        if ($errors) {
            delete_upload($logo);
            foreach ($errors as $err) {
                flash('error', $err);
            }
            redirect($self . ($id ? '?id=' . $id : '?action=new'));
        }

        $fields = [$name, $logo ?: $existing['logo'] ?? null, $url, post_int('sort'), isset($_POST['is_active']) ? 1 : 0, now()];
        if ($existing) {
            if ($logo) {
                delete_upload($existing['logo']);
            }
            $db->prepare('UPDATE partners SET name = ?, logo = ?, url = ?, sort = ?, is_active = ?, updated_at = ? WHERE id = ?')
                ->execute([...$fields, $id]);
        } else {
            $db->prepare('INSERT INTO partners (name, logo, url, sort, is_active, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([...$fields, now()]);
        }
        flash('success', t('saved'));
        redirect($self);
    }
    redirect($self);
}

// ---------- Form ----------
$id = (int)($_GET['id'] ?? 0);
if ($id || get_str('action', 10) === 'new') {
    $partner = $id ? $find($id) : ['name' => '', 'logo' => '', 'url' => '', 'is_active' => 1,
        'sort' => (int)$db->query('SELECT COALESCE(MAX(sort), 0) + 10 FROM partners')->fetchColumn()];
    if (!$partner) {
        flash('error', t('not_found'));
        redirect($self);
    }
    admin_header($id ? t('partners.edit') : t('partners.add'), 'partners', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>');
    ?>
    <form method="post" enctype="multipart/form-data" class="card stack" style="max-width: 760px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="form-grid">
        <label class="field full">
          <span class="label"><?= e(t('field.name')) ?> <span class="req">*</span></span>
          <input type="text" name="name" value="<?= e($partner['name']) ?>" required maxlength="160">
        </label>
        <div class="field full">
          <span class="label"><?= e(t('partners.logo')) ?><?= $id ? '' : ' <span class="req">*</span>' ?></span>
          <div class="picker">
            <div class="picker__preview picker__preview--contain" id="logo-preview"><?php if ($partner['logo']): ?><img src="<?= e($partner['logo']) ?>" alt=""><?php else: ?><?= icon('image') ?><?php endif; ?></div>
            <div class="stack">
              <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" data-preview="logo-preview"<?= $id ? '' : ' required' ?>>
              <small class="hint"><?= e(t('partners.logo_hint')) ?></small>
            </div>
          </div>
        </div>
        <label class="field">
          <span class="label"><?= e(t('partners.url')) ?></span>
          <input type="url" name="url" value="<?= e($partner['url']) ?>" placeholder="https://instagram.com/…">
        </label>
        <label class="field">
          <span class="label"><?= e(t('field.sort')) ?></span>
          <input type="number" name="sort" value="<?= (int)$partner['sort'] ?>">
        </label>
        <div class="full"><?= checkbox('is_active', (bool)$partner['is_active'], t('field.active')) ?></div>
      </div>
      <div class="form-actions">
        <button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button>
      </div>
    </form>
    <?php if ($id): ?>
      <div><?= action_button($self, ['id' => $id, 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger', t('confirm.delete')) ?></div>
    <?php endif; ?>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$rows = $db->query('SELECT * FROM partners ORDER BY sort, id')->fetchAll();
admin_header(t('nav.partners'), 'partners', $user,
    '<a class="btn btn--primary" href="' . $self . '?action=new">' . icon('plus') . '<span>' . e(t('partners.add')) . '</span></a>');
?>
<?php if (!$rows): ?>
  <section class="card empty"><?= e(t('partners.none')) ?></section>
<?php else: ?>
  <p class="muted small"><?= e(t('partners.count', ['n' => count($rows)])) ?></p>
  <div class="logos">
    <?php foreach ($rows as $p): ?>
      <article class="logo-card<?= $p['is_active'] ? '' : ' is-off' ?>">
        <a href="<?= $self ?>?id=<?= (int)$p['id'] ?>"><img src="<?= e($p['logo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"></a>
        <div class="logo-card__name"><?= e($p['name']) ?></div>
        <div class="logo-card__meta">
          <?= action_button($self, ['id' => $p['id'], 'action' => 'toggle'], $p['is_active'] ? t('state.on') : t('state.off'), $p['is_active'] ? 'check' : 'x', 'btn btn--ghost btn--sm') ?>
          <span>
            <a class="btn btn--ghost btn--sm btn--icon" href="<?= $self ?>?id=<?= (int)$p['id'] ?>" title="<?= e(t('edit')) ?>"><?= icon('edit') ?><span><?= e(t('edit')) ?></span></a>
            <?= action_button($self, ['id' => $p['id'], 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger btn--sm btn--icon', t('confirm.delete')) ?>
          </span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
admin_footer();
