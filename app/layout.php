<?php
/** Admin page chrome (sidebar, top bar, login card) and shared form widgets. */

declare(strict_types=1);
defined('CCS') || exit;

const ICONS = [
    'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9.5 21v-6h5v6"/>',
    'inbox' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
    'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18"/>',
    'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.2a2.5 2.5 0 1 1 3.4 2.3c-.6.3-.9.8-.9 1.4v.6M12 17h.01"/>',
    'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18.5 14a6 6 0 0 1 3 6"/>',
    'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    'sliders' => '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
    'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
    'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9 7 7M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1"/>',
    'external' => '<path d="M7 17 17 7M8 7h9v9"/>',
    'logout' => '<path d="M19 12H6M10 8l-4 4 4 4"/>',
    'plus' => '<path d="M12 5v14M5 12h14"/>',
    'edit' => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3M13.5 6.5l3 3"/>',
    'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
    'check' => '<path d="m5 12 5 5 9-10"/>',
    'download' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
    'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
    'chat' => '<path d="M4 20l1.3-3.9A8 8 0 1 1 8 19.6z"/>',
    'back' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
    'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="m21 16-5-5-9 9"/>',
    'server' => '<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>',
    'refresh' => '<path d="M20 11a8 8 0 0 0-14.6-4.5M4 4v4h4M4 13a8 8 0 0 0 14.6 4.5M20 20v-4h-4"/>',
    'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
    'pages' => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    // service icons
    'finance' => '<path d="M12 3v18M16 7.5c0-1.7-1.8-3-4-3s-4 1.3-4 3 1.8 2.6 4 3 4 1.3 4 3-1.8 3-4 3-4-1.3-4-3"/>',
    'analysis' => '<path d="M4 20V10M10 20V4M16 20v-5"/><circle cx="17" cy="8" r="3"/><path d="m19.2 10.2 2 2"/>',
    'optimize' => '<circle cx="12" cy="12" r="3.5"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9 7 7M17 17l2.1 2.1M4.9 19.1 7 17M17 7l2.1-2.1"/>',
    'recipe' => '<path d="M6 3h9l4 4v14H6zM14 3v5h5M9 13h6M9 17h4"/>',
    'inventory' => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5zM3 7.5 12 12l9-4.5M12 12v9"/>',
    'blog' => '<path d="M4 5h12v14H6a2 2 0 0 1-2-2zM16 9h4v8a2 2 0 0 1-2 2h-2"/><path d="M8 9h4M8 13h4M8 16h2"/>',
    'award' => '<circle cx="12" cy="9" r="6"/><path d="m8.5 13.8-1.5 7.2 5-3 5 3-1.5-7.2"/>',
    'shield' => '<path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
];

function icon(string $name, string $class = 'i'): string
{
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . (ICONS[$name] ?? '') . '</svg>';
}

/** URL of an admin asset with a file-time version, so browsers and Cloudflare pick up changes. */
function asset(string $path): string
{
    $file = BASE_PATH . ADMIN_URL . '/assets/' . $path;
    return ADMIN_URL . '/assets/' . $path . '?v=' . (is_file($file) ? filemtime($file) : '1');
}

function admin_nav(array $user): array
{
    $nav = [
        'nav.main' => [
            ['dashboard', 'index.php', 'home'],
            ['requests', 'requests.php', 'inbox'],
        ],
        'nav.content' => [
            ['texts', 'texts.php', 'sliders'],
            ['pages', 'pages.php', 'pages'],
            ['services', 'services.php', 'briefcase'],
            ['partners', 'partners.php', 'globe'],
            ['projects', 'projects.php', 'award'],
            ['faq', 'faq.php', 'help'],
            ['blog', 'blog.php', 'blog'],
            ['about', 'texts.php?g=about', 'users'],
        ],
        'nav.site' => [
            ['instagram', 'instagram.php', 'instagram'],
        ],
    ];
    if (is_admin($user)) {
        $nav['nav.site'][] = ['settings', 'settings.php', 'settings'];
        $nav['nav.site'][] = ['users', 'users.php', 'users'];
    }
    return $nav;
}

