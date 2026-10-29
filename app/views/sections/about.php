<?php
/** "Who we are": big title card and the text panel. */
defined('CCS') || exit;
?>
<!-- SECTION: about -->
<section class="about section" id="haqqimizda">
  <div class="container about__grid">
    <div class="about__title" data-reveal>
      <p class="eyebrow"><?= e(txt('about.eyebrow')) ?></p>
      <h2 class="about__heading"><?php foreach (preg_split('/\s+/u', plain(txt('about.title'))) as $word): ?><span><?= e($word) ?></span> <?php endforeach; ?></h2>
      <p class="about__since"><?= e(txt('about.since')) ?></p>
    </div>
    <div class="about__panel" data-reveal style="--delay:.1s">
      <img class="about__mark" src="/assets/img/logo-mark.svg" alt="" width="54" height="48" loading="lazy">
      <p class="about__lead"><?= rich(txt('about.p1')) ?></p>
      <?php foreach (['about.p2', 'about.p3'] as $key): if (txt($key) === '') continue; ?>
        <p><?= rich(txt($key)) ?></p>
      <?php endforeach; ?>
    </div>
  </div>
</section>
