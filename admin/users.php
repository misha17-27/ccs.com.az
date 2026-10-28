<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
require_admin($user);
$db = db();
$self = ADMIN_URL . '/users.php';

$find = static function (int $id) use ($db): ?array {
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
};
$activeAdmins = static fn(): int => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1")->fetchColumn();

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);
    $id = post_int('id');
    $target = $id ? $find($id) : null;

    if ($action === 'delete' && $target) {
        if ((int)$target['id'] === (int)$user['id']) {
            flash('error', t('users.cannot_delete_self'));
        } elseif ($target['role'] === 'admin' && $target['is_active'] && $activeAdmins() <= 1) {
            flash('error', t('users.last_admin'));
        } else {
            $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            flash('success', t('deleted'));
        }
        redirect($self);
    }

    if ($action === 'save') {
        $name = post_str('name', 120);
        $email = post_str('email', 190);
        $role = in_array($_POST['role'] ?? '', ROLES, true) ? $_POST['role'] : 'editor';
        $active = isset($_POST['is_active']) ? 1 : 0;
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';
        $errors = [];

        if (mb_strlen($name) < 2) {
            $errors[] = t('user.name_required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = t('user.email_invalid');
        } else {
            $dupe = $db->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $dupe->execute([$email, $id]);
            if ($dupe->fetchColumn()) {
                $errors[] = t('user.email_taken');
            }
        }
        if (!$target || $password !== '') {
            if ($problem = password_problem($password, $confirm)) {
                $errors[] = $problem;
            }
        }
        if ($target && (int)$target['id'] === (int)$user['id'] && ($role !== 'admin' || !$active)) {
            $errors[] = t('users.cannot_demote_self');
        }
        if ($target && $target['role'] === 'admin' && $target['is_active'] && ($role !== 'admin' || !$active) && $activeAdmins() <= 1) {
            $errors[] = t('users.last_admin');
        }

        if ($errors) {
            foreach ($errors as $err) {
                flash('error', $err);
            }
            redirect($self . ($id ? '?id=' . $id : '?action=new'));
        }

        if ($target) {
            $db->prepare('UPDATE users SET name = ?, email = ?, role = ?, is_active = ?, updated_at = ? WHERE id = ?')
                ->execute([$name, $email, $role, $active, now(), $id]);
            if ($password !== '') {
                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
            }
        } else {
            $db->prepare('INSERT INTO users (name, email, password_hash, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $active, now(), now()]);
        }
        flash('success', t('saved'));
        redirect($self);
    }
    redirect($self);
}

// ---------- Form ----------
$id = (int)($_GET['id'] ?? 0);
if ($id || get_str('action', 10) === 'new') {
    $target = $id ? $find($id) : ['name' => '', 'email' => '', 'role' => 'editor', 'is_active' => 1];
    if (!$target) {
        flash('error', t('not_found'));
        redirect($self);
    }
    admin_header($id ? t('users.edit') : t('users.new'), 'users', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>');
    ?>
    <form method="post" class="card stack" style="max-width: 760px" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="form-grid">
        <label class="field">
          <span class="label"><?= e(t('field.name')) ?> <span class="req">*</span></span>
          <input type="text" name="name" value="<?= e($target['name']) ?>" required>
        </label>
        <label class="field">
          <span class="label"><?= e(t('field.email')) ?> <span class="req">*</span></span>
          <input type="email" name="email" value="<?= e($target['email']) ?>" required autocomplete="off">
        </label>
        <label class="field">
          <span class="label"><?= e(t('field.role')) ?></span>
          <select name="role">
            <?php foreach (ROLES as $r): ?>
              <option value="<?= $r ?>"<?= $r === $target['role'] ? ' selected' : '' ?>><?= e(t('role.' . $r)) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="hint"><?= e(t('users.role_hint')) ?></small>
        </label>
        <div class="field" style="align-content: center"><?= checkbox('is_active', (bool)$target['is_active'], t('users.active')) ?></div>
        <label class="field">
          <span class="label"><?= e($id ? t('users.new_password') : t('field.password')) ?><?= $id ? '' : ' <span class="req">*</span>' ?></span>
          <input type="password" name="password" minlength="10" autocomplete="new-password"<?= $id ? '' : ' required' ?>>
          <small class="hint"><?= e($id ? t('users.password_keep') : t('password.hint')) ?></small>
        </label>
        <label class="field">
          <span class="label"><?= e(t('field.password_confirm')) ?></span>
          <input type="password" name="password_confirm" minlength="10" autocomplete="new-password">
        </label>
      </div>
      <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
    </form>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$rows = $db->query('SELECT * FROM users ORDER BY role, name')->fetchAll();
admin_header(t('nav.users'), 'users', $user,
    '<a class="btn btn--primary" href="' . $self . '?action=new">' . icon('plus') . '<span>' . e(t('users.new')) . '</span></a>');
?>
<section class="card card--flush">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th><?= e(t('field.name')) ?></th><th><?= e(t('field.role')) ?></th><th class="hide-sm"><?= e(t('users.last_login')) ?></th><th><?= e(t('field.status')) ?></th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="<?= $r['is_active'] ? '' : 'is-off' ?>">
          <td><a class="row-link" href="<?= $self ?>?id=<?= (int)$r['id'] ?>"><?= e($r['name']) ?></a>
            <?php if ((int)$r['id'] === (int)$user['id']): ?><span class="badge badge--new"><?= e(t('users.you')) ?></span><?php endif; ?>
            <div class="muted small"><?= e($r['email']) ?></div></td>
          <td><?= e(t('role.' . $r['role'])) ?></td>
          <td class="hide-sm muted"><?= e(format_dt($r['last_login_at'])) ?></td>
          <td><span class="badge badge--<?= $r['is_active'] ? 'on' : 'off' ?>"><?= e($r['is_active'] ? t('state.on') : t('state.off')) ?></span></td>
          <td class="actions">
            <a class="btn btn--ghost btn--sm btn--icon" href="<?= $self ?>?id=<?= (int)$r['id'] ?>" title="<?= e(t('edit')) ?>"><?= icon('edit') ?><span><?= e(t('edit')) ?></span></a>
            <?php if ((int)$r['id'] !== (int)$user['id']): ?>
              <?= action_button($self, ['id' => $r['id'], 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger btn--sm btn--icon', t('confirm.delete')) ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php
admin_footer();
