<?php
/**
 * First run: creates the first administrator. Works only while there are no users,
 * and only with the one-time code written to ccs-private/setup-code.txt on the server.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();

if (users_exist()) {
    redirect(ADMIN_URL . '/login.php');
}

$codeFile = PRIVATE_DIR . '/setup-code.txt';
if (!is_file($codeFile)) {
    file_put_contents($codeFile, strtoupper(bin2hex(random_bytes(5))) . "\n");
    @chmod($codeFile, 0600);
}
$code = trim((string)file_get_contents($codeFile));

$errors = [];
$name = '';
$email = '';

if (is_post()) {
    csrf_check();
    $ip = client_ip();
    $name = post_str('name', 120);
    $email = post_str('email', 190);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';

    if (login_blocked($ip, 'setup')) {
        $errors[] = t('login.blocked');
    } elseif (!hash_equals($code, strtoupper(post_str('code', 40)))) {
        record_login_attempt($ip, 'setup', false);
        $errors[] = t('setup.bad_code');
    } else {
        if (mb_strlen($name) < 2) {
            $errors[] = t('user.name_required');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = t('user.email_invalid');
        }
        if ($problem = password_problem($password, $confirm)) {
            $errors[] = $problem;
        }
    }

    if (!$errors) {
        db()->prepare('INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin', now(), now()]);
        @unlink($codeFile);
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        login_user($stmt->fetch());
        flash('success', t('setup.done'));
        redirect(ADMIN_URL . '/');
    }
}

auth_header(t('setup.title'));
?>
    <h1 class="auth__title"><?= e(t('setup.title')) ?></h1>
    <p class="auth__lead"><?= t('setup.lead', ['file' => '<code>' . e(basename(PRIVATE_DIR)) . '/setup-code.txt</code>']) ?></p>

    <?php foreach ($errors as $error): ?>
      <div class="flash flash--error" role="alert"><?= icon('x') ?><span><?= e($error) ?></span></div>
    <?php endforeach; ?>

    <form method="post" class="stack">
      <?= csrf_field() ?>
      <label class="field">
        <span class="label"><?= e(t('setup.code')) ?></span>
        <input type="text" name="code" required autocomplete="off" spellcheck="false" class="mono">
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.name')) ?></span>
        <input type="text" name="name" value="<?= e($name) ?>" required autocomplete="name">
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.email')) ?></span>
        <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="username">
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.password')) ?></span>
        <input type="password" name="password" required minlength="10" autocomplete="new-password">
        <small class="hint"><?= e(t('password.hint')) ?></small>
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.password_confirm')) ?></span>
        <input type="password" name="password_confirm" required minlength="10" autocomplete="new-password">
      </label>
      <button class="btn btn--dark btn--block btn--lg" type="submit"><?= e(t('setup.submit')) ?></button>
    </form>
<?php
auth_footer();
