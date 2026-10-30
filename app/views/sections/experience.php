<?php
/**
 * International experience: projects from Admin → Projects. The photos of the active project fill the gallery;
 * when several projects have photos, a click on a project shows its photos (site.js).
 */
defined('CCS') || exit;

$projects = site_projects();
if (!$projects) {
    return;
}
$withPhotos = array_values(array_filter($projects, static fn($p) => $p['photos']));
$switchable = count($withPhotos) > 1;
$activeId = $withPhotos[0]['id'] ?? 0;
// photos shown large in each gallery layout (n1 and n3 are sized in site.css)
$bigPhotos = [2 => [0], 4 => [0, 3]];
?>
<!-- SECTION: experience -->
<section class="experience section" id="tecrube">
  <div class="container experience__grid<?= $withPhotos ? '' : ' experience__grid--text' ?>" data-exp>
    <div class="experience__text" data-reveal>
      <p class="eyebrow"><?= e(txt('experience.eyebrow')) ?></p>
      <h2 class="section-title"><?= rich(txt('experience.title')) ?></h2>
      <ul class="exp-list">
        <?php foreach ($projects as $p):
            $tag = pick($p, 'tag');
            $inner = ($tag !== '' ? '<span class="exp-item__tag">' . e($tag) . '</span>' : '')
                . '<span class="exp-item__text">' . rich(pick($p, 'text')) . '</span>';
            $class = 'exp-item' . ($tag === '' ? ' exp-item--plain' : '');
            ?>
          <li>
            <?php if ($switchable && $p['photos']): $n = count($p['photos']); ?>
              <button class="<?= $class ?> exp-item--btn" type="button" data-exp-tab aria-controls="exp-gallery-<?= (int)$p['id'] ?>" aria-pressed="<?= $p['id'] === $activeId ? 'true' : 'false' ?>">
                <?= $inner ?>
                <span class="exp-item__photos" aria-hidden="true"><svg class="icon"><use href="#i-photo"/></svg><?= $n ?></span>
                <span class="sr-only"> (<?= e(ui('exp_photos', ['n' => $n])) ?>)</span>
              </button>
            <?php else: ?>
              <div class="<?= $class ?>"><?= $inner ?></div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <?php if ($withPhotos): ?>
      <div class="experience__media" data-reveal style="--delay:.1s">
        <?php foreach ($withPhotos as $p):
            $n = count($p['photos']);
            $caption = pick($p, 'caption');
            $alt = $caption !== '' ? $caption : pick($p, 'tag');
            ?>
          <div class="experience__gallery experience__gallery--n<?= $n ?>" id="exp-gallery-<?= (int)$p['id'] ?>"<?= $p['id'] === $activeId ? '' : ' hidden' ?>>
            <?php foreach ($p['photos'] as $i => $photo):
                $focus = PHOTO_FOCUS[$photo['focus']] ?? PHOTO_FOCUS['center'];
                ?>
              <figure class="gallery__item<?= in_array($i, $bigPhotos[$n] ?? [], true) ? ' gallery__item--big' : '' ?>">
                <img src="<?= e(site_asset($photo['path'])) ?>" alt="<?= e($alt) ?>" loading="lazy" decoding="async" data-exp-img<?= img_size_attrs($photo['path']) ?><?= $focus !== PHOTO_FOCUS['center'] ? ' style="object-position:' . $focus . '"' : '' ?>>
                <?php if ($i === 0 && $caption !== ''): ?><figcaption><?= e($caption) ?></figcaption><?php endif; ?>
              </figure>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
