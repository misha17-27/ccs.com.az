<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/profile.php';

if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);

    if ($action === 'name') {
        $name = post_str('name', 120);
        if (mb_strlen($name) < 2) {
            flash('error', t('user.name_required'));
        } else {
            $db->prepare('UPDATE users SET name = ?, updated_at = ? WHERE id = ?')->execute([$name, now(), $user['id']]);
            flash('success', t('saved'));
        }
    }

    if ($action === 'password') {
        $current = is_string($_POST['current'] ?? null) ? $_POST['current'] : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';
        if (!password_verify($current, $user['password_hash'])) {
            flash('error', t('profile.current_wrong'));
        } elseif ($problem = password_problem($password, $confirm)) {
            flash('error', $problem);
        } else {
            $db->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?')
                ->execute([password_hash($password, PASSWORD_DEFAULT), now(), $user['id']]);
            session_regenerate_id(true);
            flash('success', t('profile.password_changed'));
        }
    }
    redirect($self);
}

admin_header(t('profile.title'), '', $user);
?>
<div class="stack" style="max-width: 760px">
  <form method="post" class="card stack">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="name">
    <div class="card__head"><h2><?= e(t('profile.data')) ?></h2><span class="badge badge--new"><?= e(t('role.' . $user['role'])) ?></span></div>
    <div class="form-grid">
      <label class="field">
        <span class="label"><?= e(t('field.name')) ?></span>
        <input type="text" name="name" value="<?= e($user['name']) ?>" required>
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.email')) ?></span>
        <input type="email" value="<?= e($user['email']) ?>" disabled>
      </label>
    </div>
    <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
  </form>

  <form method="post" class="card stack" autocomplete="off">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <div class="card__head"><h2><?= e(t('profile.change_password')) ?></h2></div>
    <label class="field">
      <span class="label"><?= e(t('profile.current')) ?></span>
      <input type="password" name="current" required autocomplete="current-password">
    </label>
    <div class="form-grid">
      <label class="field">
        <span class="label"><?= e(t('users.new_password')) ?></span>
        <input type="password" name="password" required minlength="10" autocomplete="new-password">
        <small class="hint"><?= e(t('password.hint')) ?></small>
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.password_confirm')) ?></span>
        <input type="password" name="password_confirm" required minlength="10" autocomplete="new-password">
      </label>
    </div>
    <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
  </form>
</div>
<?php
admin_footer();