function lang_switch(string $class = ''): string
{
    $html = '<div class="lang ' . e($class) . '">';
    foreach (ADMIN_LANGS as $lang) {
        $query = array_merge($_GET, ['lang' => $lang]);
        $html .= '<a href="?' . e(http_build_query($query)) . '" class="' . ($lang === admin_lang() ? 'is-active' : '') . '">'
            . strtoupper($lang) . '</a>';
    }
    return $html . '</div>';
}

function html_head(string $title, string $bodyClass): void
{
    ?><!doctype html>
<html lang="<?= e(admin_lang()) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> — CCS Admin</title>
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('admin.css')) ?>">
</head>
<body class="<?= e($bodyClass) ?>">
<?php
}

function admin_header(string $title, string $active, array $user, string $actions = ''): void
{
    $newRequests = (int)db()->query("SELECT COUNT(*) FROM requests WHERE status = 'new'")->fetchColumn();
    html_head($title, 'admin');
    ?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar__brand">
    <a class="brand" href="<?= ADMIN_URL ?>/">
      <img src="/assets/img/logo-mark-white.svg" alt="" width="34" height="30">
      <span class="brand__word" lang="en">Cost<br>Control<br>Services</span>
    </a>
    <span class="sidebar__tag"><?= e(t('admin_tag')) ?></span>
  </div>
  <nav class="sidebar__nav" aria-label="<?= e(t('nav.label')) ?>">
    <?php foreach (admin_nav($user) as $section => $items): ?>
      <p class="sidebar__section"><?= e(t($section)) ?></p>
      <?php foreach ($items as [$key, $href, $ic]): ?>
        <a class="sidebar__link<?= $key === $active ? ' is-active' : '' ?>" href="<?= ADMIN_URL . '/' . e($href) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>>
          <?= icon($ic) ?><span><?= e(t('nav.' . $key)) ?></span>
          <?php if ($key === 'requests' && $newRequests): ?><b class="sidebar__count"><?= $newRequests ?></b><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar__user">
    <a class="sidebar__me" href="<?= ADMIN_URL ?>/profile.php">
      <strong><?= e($user['name']) ?></strong>
      <span><?= e($user['email']) ?></span>
    </a>
    <span class="role-badge"><?= e(t('role.' . $user['role'])) ?></span>
    <div class="sidebar__bottom">
      <?= lang_switch('lang--dark') ?>
      <form method="post" action="<?= ADMIN_URL ?>/logout.php">
        <?= csrf_field() ?>
        <button class="sidebar__logout" type="submit"><?= icon('logout') ?><?= e(t('logout')) ?></button>
      </form>
    </div>
  </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close></div>
<div class="main">
  <header class="topbar">
    <button class="topbar__menu" type="button" data-sidebar-toggle aria-controls="sidebar" aria-label="<?= e(t('nav.label')) ?>"><?= icon('menu') ?></button>
    <h1 class="topbar__title"><?= e($title) ?></h1>
    <div class="topbar__actions">
      <?= $actions ?>
      <a class="btn btn--ghost" href="/" target="_blank" rel="noopener"><?= icon('external') ?><span><?= e(t('view_site')) ?></span></a>
    </div>
  </header>
  <main class="content">
    <?php foreach (take_flashes() as $f): ?>
      <div class="flash flash--<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'error' ? 'x' : 'check') ?><span><?= e($f['message']) ?></span></div>
    <?php endforeach; ?>
<?php
}

function admin_footer(): void
{
    ?>
  </main>
</div>
<script src="<?= e(asset('admin.js')) ?>" defer></script>
</body>
</html>
<?php
}

/** Centered card used by the login and first-run setup pages. */
function auth_header(string $title): void
{
    html_head($title, 'auth');
    ?>
<main class="auth">
  <div class="auth__card">
    <div class="auth__top">
      <a class="brand brand--dark" href="/">
        <img src="/assets/img/logo-mark.svg" alt="" width="40" height="36">
        <span class="brand__word" lang="en">Cost<br>Control<br>Services</span>
      </a>
      <?= lang_switch() ?>
    </div>
<?php
}

function auth_footer(bool $turnstile = false): void
{
    ?>
    <a class="auth__back" href="/"><?= icon('back') ?><?= e(t('view_site')) ?></a>
  </div>
</main>
<?php if ($turnstile): ?><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script><?php endif; ?>
<script src="<?= e(asset('admin.js')) ?>" defer></script>
</body>
</html>
<?php
}

