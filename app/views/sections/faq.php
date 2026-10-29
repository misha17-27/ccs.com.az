<?php
/** Questions from Admin → FAQ as an accordion (native <details>). */
defined('CCS') || exit;

$faqs = site_faqs();
?>
<!-- SECTION: faq -->
<section class="faq section" id="suallar">
  <div class="container faq__grid">
    <header class="faq__aside" data-reveal>
      <p class="eyebrow"><?= e(txt('faq.eyebrow')) ?></p>
      <h2 class="section-title"><?= rich(txt('faq.title')) ?></h2>
      <p class="section-lead"><?= rich(txt('faq.lead')) ?></p>
      <a class="link-arrow" href="<?= e(whatsapp_url(true)) ?>" target="_blank" rel="noopener"><?= e(ui('faq_ask')) ?> <svg class="icon"><use href="#i-arrow"/></svg></a>
    </header>

    <div class="faq__list" data-reveal style="--delay:.1s">
      <?php foreach ($faqs as $i => $faq): ?>
        <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
          <summary><span><?= e(pick($faq, 'question')) ?></span><svg class="icon"><use href="#i-chevron"/></svg></summary>
          <div class="faq__answer"><p><?= rich(pick($faq, 'answer')) ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
