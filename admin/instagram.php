<?php
/** Instagram feed status and token management (files shared with /api/instagram.php). */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$self = ADMIN_URL . '/instagram.php';

$tokenFile = PRIVATE_DIR . '/instagram-token.txt';
$stateFile = PRIVATE_DIR . '/instagram-state.json';
$cacheFile = PRIVATE_DIR . '/instagram-cache.json';
$readJson = static fn(string $f): ?array => is_file($f) && is_array($d = json_decode((string)file_get_contents($f), true)) ? $d : null;

if (is_post()) {
    csrf_check();
    require_admin($user);
    $action = post_str('action', 20);

    if ($action === 'token') {
        $token = preg_replace('/\s+/', '', post_str('token', 2000));
        if (!preg_match('/^[A-Za-z0-9_\-.|]{40,1500}$/', $token)) {
            flash('error', t('instagram.token_invalid'));
            redirect($self);
        }
        file_put_contents($tokenFile, $token . "\n");
        @chmod($tokenFile, 0600);
        @unlink($stateFile);   // the API re-reads the token and starts refreshing it from now
        @unlink($cacheFile);
        flash('success', t('instagram.token_saved'));
    } elseif ($action === 'refresh') {
        @unlink($cacheFile);
        flash('success', t('instagram.cache_cleared'));
    } elseif ($action === 'remove') {
        @unlink($tokenFile);
        @unlink($stateFile);
        @unlink($cacheFile);
        flash('success', t('instagram.token_removed'));
    }
    redirect($self);
}

$state = $readJson($stateFile);
$cache = $readJson($cacheFile);
$configured = $state !== null || is_file($tokenFile);
$items = $cache['payload']['items'] ?? [];
$source = 'live';
if (!$items) {
    $snapshot = $readJson(BASE_PATH . '/assets/data/instagram.json');
    $items = $snapshot['items'] ?? [];
    $source = 'snapshot';
}

admin_header(t('nav.instagram'), 'instagram', $user,
    '<a class="btn btn--ghost" href="https://www.instagram.com/costcontrol.services/" target="_blank" rel="noopener">' . icon('instagram') . '<span>@costcontrol.services</span></a>');
?>
<div class="split">
  <section class="card stack">
    <div class="card__head"><h2><?= e(t('instagram.feed')) ?></h2>
      <span class="badge <?= $source === 'live' ? 'badge--done' : 'badge--off' ?>"><?= e(t('instagram.source_' . $source)) ?></span></div>
    <?php if ($items): ?>
      <div class="ig-grid">
        <?php foreach (array_slice($items, 0, 12) as $item): ?>
          <a href="<?= e($item['permalink'] ?? '#') ?>" target="_blank" rel="noopener"><img src="<?= e($item['image'] ?? '') ?>" alt="" loading="lazy"></a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="empty"><?= e(t('instagram.no_items')) ?></p>
    <?php endif; ?>
    <p class="muted small"><?= e(t('instagram.how_it_works')) ?></p>
  </section>

  <aside class="split__side">
    <section class="card stack">
      <div class="card__head"><h2><?= e(t('instagram.status')) ?></h2></div>
      <p class="status-line"><span class="dot <?= $configured ? 'dot--ok' : 'dot--warn' ?>"></span><?= e($configured ? t('instagram.connected') : t('instagram.not_connected')) ?></p>
      <dl class="kv">
        <dt><?= e(t('instagram.token_refreshed')) ?></dt><dd><?= e(isset($state['refreshed_at']) ? date('d.m.Y H:i', (int)$state['refreshed_at']) : '—') ?></dd>
        <dt><?= e(t('instagram.last_fetch')) ?></dt><dd><?= e(isset($cache['fetched_at']) ? date('d.m.Y H:i', (int)$cache['fetched_at']) : '—') ?></dd>
        <dt><?= e(t('instagram.posts')) ?></dt><dd><?= count($items) ?></dd>
      </dl>
      <?php if ($configured && is_admin($user)): ?>
        <div class="form-actions">
          <?= action_button($self, ['action' => 'refresh'], t('instagram.refresh'), 'refresh') ?>
          <?= action_button($self, ['action' => 'remove'], t('instagram.remove'), 'trash', 'btn btn--danger btn--sm', t('confirm.delete')) ?>
        </div>
      <?php endif; ?>
    </section>

    <?php if (is_admin($user)): ?>
      <form class="card stack" method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="token">
        <div class="card__head"><h2><?= e($configured ? t('instagram.replace_token') : t('instagram.connect')) ?></h2></div>
        <label class="field">
          <span class="label"><?= e(t('instagram.token')) ?></span>
          <textarea name="token" rows="4" class="mono" required spellcheck="false" placeholder="IGAA…"></textarea>
        </label>
        <p class="muted small"><?= e(t('instagram.token_hint')) ?></p>
        <button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button>
      </form>
    <?php endif; ?>
  </aside>
</div>
<?php
admin_footer();
