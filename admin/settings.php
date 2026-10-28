<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
require_admin($user);
$self = ADMIN_URL . '/settings.php';

if (is_post()) {
    csrf_check();
    $errors = [];
    $values = [];
    foreach (SETTING_FIELDS as $key => [$section, $az, $ru, $type]) {
        $value = post_str($key, 500);
        if ($type === 'password' && $value === '') {
            continue; // empty secret field = keep the saved one
        }
        if ($value !== '' && $type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[] = t('error.email_field', ['field' => admin_lang() === 'ru' ? $ru : $az]);
        }
        if ($value !== '' && $type === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $errors[] = t('error.url_field', ['field' => admin_lang() === 'ru' ? $ru : $az]);
        }
        if ($key === 'contact_whatsapp') {
            $value = preg_replace('/\D+/', '', $value);
        }
        $values[$key] = $value;
    }
    if (isset($_POST['turnstile_clear'])) {
        $values['turnstile_site_key'] = '';
        $values['turnstile_secret_key'] = '';
    }
    if ($errors) {
        foreach ($errors as $err) {
            flash('error', $err);
        }
    } else {
        foreach ($values as $key => $value) {
            save_setting($key, $value);
        }
        flash('success', t('saved'));
    }
    redirect($self);
}

admin_header(t('nav.settings'), 'settings', $user);
?>
<form method="post" class="stack" style="max-width: 860px" autocomplete="off">
  <?= csrf_field() ?>
  <?php foreach (SETTING_SECTIONS as $section => $labels): ?>
    <section class="card stack">
      <div class="card__head"><h2><?= e(tl($labels)) ?></h2></div>
      <?php if ($section === 'notify'): ?><p class="muted small"><?= e(t('settings.notify_hint')) ?></p><?php endif; ?>
      <?php if ($section === 'security'): ?>
        <p class="status-line"><span class="dot <?= turnstile_enabled() ? 'dot--ok' : '' ?>"></span><?= e(turnstile_enabled() ? t('settings.turnstile_on') : t('settings.turnstile_off')) ?></p>
        <p class="muted small"><?= e(t('settings.turnstile_hint')) ?></p>
      <?php endif; ?>
      <div class="form-grid">
        <?php foreach (SETTING_FIELDS as $key => [$s, $az, $ru, $type]): if ($s !== $section) continue; ?>
          <label class="field<?= $section === 'security' ? ' full' : '' ?>">
            <span class="label"><?= e(admin_lang() === 'ru' ? $ru : $az) ?></span>
            <?php if ($type === 'password'): ?>
              <input type="password" name="<?= $key ?>" value="" autocomplete="new-password"
                placeholder="<?= e(setting($key) !== '' ? t('settings.secret_saved') : '') ?>">
            <?php else: ?>
              <input type="<?= $type ?>" name="<?= $key ?>" value="<?= e(setting($key)) ?>">
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if ($section === 'security' && turnstile_enabled()): ?>
        <?= checkbox('turnstile_clear', false, t('settings.turnstile_clear')) ?>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
  <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
</form>
<?php
admin_footer();
