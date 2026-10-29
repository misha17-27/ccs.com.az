<?php
/** Latest Instagram posts (filled by assets/js/instagram-feed.js from /api/instagram.php). */
defined('CCS') || exit;
?>
<!-- SECTION: instagram-feed -->
<section class="ig section" id="instagram" data-ig-section aria-labelledby="ig-title">
  <div class="container">
    <header class="ig-head" data-reveal>
      <a class="ig-head__icon" href="<?= e(instagram_url()) ?>" target="_blank" rel="noopener" aria-label="<?= e(ui('ig_profile')) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/></g><circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"/></svg></a>
      <h2 class="ig-head__handle" id="ig-title"><a href="<?= e(instagram_url()) ?>" target="_blank" rel="noopener"><?= e(instagram_handle()) ?></a></h2>
      <p class="ig-head__text"><?= rich(txt('instagram.text')) ?></p>
    </header>
    <div class="ig-feed" data-ig-feed data-ig-limit="12"></div>
    <div class="ig-cta"><a class="btn btn--primary" href="<?= e(instagram_url()) ?>" target="_blank" rel="noopener"><svg class="icon"><use href="#i-instagram"/></svg><?= e(ui('ig_follow')) ?></a></div>
  </div>
</section>
