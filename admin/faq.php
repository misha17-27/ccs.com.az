<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/faq.php';

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);
    $id = post_int('id');

    if ($action === 'delete') {
        $db->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
        flash('success', t('deleted'));
        redirect($self);
    }
    if ($action === 'toggle') {
        $db->prepare('UPDATE faqs SET is_active = 1 - is_active, updated_at = ? WHERE id = ?')->execute([now(), $id]);
        redirect($self);
    }
    if ($action === 'save') {
        $question = post_ml('question', 300);
        $answer = post_ml('answer', 3000);
        if ($question['az'] === '') {
            flash('error', t('faq.question_required'));
            redirect($self . ($id ? '?id=' . $id : '?action=new'));
        }
        $cols = ['sort' => post_int('sort'), 'is_active' => isset($_POST['is_active']) ? 1 : 0, 'updated_at' => now()];
        foreach (array_keys(CONTENT_LANGS) as $code) {
            $cols['question_' . $code] = $question[$code];
            $cols['answer_' . $code] = $answer[$code];
        }
        if ($id) {
            $set = implode(', ', array_map(static fn($c) => "$c = ?", array_keys($cols)));
            $db->prepare("UPDATE faqs SET {$set} WHERE id = ?")->execute([...array_values($cols), $id]);
        } else {
            $cols['created_at'] = now();
            $db->prepare('INSERT INTO faqs (' . implode(', ', array_keys($cols)) . ') VALUES (' . rtrim(str_repeat('?, ', count($cols)), ', ') . ')')
                ->execute(array_values($cols));
        }
        flash('success', t('saved'));
        redirect($self);
    }
    redirect($self);
}

// ---------- Form ----------
$id = (int)($_GET['id'] ?? 0);
if ($id || get_str('action', 10) === 'new') {
    $faq = ['sort' => (int)$db->query('SELECT COALESCE(MAX(sort), 0) + 10 FROM faqs')->fetchColumn(), 'is_active' => 1];
    if ($id) {
        $stmt = $db->prepare('SELECT * FROM faqs WHERE id = ?');
        $stmt->execute([$id]);
        $faq = $stmt->fetch();
        if (!$faq) {
            flash('error', t('not_found'));
            redirect($self);
        }
    }
    admin_header($id ? t('faq.edit') : t('faq.new'), 'faq', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>');
    ?>
    <form method="post" class="card stack" style="max-width: 860px">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <?= ml_field('question', t('faq.question'), row_ml($faq, 'question'), 'line', true) ?>
      <?= ml_field('answer', t('faq.answer'), row_ml($faq, 'answer'), 'area') ?>
      <div class="form-grid">
        <label class="field">
          <span class="label"><?= e(t('field.sort')) ?></span>
          <input type="number" name="sort" value="<?= (int)$faq['sort'] ?>">
        </label>
        <div class="field" style="align-content: end"><?= checkbox('is_active', (bool)$faq['is_active'], t('field.active')) ?></div>
      </div>
      <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
    </form>
    <?php if ($id): ?>
      <div><?= action_button($self, ['id' => $id, 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger', t('confirm.delete')) ?></div>
    <?php endif; ?>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$rows = $db->query('SELECT * FROM faqs ORDER BY sort, id')->fetchAll();
admin_header(t('nav.faq'), 'faq', $user,
    '<a class="btn btn--primary" href="' . $self . '?action=new">' . icon('plus') . '<span>' . e(t('faq.new')) . '</span></a>');
?>
<section class="card card--flush">
  <?php if (!$rows): ?>
    <p class="empty"><?= e(t('faq.none')) ?></p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th><?= e(t('faq.question')) ?></th><th class="hide-sm"><?= e(t('field.sort')) ?></th><th><?= e(t('field.status')) ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['is_active'] ? '' : 'is-off' ?>">
            <td><a class="row-link" href="<?= $self ?>?id=<?= (int)$r['id'] ?>"><?= e($r['question_az']) ?></a>
              <div class="muted small"><?= e(mb_strimwidth((string)$r['answer_az'], 0, 120, '…')) ?></div></td>
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
