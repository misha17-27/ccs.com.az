<?php
/** Projects of the "International experience" block: tag, text, photo caption and up to four photos. */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/projects.php';

$find = static function (int $id) use ($db): ?array {
    $stmt = $db->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
};
$photosOf = static function (int $id) use ($db): array {
    $stmt = $db->prepare('SELECT * FROM project_photos WHERE project_id = ? ORDER BY sort, id');
    $stmt->execute([$id]);
    return $stmt->fetchAll();
};

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);
    $id = post_int('id');

    if ($action === 'delete' && $find($id)) {
        foreach ($photosOf($id) as $photo) {
            delete_upload($photo['path']);
        }
        $db->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);   // photos go with it (ON DELETE CASCADE)
        flash('success', t('deleted'));
        redirect($self);
    }

    if ($action === 'toggle') {
        $db->prepare('UPDATE projects SET is_active = 1 - is_active, updated_at = ? WHERE id = ?')->execute([now(), $id]);
        redirect($self);
    }

    if ($action === 'save') {
        $existing = $id ? $find($id) : null;
        if ($id && !$existing) {
            flash('error', t('not_found'));
            redirect($self);
        }
        $tag = post_ml('tag', 40);
        $text = post_ml('text', 1000);
        $caption = post_ml('caption', 160);
        if ($text['az'] === '') {
            flash('error', t('projects.text_required'));
            redirect($self . ($id ? '?id=' . $id : '?action=new'));
        }

        $cols = ['sort' => post_int('sort'), 'is_active' => isset($_POST['is_active']) ? 1 : 0, 'updated_at' => now()];
        foreach (array_keys(CONTENT_LANGS) as $code) {
            $cols['tag_' . $code] = $tag[$code];
            $cols['text_' . $code] = $text[$code];
            $cols['caption_' . $code] = $caption[$code];
        }

        // Photos already there: focus, order, removal
        $posted = (array)($_POST['photos'] ?? []);
        $removed = [];
        $kept = 0;
        $db->beginTransaction();
        if ($existing) {
            $set = implode(', ', array_map(static fn($c) => "$c = ?", array_keys($cols)));
            $db->prepare("UPDATE projects SET {$set} WHERE id = ?")->execute([...array_values($cols), $id]);
            foreach ($photosOf($id) as $photo) {
                $p = (array)($posted[$photo['id']] ?? []);
                if (!empty($p['delete'])) {
                    $db->prepare('DELETE FROM project_photos WHERE id = ?')->execute([$photo['id']]);
                    $removed[] = $photo['path'];
                    continue;
                }
                $focus = isset(PHOTO_FOCUS[$p['focus'] ?? '']) ? $p['focus'] : $photo['focus'];
                $db->prepare('UPDATE project_photos SET focus = ?, sort = ? WHERE id = ?')
                    ->execute([$focus, (int)($p['sort'] ?? $photo['sort']), $photo['id']]);
                $kept++;
            }
        } else {
            $cols['created_at'] = now();
            $db->prepare('INSERT INTO projects (' . implode(', ', array_keys($cols)) . ') VALUES (' . rtrim(str_repeat('?, ', count($cols)), ', ') . ')')
                ->execute(array_values($cols));
            $id = (int)$db->lastInsertId();
        }
        $db->commit();
        foreach ($removed as $path) {
            delete_upload($path);
        }

        // New photos go after the others; large phone photos are scaled down
        $files = uploaded_files('new_photos');
        if (count($files) > PROJECT_PHOTOS_MAX - $kept) {
            flash('error', t('projects.too_many', ['n' => PROJECT_PHOTOS_MAX]));
            $files = array_slice($files, 0, max(0, PROJECT_PHOTOS_MAX - $kept));
        }
        $stmt = $db->prepare('SELECT COALESCE(MAX(sort), 0) FROM project_photos WHERE project_id = ?');
        $stmt->execute([$id]);
        $sort = (int)$stmt->fetchColumn();
        $insert = $db->prepare('INSERT INTO project_photos (project_id, path, focus, sort) VALUES (?, ?, ?, ?)');
        foreach ($files as $file) {
            try {
                $path = store_upload_file($file, 'projects');
                shrink_photo($path);
                $insert->execute([$id, $path, 'center', $sort += 10]);
            } catch (RuntimeException $ex) {
                flash('error', $file['name'] . ': ' . $ex->getMessage());
            }
        }

        flash('success', t('saved'));
        redirect($self . '?id=' . $id);
    }
    redirect($self);
}

