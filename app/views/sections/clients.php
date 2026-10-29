<?php
/** Partner logos from Admin → Partners in two scrolling rows. */
defined('CCS') || exit;

$partners = site_partners();
$rows = $partners ? array_chunk($partners, (int)ceil(count($partners) / 2)) : [];
?>
<!-- SECTION: clients -->
<section class="clients section" id="musteriler">
  <div class="container">
    <header class="section-head section-head--center" data-reveal>
      <p class="eyebrow"><?= e(txt('clients.eyebrow')) ?></p>
      <h2 class="section-title"><?= rich(txt('clients.title')) ?></h2>
      <p class="section-lead"><?= rich(txt('clients.lead')) ?></p>
    </header>
  </div>

  <?php if ($rows): ?>
    <div class="marquee" data-reveal>
      <?php foreach ($rows as $r => $row): ?>
        <div class="marquee__row<?= $r % 2 ? ' marquee__row--reverse' : '' ?>">
          <ul class="marquee__track">
            <?php foreach ($row as $partner):
                [$w, $h] = img_size((string)$partner['logo']);
                // Logos that are not square are shown whole instead of cropped
                $contain = $w && abs($w - $h) / $w > .05;
                $img = '<img src="' . e($partner['logo']) . '" alt="' . e($partner['name']) . '" title="' . e($partner['name']) . '" width="150" height="150" loading="lazy">';
                ?>
              <li class="logo-tile<?= $contain ? ' logo-tile--contain' : '' ?>"><?= $partner['url'] ? '<a href="' . e($partner['url']) . '" target="_blank" rel="noopener">' . $img . '</a>' : $img ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
