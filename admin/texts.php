<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/texts.php';

$group = get_str('g', 30);
if (!isset(TEXT_GROUPS[$group])) {
    $group = 'hero';
}
$fields = array_filter(TEXT_FIELDS, static fn($def) => $def[0] === $group);

if (is_post()) {
    csrf_check();
    $posted = $_POST['t'] ?? [];
    $update = $db->prepare('UPDATE texts SET value_az = ?, value_ru = ?, value_en = ?, updated_at = ? WHERE key = ?');
    $insert = $db->prepare('INSERT INTO texts (value_az, value_ru, value_en, updated_at, key) VALUES (?, ?, ?, ?, ?)');
    $db->beginTransaction();
    foreach ($fields as $key => $def) {
        $value = ml_clean(is_array($posted) ? ($posted[$key] ?? []) : [], $def[3] === 'area' ? 5000 : 500);
        $args = [$value['az'], $value['ru'], $value['en'], now(), $key];
        $update->execute($args);
        if ($update->rowCount() === 0) {
            $insert->execute($args);
        }
    }
    $db->commit();
    flash('success', t('saved'));
    redirect($self . '?g=' . $group);
}

$rows = [];
foreach ($db->query('SELECT * FROM texts') as $row) {
    $rows[$row['key']] = $row;
}

admin_header(t('nav.texts'), $group === 'about' ? 'about' : 'texts', $user);
?>
<nav class="tabs">
  <?php foreach (TEXT_GROUPS as $g => $labels): ?>
    <a href="<?= $self ?>?g=<?= $g ?>" class="<?= $g === $group ? 'is-active' : '' ?>"><?= e(tl($labels)) ?></a>
  <?php endforeach; ?>
</nav>

<form method="post" class="card stack" style="max-width: 980px">
  <?= csrf_field() ?>
  <div class="card__head">
    <h2><?= e(tl(TEXT_GROUPS[$group])) ?></h2>
    <div class="ml__tabs" aria-label="<?= e(t('texts.show_lang')) ?>">
      <?php foreach (CONTENT_LANGS as $code => $short): ?>
        <button type="button" class="<?= $code === 'az' ? 'is-active is-filled' : 'is-filled' ?>" data-ml-all="<?= $code ?>"><?= $short ?></button>
      <?php endforeach; ?>
    </div>
  </div>
  <?php foreach ($fields as $key => [$g, $labelAz, $labelRu, $type]): ?>
    <?= ml_field("t[{$key}]", admin_lang() === 'ru' ? $labelRu : $labelAz, row_ml($rows[$key] ?? [], 'value'), $type, true) ?>
  <?php endforeach; ?>
  <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
</form>
<?php
admin_footer();