// ---------- Form ----------
$id = (int)($_GET['id'] ?? 0);
if ($id || get_str('action', 10) === 'new') {
    $project = ['sort' => (int)$db->query('SELECT COALESCE(MAX(sort), 0) + 10 FROM projects')->fetchColumn(), 'is_active' => 1];
    $photos = [];
    if ($id) {
        $project = $find($id);
        if (!$project) {
            flash('error', t('not_found'));
            redirect($self);
        }
        $photos = $photosOf($id);
    }
    admin_header($id ? t('projects.edit') : t('projects.new'), 'projects', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>');
    ?>
    <form method="post" enctype="multipart/form-data" class="split">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="stack">
        <section class="card stack">
          <?= ml_field('tag', t('projects.tag'), row_ml($project, 'tag'), 'line', false, 40) ?>
          <small class="hint"><?= e(t('projects.tag_hint')) ?></small>
          <?= ml_field('text', t('field.text'), row_ml($project, 'text'), 'area', true) ?>
          <small class="hint"><?= e(t('texts.format_hint')) ?></small>
        </section>
        <section class="card stack">
          <div class="card__head"><h2><?= e(t('projects.photos')) ?></h2><span class="muted small"><?= count($photos) ?> / <?= PROJECT_PHOTOS_MAX ?></span></div>
          <?php if ($photos): ?>
            <div class="photo-grid">
              <?php foreach ($photos as $i => $photo): $pid = (int)$photo['id']; ?>
                <div class="photo-card">
                  <img src="<?= e($photo['path']) ?>" alt="" style="object-position:<?= PHOTO_FOCUS[$photo['focus']] ?? PHOTO_FOCUS['center'] ?>">
                  <?php if ($i === 0): ?><span class="photo-card__badge"><?= e(t('projects.main_photo')) ?></span><?php endif; ?>
                  <label class="field">
                    <span class="label"><?= e(t('projects.focus')) ?></span>
                    <select name="photos[<?= $pid ?>][focus]">
                      <?php foreach (array_keys(PHOTO_FOCUS) as $f): ?>
                        <option value="<?= $f ?>"<?= $f === $photo['focus'] ? ' selected' : '' ?>><?= e(t('focus.' . $f)) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </label>
                  <label class="field">
                    <span class="label"><?= e(t('field.sort')) ?></span>
                    <input type="number" name="photos[<?= $pid ?>][sort]" value="<?= (int)$photo['sort'] ?>" step="1">
                  </label>
                  <?= checkbox('photos[' . $pid . '][delete]', false, t('projects.remove_photo')) ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if (count($photos) < PROJECT_PHOTOS_MAX): ?>
            <label class="field">
              <span class="label"><?= e(t('projects.add_photos')) ?></span>
              <input type="file" name="new_photos[]" accept="image/jpeg,image/png,image/webp" multiple>
            </label>
          <?php endif; ?>
          <small class="hint"><?= e(t('projects.photos_hint', ['n' => PROJECT_PHOTOS_MAX, 'mb' => MAX_UPLOAD_BYTES / 1048576])) ?></small>
          <?= ml_field('caption', t('projects.caption'), row_ml($project, 'caption'), 'line', false, 160) ?>
          <small class="hint"><?= e(t('projects.caption_hint')) ?></small>
        </section>
      </div>
      <aside class="split__side">
        <section class="card stack">
          <?= checkbox('is_active', (bool)$project['is_active'], t('field.active')) ?>
          <label class="field">
            <span class="label"><?= e(t('field.sort')) ?></span>
            <input type="number" name="sort" value="<?= (int)$project['sort'] ?>" step="1">
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
$rows = $db->query('SELECT p.*, (SELECT COUNT(*) FROM project_photos f WHERE f.project_id = p.id) AS photos,
    (SELECT path FROM project_photos f WHERE f.project_id = p.id ORDER BY sort, id LIMIT 1) AS cover FROM projects p ORDER BY sort, id')->fetchAll();

admin_header(t('nav.projects'), 'projects', $user,
    '<a class="btn btn--primary" href="' . $self . '?action=new">' . icon('plus') . '<span>' . e(t('projects.new')) . '</span></a>');
?>
<p class="muted small"><?= e(t('projects.how')) ?> <a class="inline-link" href="texts.php?g=experience"><?= e(t('projects.title_link')) ?></a></p>
<section class="card card--flush">
  <?php if (!$rows): ?>
    <p class="empty"><?= e(t('projects.none')) ?></p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th></th><th><?= e(t('field.text')) ?></th><th class="hide-sm"><?= e(t('projects.photos')) ?></th><th class="hide-sm"><?= e(t('field.sort')) ?></th><th><?= e(t('field.status')) ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['is_active'] ? '' : 'is-off' ?>">
            <td><?php if ($r['cover']): ?><img class="thumb" src="<?= e($r['cover']) ?>" alt=""><?php else: ?><?= icon('award') ?><?php endif; ?></td>
            <td><a class="row-link" href="<?= $self ?>?id=<?= (int)$r['id'] ?>"><?= e($r['tag_az'] !== '' ? $r['tag_az'] : mb_strimwidth((string)$r['text_az'], 0, 60, '…')) ?></a>
              <div class="muted small"><?= e(mb_strimwidth(str_replace('*', '', (string)$r['text_az']), 0, 120, '…')) ?></div></td>
            <td class="hide-sm"><?= (int)$r['photos'] ?></td>
            <td class="hide-sm muted"><?= (int)$r['sort'] ?></td>
            <td><?= action_button($self, ['id' => $r['id'], 'action' => 'toggle'], $r['is_active'] ? t('state.on') : t('state.off'), $r['is_active'] ? 'check' : 'x', 'btn btn--ghost btn--sm') ?></td>
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
