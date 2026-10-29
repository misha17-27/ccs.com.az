<?php
/** Top band of an inner page: breadcrumb, page name (h1) and its SEO description as the lead. @var string $name @var string $lead */
defined('CCS') || exit;
?>
<!-- SECTION: page-head -->
<section class="page-head" id="top">
  <div class="page-head__glow" aria-hidden="true"></div>
  <div class="container">
    <nav class="crumbs" aria-label="<?= e(ui('breadcrumb')) ?>" data-reveal>
      <a href="<?= e(url('home')) ?>"><?= e(pick(site_pages()['home'], 'name')) ?></a>
      <span aria-hidden="true">/</span>
      <span aria-current="page"><?= e($name) ?></span>
    </nav>
    <h1 class="page-head__title" data-reveal style="--delay:.06s"><?= e($name) ?></h1>
    <?php if ($lead !== ''): ?><p class="page-head__lead" data-reveal style="--delay:.12s"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
