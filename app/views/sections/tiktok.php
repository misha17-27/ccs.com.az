<?php
/** TikTok block: texts from Admin → Texts, a preview of the videos; everything links to the profile from Settings. */
defined('CCS') || exit;

// Covers of a few videos from the profile, as a preview
$covers = ['7694345186855456020', '7693584921171856646', '7691019885664652565', '7658729561785601300', '7657863589746855189'];
?>
<!-- SECTION: tiktok -->
<section class="tt section" id="tiktok" aria-labelledby="tt-title">
  <div class="container">
    <div class="tt__head">
      <header class="section-head" data-reveal>
        <p class="eyebrow"><?= e(txt('tiktok.eyebrow')) ?></p>
        <h2 class="section-title" id="tt-title"><?= rich(txt('tiktok.title')) ?></h2>
        <?php if (txt('tiktok.lead') !== ''): ?><p class="section-lead"><?= rich(txt('tiktok.lead')) ?></p><?php endif; ?>
      </header>
      <a class="btn btn--primary" href="<?= e(tiktok_url()) ?>" target="_blank" rel="noopener" data-reveal style="--delay:.1s"><svg class="icon icon--fill"><use href="#i-tiktok"/></svg><?= e(ui('tt_follow')) ?></a>
    </div>

    <a class="tt__grid" href="<?= e(tiktok_url()) ?>" target="_blank" rel="noopener" aria-label="<?= e(tiktok_handle() . ' — ' . ui('tt_follow')) ?>" data-reveal>
      <?php foreach ($covers as $id): $src = '/assets/img/tiktok/' . $id . '.jpg'; ?>
        <span class="tt__cover">
          <img src="<?= e($src) ?>" alt="" loading="lazy"<?= img_size_attrs($src) ?>>
          <span class="tt__badge" aria-hidden="true"><svg class="icon icon--fill"><use href="#i-tiktok"/></svg></span>
        </span>
      <?php endforeach; ?>
    </a>
  </div>
</section>
