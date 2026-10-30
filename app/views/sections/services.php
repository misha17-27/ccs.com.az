<?php
/** Services from Admin → Services: tabs on desktop, accordion on phones (site.js). */
defined('CCS') || exit;

$services = site_services();
?>
<!-- SECTION: services -->
<section class="services section" id="xidmetler">
  <div class="container">
    <header class="section-head" data-reveal>
      <p class="eyebrow"><?= e(txt('services.eyebrow')) ?></p>
      <h2 class="section-title"><?= rich(txt('services.title')) ?></h2>
      <p class="section-lead"><?= rich(txt('services.lead')) ?></p>
    </header>

    <div class="svc" data-svc data-reveal style="--svc-rows:<?= count($services) ?>">
      <?php foreach ($services as $i => $service):
          $n = $i + 1;
          $title = pick($service, 'title');
          [$media, $imgStyle] = SERVICE_MEDIA[$service['image']] ?? ['dark', ''];
          ?>
        <button class="svc__tab" type="button" id="svc-tab-<?= $n ?>" aria-expanded="<?= $n === 1 ? 'true' : 'false' ?>" aria-controls="svc-panel-<?= $n ?>">
          <span class="svc__icon"><svg class="icon"><use href="#i-<?= e($service['icon'] ?: 'finance') ?>"/></svg></span>
          <span class="svc__num"><?= sprintf('%02d', $n) ?></span>
          <span class="svc__name"><?= e($title) ?></span>
          <svg class="icon svc__chev"><use href="#i-chevron"/></svg>
        </button>
        <div class="svc__panel<?= $n === 1 ? ' is-open' : '' ?>" id="svc-panel-<?= $n ?>" role="region" aria-labelledby="svc-tab-<?= $n ?>">
          <div class="svc__body">
            <h3 class="svc__title"><?= e($title) ?></h3>
            <?php if (pick($service, 'lead') !== ''): ?><p class="svc__intro"><?= rich(pick($service, 'lead')) ?></p><?php endif; ?>
            <?php if ($service['points']): ?>
              <ol class="svc__points">
                <?php foreach ($service['points'] as $point): ?>
                  <li><h4><?= e(pick($point, 'title')) ?></h4><?php if (pick($point, 'text') !== ''): ?><p><?= rich(pick($point, 'text')) ?></p><?php endif; ?></li>
                <?php endforeach; ?>
              </ol>
            <?php endif; ?>
            <a class="link-arrow" href="#elaqe" data-service="<?= e($service['title_az']) ?>"><?= e(ui('svc_apply')) ?> <svg class="icon"><use href="#i-arrow"/></svg></a>
          </div>
          <?php if ($service['image']):
              // a lighter WebP copy next to the image is used where the browser supports it
              $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $service['image']);
              $hasWebp = $webp !== $service['image'] && is_file(BASE_PATH . $webp);
              ?>
            <figure class="svc__media svc__media--<?= e($media) ?>"><?php if ($hasWebp): ?><picture><source srcset="<?= e(site_asset($webp)) ?>" type="image/webp"><?php endif; ?><img src="<?= e(site_asset($service['image'])) ?>" alt="<?= e($title) ?>" loading="lazy" decoding="async" data-svc-img<?= img_size_attrs($service['image']) ?><?= $imgStyle ? ' style="' . e($imgStyle) . '"' : '' ?>><?php if ($hasWebp): ?></picture><?php endif; ?></figure>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
