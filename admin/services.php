<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/services.php';

$renders = array_map(static fn($f) => '/assets/img/render/' . basename($f), glob(BASE_PATH . '/assets/img/render/*.jpg') ?: []);

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);
    $id = post_int('id');

    if ($action === 'delete') {
        $stmt = $db->prepare('SELECT image FROM services WHERE id = ?');
        $stmt->execute([$id]);
        delete_upload($stmt->fetchColumn() ?: null);
        $db->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
        flash('success', t('deleted'));
        redirect($self);
    }

    if ($action === 'toggle') {
        $db->prepare('UPDATE services SET is_active = 1 - is_active, updated_at = ? WHERE id = ?')->execute([now(), $id]);
        redirect($self);
    }

    if ($action === 'save') {
        $title = post_ml('title', 200);
        $lead = post_ml('lead', 1000);
        $icon = in_array($_POST['icon'] ?? '', SERVICE_ICONS, true) ? $_POST['icon'] : SERVICE_ICONS[0];
        $image = post_str('image', 300);
        if ($image !== '' && !in_array($image, $renders, true) && !str_starts_with($image, UPLOADS_URL . '/services/')) {
            $image = '';
        }
        $errors = [];
        if ($title['az'] === '') {
            $errors[] = t('error.title_required');
        }
        try {
            $uploaded = store_upload('image_file', 'services');
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
            $uploaded = null;
        }

        $points = [];
        foreach ((array)($_POST['points'] ?? []) as $pt) {
            $pTitle = ml_clean($pt['title'] ?? [], 200);
            $pText = ml_clean($pt['text'] ?? [], 1000);
            if (implode('', $pTitle) . implode('', $pText) !== '') {
                $points[] = [$pTitle, $pText];
            }
        }

        if ($errors) {
            foreach ($errors as $err) {
                flash('error', $err);
            }
            redirect($self . ($id ? '?id=' . $id : '?action=new'));
        }

        $old = null;
        if ($id) {
            $stmt = $db->prepare('SELECT image FROM services WHERE id = ?');
            $stmt->execute([$id]);
            $old = $stmt->fetchColumn() ?: null;
        }
        if ($uploaded) {
            $image = $uploaded;
        }
        if ($old && $old !== $image) {
            delete_upload($old);
        }

        $cols = ['icon' => $icon, 'image' => $image, 'sort' => post_int('sort'), 'is_active' => isset($_POST['is_active']) ? 1 : 0, 'updated_at' => now()];
        foreach (CONTENT_LANGS as $code => $_) {
            $cols['title_' . $code] = $title[$code];
            $cols['lead_' . $code] = $lead[$code];
        }

        $db->beginTransaction();
        if ($id) {
            $set = implode(', ', array_map(static fn($c) => "$c = ?", array_keys($cols)));
            $db->prepare("UPDATE services SET {$set} WHERE id = ?")->execute([...array_values($cols), $id]);
        } else {
            $cols['created_at'] = now();
            $db->prepare('INSERT INTO services (' . implode(', ', array_keys($cols)) . ') VALUES (' . rtrim(str_repeat('?, ', count($cols)), ', ') . ')')
                ->execute(array_values($cols));
            $id = (int)$db->lastInsertId();
        }
        $db->prepare('DELETE FROM service_points WHERE service_id = ?')->execute([$id]);
        $insert = $db->prepare('INSERT INTO service_points (service_id, sort, title_az, title_ru, title_en, text_az, text_ru, text_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($points as $i => [$pTitle, $pText]) {
            $insert->execute([$id, ($i + 1) * 10, $pTitle['az'], $pTitle['ru'], $pTitle['en'], $pText['az'], $pText['ru'], $pText['en']]);
        }
        $db->commit();

        flash('success', t('saved'));
        redirect($self . '?id=' . $id);
    }
    redirect($self);
}

