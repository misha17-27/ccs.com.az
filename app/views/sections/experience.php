<?php
/** International experience with the championship photos. */
defined('CCS') || exit;
?>
<!-- SECTION: experience -->
<section class="experience section" id="tecrube">
  <div class="container experience__grid">
    <div class="experience__text" data-reveal>
      <p class="eyebrow"><?= e(txt('experience.eyebrow')) ?></p>
      <h2 class="section-title"><?= rich(txt('experience.title')) ?></h2>
      <?php foreach ([1, 2] as $i): if (txt("experience.p{$i}") === '') continue; ?>
        <div class="exp-item">
          <span class="exp-item__tag"><?= e(txt("experience.tag{$i}")) ?></span>
          <p><?= rich(txt("experience.p{$i}")) ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="experience__gallery" data-reveal style="--delay:.1s">
      <figure class="gallery__item gallery__item--big">
        <img src="/assets/img/experience/wtc-baku-2023.jpg" alt="<?= e(ui('exp_gallery_wide')) ?>" loading="lazy" width="1000" height="750">
        <figcaption><?= e(ui('exp_gallery_caption')) ?></figcaption>
      </figure>
      <figure class="gallery__item">
        <img src="/assets/img/experience/wtc-team-arena.jpg" alt="<?= e(ui('exp_gallery_arena')) ?>" loading="lazy" width="960" height="1280" style="object-position:75% 50%">
      </figure>
      <figure class="gallery__item">
        <img src="/assets/img/experience/wtc-team-stage.jpg" alt="<?= e(ui('exp_gallery_stage')) ?>" loading="lazy" width="960" height="1280">
      </figure>
      <figure class="gallery__item gallery__item--big">
        <img src="/assets/img/experience/wtc-team-banner.jpg" alt="<?= e(ui('exp_gallery_banner')) ?>" loading="lazy" width="1280" height="960">
      </figure>
    </div>
  </div>
</section>
