<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();

if (!users_exist()) {
    redirect(ADMIN_URL . '/setup.php');
}
if (current_user()) {
    redirect(ADMIN_URL . '/');
}

// Only redirect back inside the admin area.
$next = get_str('next', 500);
if (!str_starts_with($next, ADMIN_URL . '/') || preg_match('#//|\\\\#', $next)) {
    $next = '';
}

$error = '';
$email = '';
$turnstile = turnstile_enabled();

if (is_post()) {
    csrf_check();
    $email = post_str('email', 190);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $ip = client_ip();

    if (login_blocked($ip, $email)) {
        $error = t('login.blocked');
    } elseif ($turnstile && !turnstile_verify(post_str('cf-turnstile-response', 4096))) {
        $error = t('login.captcha');
    } else {
        $user = check_credentials($email, $password);
        record_login_attempt($ip, $email, $user !== null);
        if ($user) {
            login_user($user);
            redirect($next ?: ADMIN_URL . '/');
        }
        $error = t('login.invalid');
    }
}

auth_header(t('login.title'));
?>
    <h1 class="auth__title"><?= e(t('login.title')) ?></h1>
    <p class="auth__lead"><?= e(t('login.lead')) ?></p>

    <?php if ($error): ?>
      <div class="flash flash--error" role="alert"><?= icon('x') ?><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form method="post" class="stack" autocomplete="on">
      <?= csrf_field() ?>
      <label class="field">
        <span class="label"><?= e(t('field.email')) ?></span>
        <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus>
      </label>
      <label class="field">
        <span class="label"><?= e(t('field.password')) ?></span>
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <?php if ($turnstile): ?>
        <div class="cf-turnstile" data-sitekey="<?= e(setting('turnstile_site_key')) ?>" data-language="<?= e(admin_lang()) ?>"></div>
      <?php endif; ?>
      <button class="btn btn--dark btn--block btn--lg" type="submit"><?= e(t('login.submit')) ?></button>
    </form>
<?php
auth_footer($turnstile);
