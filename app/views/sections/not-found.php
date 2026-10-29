<?php
/** 404 page body. */
defined('CCS') || exit;
?>
<!-- SECTION: not-found -->
<section class="page-head page-head--404" id="top">
  <div class="page-head__glow" aria-hidden="true"></div>
  <div class="container">
    <p class="page-head__code" aria-hidden="true">404</p>
    <h1 class="page-head__title"><?= e(ui('not_found_title')) ?></h1>
    <p class="page-head__lead"><?= e(ui('not_found_text')) ?></p>
    <a class="btn btn--primary" href="<?= e(url('home')) ?>"><?= e(ui('not_found_home')) ?> <svg class="icon"><use href="#i-arrow"/></svg></a>
  </div>
</section>
