<?php
/** Home page hero with the facts row. */
defined('CCS') || exit;

$cardItems = array_filter(array_map('trim', explode("\n", txt('hero.card_items'))), 'strlen');
?>
<!-- SECTION: hero -->
<section class="hero" id="top">
  <div class="hero__glow" aria-hidden="true"></div>

    <svg class="hero__art" viewBox="0 0 700 1000" preserveAspectRatio="xMaxYMid slice" aria-hidden="true" focusable="false">
      <defs>
        <linearGradient id="hero-stroke" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="700" y2="0">
          <stop offset="0" stop-color="#fff" stop-opacity="0"/>
          <stop offset=".3" stop-color="#fff" stop-opacity=".16"/>
          <stop offset="1" stop-color="#fff" stop-opacity=".5"/>
        </linearGradient>
      </defs>
      <g class="hero__lines" fill="none" stroke="url(#hero-stroke)" stroke-width="1.2">
        <path pathLength="1" style="--d:.05s" d="M150 300V1000"/>
        <path pathLength="1" style="--d:.15s" d="M270 180V1000"/>
        <path pathLength="1" style="--d:.25s" d="M390 60V540"/>
        <path pathLength="1" style="--d:.35s" d="M390 780V1000"/>
        <path pathLength="1" style="--d:.1s" d="M510 0V1000"/>
        <path pathLength="1" style="--d:.2s" d="M630 0V1000"/>
        <path pathLength="1" style="--d:.6s" d="M40 420H150"/>
        <path pathLength="1" style="--d:.7s" d="M10 540H150"/>
        <path pathLength="1" style="--d:.5s" d="M0 780H510"/>
        <path pathLength="1" style="--d:.55s" d="M510 180H700"/>
        <path pathLength="1" style="--d:.65s" d="M510 420H700"/>
        <path pathLength="1" style="--d:.45s" d="M390 60H700"/>
        <path pathLength="1" style="--d:.75s" d="M510 900H630"/>
        <path pathLength="1" style="--d:.8s" d="M150 780L390 540L510 660"/>
        <path pathLength="1" style="--d:.9s" d="M270 420L510 180L630 300L700 230"/>
        <path pathLength="1" style="--d:1s" d="M270 180L390 300"/>
        <path pathLength="1" style="--d:1.05s" d="M150 540L0 690"/>
        <path pathLength="1" style="--d:1.1s" d="M150 900L270 780L390 900L510 780L630 900L700 830"/>
        <path pathLength="1" style="--d:1.15s" d="M510 420L630 540L700 470"/>
        <path pathLength="1" style="--d:1.2s" d="M510 660L630 780L700 710"/>
        <path pathLength="1" style="--d:1.25s" d="M630 540L510 660"/>
      </g>
      <g class="hero__nodes">
        <circle cx="390" cy="540" r="3.5"/>
        <circle cx="510" cy="180" r="3.5"/>
        <circle cx="270" cy="780" r="3.5"/>
        <circle cx="630" cy="540" r="3.5"/>
      </g>
    </svg>

  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow eyebrow--pill" data-reveal><span class="dot" aria-hidden="true"></span><?= rich(txt('hero.eyebrow')) ?></p>
      <h1 class="hero__title" data-reveal style="--delay:.08s"><?= rich(txt('hero.title')) ?></h1>
      <p class="hero__lead" data-reveal style="--delay:.16s"><?= rich(txt('hero.lead')) ?></p>
      <div class="hero__actions" data-reveal style="--delay:.24s">
        <a class="btn btn--primary" href="#elaqe"><?= e(txt('hero.cta')) ?> <svg class="icon"><use href="#i-arrow"/></svg></a>
        <a class="btn btn--ghost" href="<?= e(whatsapp_url(true)) ?>" target="_blank" rel="noopener"><svg class="icon icon--fill"><use href="#i-whatsapp"/></svg>WhatsApp</a>
      </div>
    </div>

    <?php if ($cardItems): ?>
      <aside class="hero__card" data-reveal style="--delay:.4s" aria-label="<?= e(ui('hero_card_label')) ?>">
        <p class="hero__card-title"><?= e(txt('hero.card_title')) ?></p>
        <ul class="hero__card-list">
          <?php foreach ($cardItems as $item): ?>
            <li><svg class="icon"><use href="#i-check"/></svg><?= rich($item) ?></li>
          <?php endforeach; ?>
        </ul>
      </aside>
    <?php endif; ?>
  </div>

  <!-- SECTION: facts -->
  <div class="container">
    <dl class="facts" data-reveal>
      <?php for ($i = 1; $i <= 4; $i++):
          $num = txt("facts.{$i}_num");
          if ($num === '') {
              continue;
          }
          // Counts up from zero on scroll; years such as 2021 stay as they are.
          $count = preg_match('/^(\d+)(.*)$/u', $num, $m) && (int)$m[1] < 1000;
          ?>
        <div class="facts__item">
          <dt class="facts__num"><?php if ($count): ?><span data-count="<?= (int)$m[1] ?>"><?= (int)$m[1] ?></span><?= e($m[2]) ?><?php else: ?><?= e($num) ?><?php endif; ?></dt>
          <dd class="facts__label"><?= rich(txt("facts.{$i}_label")) ?></dd>
        </div>
      <?php endfor; ?>
    </dl>
  </div>
</section>
