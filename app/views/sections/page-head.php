<?php
/**
 * Top band of an inner page: breadcrumb with the menu name, the page title (h1) and the text under it.
 * @var string $name @var string $title @var string $lead
 * @var array $parents [[name, url], …] between the home page and this one (a blog post: the blog)
 * @var string $date $datetime date of a blog post: 30.10.2026 and 2026-10-30
 */
defined('CCS') || exit;
?>
<!-- SECTION: page-head -->
<section class="page-head" id="top">
  <div class="page-head__glow" aria-hidden="true"></div>
  <div class="container">
    <nav class="crumbs" aria-label="<?= e(ui('breadcrumb')) ?>" data-reveal>
      <a href="<?= e(url('home')) ?>"><?= e(pick(site_pages()['home'], 'name')) ?></a>
      <span aria-hidden="true">/</span>
      <?php foreach ($parents ?? [] as [$parentName, $parentUrl]): ?>
        <a href="<?= e($parentUrl) ?>"><?= e($parentName) ?></a>
        <span aria-hidden="true">/</span>
      <?php endforeach; ?>
      <span aria-current="page"><?= e($name) ?></span>
    </nav>
    <?php if (($date ?? '') !== ''): ?><p class="page-head__date" data-reveal><time datetime="<?= e($datetime) ?>"><?= e($date) ?></time></p><?php endif; ?>
    <h1 class="page-head__title<?= isset($date) ? ' page-head__title--post' : '' ?>" data-reveal style="--delay:.06s"><?= rich($title) ?></h1>
    <?php if ($lead !== ''): ?><p class="page-head__lead" data-reveal style="--delay:.12s"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