// ---------- Edit form ----------
$id = (int)($_GET['id'] ?? 0);
if ($id || get_str('action', 10) === 'new') {
    $service = ['icon' => SERVICE_ICONS[0], 'image' => '', 'sort' => 0, 'is_active' => 1];
    $points = [];
    if ($id) {
        $stmt = $db->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        if (!$service) {
            flash('error', t('not_found'));
            redirect($self);
        }
        $stmt = $db->prepare('SELECT * FROM service_points WHERE service_id = ? ORDER BY sort, id');
        $stmt->execute([$id]);
        $points = $stmt->fetchAll();
    } else {
        $service['sort'] = (int)$db->query('SELECT COALESCE(MAX(sort), 0) + 10 FROM services')->fetchColumn();
    }

    $pointHtml = static function (string $key, array $pt, int $num): string {
        return '<div class="repeater__item"><div class="repeater__head"><span>' . e(t('services.point')) . ' <span data-repeater-num>' . $num . '</span></span>'
            . '<button type="button" class="btn btn--ghost btn--sm" data-repeater-remove>' . icon('trash') . '<span>' . e(t('delete')) . '</span></button></div>'
            . ml_field("points[{$key}][title]", t('field.title'), row_ml($pt, 'title'))
            . ml_field("points[{$key}][text]", t('field.text'), row_ml($pt, 'text'), 'area')
            . '</div>';
    };

    admin_header($id ? t('services.edit') : t('services.new'), 'services', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>');
    ?>
    <form method="post" enctype="multipart/form-data" class="split">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="stack">
        <section class="card stack">
          <div class="card__head"><h2><?= e(t('services.main')) ?></h2></div>
          <?= ml_field('title', t('field.title'), row_ml($service, 'title'), 'line', true) ?>
          <?= ml_field('lead', t('field.lead'), row_ml($service, 'lead'), 'area') ?>
        </section>
        <section class="card" data-repeater>
          <div class="card__head">
            <h2><?= e(t('services.points')) ?></h2>
            <button type="button" class="btn btn--ghost btn--sm" data-repeater-add><?= icon('plus') ?><span><?= e(t('services.add_point')) ?></span></button>
          </div>
          <div class="repeater" data-repeater-list>
            <?php foreach ($points as $i => $pt): ?>
              <?= $pointHtml((string)$pt['id'], $pt, $i + 1) ?>
            <?php endforeach; ?>
          </div>
          <template><?= $pointHtml('__i__', [], 0) ?></template>
        </section>
      </div>
      <aside class="split__side">
        <section class="card stack">
          <?= checkbox('is_active', (bool)$service['is_active'], t('field.active')) ?>
          <label class="field">
            <span class="label"><?= e(t('field.icon')) ?></span>
            <select name="icon">
              <?php foreach (SERVICE_ICONS as $ic): ?>
                <option value="<?= $ic ?>"<?= $ic === $service['icon'] ? ' selected' : '' ?>><?= e(t('icon.' . $ic)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <div class="field">
            <span class="label"><?= e(t('field.image')) ?></span>
            <div class="picker">
              <div class="picker__preview" id="svc-preview"><?php if ($service['image']): ?><img src="<?= e($service['image']) ?>" alt=""><?php else: ?><?= icon('image') ?><?php endif; ?></div>
            </div>
            <select name="image" data-image-choice="svc-preview">
              <option value=""><?= e(t('services.no_image')) ?></option>
              <?php foreach (array_unique(array_filter([...$renders, $service['image']])) as $img): ?>
                <option value="<?= e($img) ?>"<?= $img === $service['image'] ? ' selected' : '' ?>><?= e(basename($img)) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" data-preview="svc-preview">
            <small class="hint"><?= e(t('upload.hint')) ?></small>
          </div>
          <label class="field">
            <span class="label"><?= e(t('field.sort')) ?></span>
            <input type="number" name="sort" value="<?= (int)$service['sort'] ?>" step="1">
            <small class="hint"><?= e(t('sort.hint')) ?></small>
          </label>
          <button class="btn btn--dark btn--block" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button>
        </section>
      </aside>
    </form>
    <?php if ($id): ?>
      <div><?= action_button($self, ['id' => $id, 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger', t('confirm.delete')) ?></div>
    <?php endif; ?>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$rows = $db->query('SELECT s.*, (SELECT COUNT(*) FROM service_points p WHERE p.service_id = s.id) AS points FROM services s ORDER BY sort, id')->fetchAll();

admin_header(t('nav.services'), 'services', $user,
    '<a class="btn btn--primary" href="' . $self . '?action=new">' . icon('plus') . '<span>' . e(t('services.new')) . '</span></a>');
?>
<section class="card card--flush">
  <?php if (!$rows): ?>
    <p class="empty"><?= e(t('services.none')) ?></p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th></th><th><?= e(t('field.title')) ?></th><th class="hide-sm"><?= e(t('services.points')) ?></th><th class="hide-sm"><?= e(t('field.sort')) ?></th><th><?= e(t('field.status')) ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['is_active'] ? '' : 'is-off' ?>">
            <td><?php if ($r['image']): ?><img class="thumb" src="<?= e($r['image']) ?>" alt=""><?php else: ?><?= icon($r['icon'] ?: 'briefcase') ?><?php endif; ?></td>
            <td><a class="row-link" href="<?= $self ?>?id=<?= (int)$r['id'] ?>"><?= e($r['title_az']) ?></a>
              <div class="muted small"><?= e(implode(' · ', array_filter([$r['title_ru'] ? 'RU' : '', $r['title_en'] ? 'EN' : '']))) ?></div></td>
            <td class="hide-sm"><?= (int)$r['points'] ?></td>
            <td class="hide-sm muted"><?= (int)$r['sort'] ?></td>
            <td><?= action_button($self, ['id' => $r['id'], 'action' => 'toggle'], $r['is_active'] ? t('state.on') : t('state.off'), $r['is_active'] ? 'check' : 'x', 'btn btn--sm ' . ($r['is_active'] ? 'btn--ghost' : 'btn--ghost')) ?></td>
            <td class="actions">
              <a class="btn btn--ghost btn--sm btn--icon" href="<?= $self ?>?id=<?= (int)$r['id'] ?>" title="<?= e(t('edit')) ?>"><?= icon('edit') ?><span><?= e(t('edit')) ?></span></a>
              <?= action_button($self, ['id' => $r['id'], 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger btn--sm btn--icon', t('confirm.delete')) ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php
admin_footer();