// ---------- Form widgets ----------

/**
 * One field per content language (AZ/RU/EN) with tabs; posts as name[az], name[ru], name[en].
 * $values: ['az' => …, 'ru' => …, 'en' => …]; $limit > 0 shows a "42 / 60" character counter.
 */
function ml_field(string $name, string $label, array $values, string $type = 'line', bool $required = false, int $limit = 0): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9]+/i', '-', $name);
    $html = '<div class="ml" data-ml' . ($limit ? ' data-limit="' . $limit . '"' : '') . '><div class="ml__head"><span class="label">' . e($label)
        . ($required ? ' <span class="req">*</span>' : '') . '</span>'
        . ($limit ? '<span class="ml__count" data-ml-count aria-live="polite"></span>' : '') . '<div class="ml__tabs" role="tablist">';
    foreach (CONTENT_LANGS as $code => $short) {
        $filled = trim((string)($values[$code] ?? '')) !== '';
        $html .= '<button type="button" role="tab" data-ml-tab="' . $code . '" class="' . ($code === 'az' ? 'is-active ' : '')
            . ($filled ? 'is-filled' : '') . '" aria-selected="' . ($code === 'az' ? 'true' : 'false') . '">' . $short . '</button>';
    }
    $html .= '</div></div>';
    foreach (CONTENT_LANGS as $code => $short) {
        $value = (string)($values[$code] ?? '');
        $attrs = 'id="' . $id . '-' . $code . '" name="' . e($name) . '[' . $code . ']" lang="' . $code . '" aria-label="' . e($label . ' (' . $short . ')') . '"'
            . ($required && $code === 'az' ? ' required' : '');
        $control = $type === 'area'
            ? '<textarea ' . $attrs . ' rows="4">' . e($value) . '</textarea>'
            : '<input type="text" ' . $attrs . ' value="' . e($value) . '">';
        $html .= '<div class="ml__pane" data-ml-pane="' . $code . '"' . ($code === 'az' ? '' : ' hidden') . '>' . $control . '</div>';
    }
    return $html . '</div>';
}

/** Cleans one multi-language value posted as [az => …, ru => …, en => …]. */
function ml_clean($raw, int $max = 5000): array
{
    $out = [];
    foreach (array_keys(CONTENT_LANGS) as $code) {
        $value = is_array($raw) ? ($raw[$code] ?? '') : '';
        $out[$code] = is_string($value) ? mb_substr(trim($value), 0, $max) : '';
    }
    return $out;
}

/** Reads name[az|ru|en] from POST. */
function post_ml(string $name, int $max = 5000): array
{
    return ml_clean($_POST[$name] ?? [], $max);
}

/** Row columns col_az/col_ru/col_en as ['az' => …, 'ru' => …, 'en' => …]. */
function row_ml(array $row, string $col): array
{
    $out = [];
    foreach (array_keys(CONTENT_LANGS) as $code) {
        $out[$code] = (string)($row[$col . '_' . $code] ?? '');
    }
    return $out;
}

function status_badge(string $status): string
{
    return '<span class="badge badge--' . e($status) . '">' . e(t('status.' . $status)) . '</span>';
}

function format_dt(?string $value, bool $time = true): string
{
    if (!$value) {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date($time ? 'd.m.Y H:i' : 'd.m.Y', $ts) : '—';
}

function checkbox(string $name, bool $checked, string $label): string
{
    return '<label class="switch"><input type="checkbox" name="' . e($name) . '" value="1"' . ($checked ? ' checked' : '') . '><span class="switch__ui" aria-hidden="true"></span><span>' . e($label) . '</span></label>';
}

/** Small POST form with a single button (delete, toggle…), protected by CSRF and an optional confirm text. */
function action_button(string $url, array $fields, string $label, string $iconName, string $class = 'btn btn--ghost btn--sm', string $confirm = ''): string
{
    $html = '<form method="post" action="' . e($url) . '" class="inline"' . ($confirm !== '' ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . csrf_field();
    foreach ($fields as $name => $value) {
        $html .= '<input type="hidden" name="' . e($name) . '" value="' . e((string)$value) . '">';
    }
    return $html . '<button type="submit" class="' . e($class) . '" title="' . e($label) . '">' . icon($iconName) . '<span>' . e($label) . '</span></button></form>';
}
