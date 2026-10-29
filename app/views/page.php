<?php
/**
 * One page of the site (design V1): head with SEO, header, the page's sections, footer.
 * @var string $pageKey page key from PAGES, or '404'
 */

declare(strict_types=1);
defined('CCS') || exit;

$lang = site_lang();
$pages = site_pages();
$page = $pages[$pageKey] ?? null;
$isHome = $pageKey === 'home';
$name = $page ? pick($page, 'name') : ui('not_found_title');
$title = $page ? (pick($page, 'seo_title') ?: $name . ' — ' . SITE_NAME) : ui('not_found_title') . ' — ' . SITE_NAME;
$description = $page ? pick($page, 'seo_description') : '';
$ogImage = $page && $page['og_image'] ? $page['og_image'] : ($pages['home']['og_image'] ?? '');
$origin = site_origin();
$sections = $page ? PAGES[$pageKey]['sections'] : [];
$navKeys = array_values(array_diff(array_keys($pages), ['home']));
$langUrl = static fn(string $l): string => url($page ? $pageKey : 'home', $l);
?><!doctype html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
<?php if ($description !== ''): ?>
  <meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($page): ?>
  <link rel="canonical" href="<?= e($origin . url($pageKey)) ?>">
<?php foreach (array_keys(CONTENT_LANGS) as $l): ?>
  <link rel="alternate" hreflang="<?= $l ?>" href="<?= e($origin . url($pageKey, $l)) ?>">
<?php endforeach; ?>
  <link rel="alternate" hreflang="x-default" href="<?= e($origin . url($pageKey, 'az')) ?>">
<?php else: ?>
  <meta name="robots" content="noindex">
<?php endif; ?>
  <meta name="theme-color" content="#0D0A1F">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= SITE_NAME ?>">
  <meta property="og:locale" content="<?= e(ui('locale')) ?>">
  <meta property="og:title" content="<?= e($title) ?>">
<?php if ($description !== ''): ?>
  <meta property="og:description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($page): ?>
  <meta property="og:url" content="<?= e($origin . url($pageKey)) ?>">
<?php endif; ?>
<?php if ($ogImage): ?>
  <meta property="og:image" content="<?= e($origin . $ogImage) ?>">
  <meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
  <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(site_asset('/assets/css/instagram-feed.css')) ?>">
  <link rel="stylesheet" href="<?= e(site_asset('/assets/css/site.css')) ?>">
  <script>document.documentElement.classList.add('js');</script>
</head>
<body class="page-<?= e($pageKey) ?>">

<?php require __DIR__ . '/sprite.php'; ?>

<a class="skip-link" href="#main"><?= e(ui('skip')) ?></a>

<!-- SECTION: header -->
<header class="header" data-header>
  <div class="container header__inner">
    <a class="brand" href="<?= e(url('home')) ?>" aria-label="<?= e(ui('brand_home')) ?>">
      <img class="brand__mark" src="/assets/img/logo-mark-white.svg" alt="" width="40" height="36">
      <span class="brand__text" lang="en">Cost<br>Control<br>Services</span>
    </a>

    <nav class="nav" id="site-nav" aria-label="<?= e(ui('nav_label')) ?>">
      <ul class="nav__list">
        <?php foreach ($navKeys as $key): ?>
          <li><a class="nav__link<?= $key === $pageKey ? ' is-active' : '' ?>" href="<?= e(url($key)) ?>"<?= $key === $pageKey ? ' aria-current="page"' : '' ?>><?= e(pick($pages[$key], 'name')) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="nav__extra">
        <?php $langClass = ''; require __DIR__ . '/lang-switch.php'; ?>
        <a class="nav__contact" href="<?= e(phone_href()) ?>"><svg class="icon"><use href="#i-phone"/></svg><?= e(phone_display()) ?></a>
        <a class="btn btn--primary btn--block" href="#elaqe"><?= e(txt('hero.cta')) ?></a>
      </div>
    </nav>

    <div class="header__actions">
      <?php $langClass = 'lang--header'; require __DIR__ . '/lang-switch.php'; ?>
      <a class="btn btn--primary btn--sm header__cta" href="#elaqe"><?= e(ui('cta_short')) ?></a>
      <button class="burger" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="<?= e(ui('menu_open')) ?>"
        data-burger data-label-open="<?= e(ui('menu_open')) ?>" data-label-close="<?= e(ui('menu_close')) ?>">
        <span></span><span></span>
      </button>
    </div>
  </div>
</header>

<main id="main">
<?php if ($isHome): ?>
<?php elseif ($page): ?>
  <?php section('page-head', ['name' => $name, 'lead' => $description]); ?>
<?php else: ?>
  <?php section('not-found'); ?>
<?php endif; ?>
<?php foreach ($sections as $sectionName): ?>
  <?php section($sectionName, ['pageKey' => $pageKey]); ?>
<?php endforeach; ?>
</main>

<!-- SECTION: footer -->
<footer class="footer">
  <div class="container footer__grid">
    <div class="footer__brand">
      <a class="brand" href="#top" aria-label="<?= e(ui('footer_back_top')) ?>">
        <img class="brand__mark" src="/assets/img/logo-mark-white.svg" alt="" width="40" height="36" loading="lazy">
        <span class="brand__text" lang="en">Cost<br>Control<br>Services</span>
      </a>
      <p><?= rich(txt('footer.text')) ?></p>
    </div>
    <nav class="footer__col" aria-label="<?= e(ui('footer_nav_label')) ?>">
      <h3><?= e(ui('footer_nav')) ?></h3>
      <ul>
        <?php foreach ($navKeys as $key): ?>
          <li><a href="<?= e(url($key)) ?>"><?= e(pick($pages[$key], 'name')) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="footer__col">
      <h3><?= e(ui('footer_services')) ?></h3>
      <ul>
        <?php foreach (site_services() as $i => $service): ?>
          <li><a href="<?= e(url('services', null, 'svc-tab-' . ($i + 1))) ?>"><?= e(pick($service, 'title')) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="footer__col">
      <h3><?= e(ui('footer_contact')) ?></h3>
      <ul>
        <li><?= e(txt('contact.address')) ?></li>
        <li><a href="<?= e(phone_href()) ?>"><?= e(phone_display()) ?></a></li>
        <?php if (setting('contact_email') !== ''): ?>
          <li><a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a></li>
        <?php endif; ?>
        <li><a href="<?= e(instagram_url()) ?>" target="_blank" rel="noopener">Instagram</a></li>
      </ul>
    </div>
  </div>
  <div class="container footer__bottom">
    <p>© <?= date('Y') ?> <?= SITE_NAME ?>. <?= e(ui('footer_rights')) ?></p>
    <a href="#top" class="footer__top"><?= e(ui('footer_top')) ?> <svg class="icon"><use href="#i-arrow-ur"/></svg></a>
  </div>
</footer>

<a class="wa-float" href="<?= e(whatsapp_url(true)) ?>" target="_blank" rel="noopener" aria-label="<?= e(ui('wa_label')) ?>">
  <svg class="icon icon--fill"><use href="#i-whatsapp"/></svg>
  <span class="wa-float__label"><?= e(ui('wa_label')) ?></span>
</a>

<script src="<?= e(site_asset('/assets/js/site.js')) ?>" defer></script>
<?php if (in_array('instagram', $sections, true)): ?>
<script src="<?= e(site_asset('/assets/js/instagram-feed.js')) ?>" defer></script>
<?php endif; ?>
</body>
</html>
