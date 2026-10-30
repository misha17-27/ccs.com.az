<?php
/**
 * Blog from Admin → Blog: the list of posts, or one post on its own page.
 * @var array|null $post
 */
defined('CCS') || exit;

$cover = static function (?string $src, string $alt, string $class, string $loading): string {
    if (!$src) {
        return '<span class="' . $class . ' post-media--empty" aria-hidden="true"><img src="/assets/img/logo-mark-white.svg" alt="" width="54" height="48"></span>';
    }
    return '<span class="' . $class . '"><img src="' . e(site_asset($src)) . '" alt="' . e($alt) . '" loading="' . $loading . '" decoding="async"' . img_size_attrs($src) . '></span>';
};
?>
<?php if ($post ?? null): ?>
<!-- SECTION: blog post -->
<article class="post section" id="bloq">
  <div class="container post__inner">
    <?php if ($post['cover']): ?><?= $cover($post['cover'], pick($post, 'title'), 'post__cover', 'eager') ?><?php endif; ?>
    <div class="prose"><?= post_body_html(pick($post, 'body')) ?></div>
    <a class="link-arrow post__back" href="<?= e(url('blog')) ?>"><svg class="icon icon--flip"><use href="#i-arrow"/></svg> <?= e(ui('blog_back')) ?></a>
  </div>
</article>
<?php else: $posts = site_posts(); ?>
<!-- SECTION: blog -->
<section class="blog section" id="bloq">
  <div class="container">
    <?php if (!$posts): ?>
      <p class="blog__empty" data-reveal><?= e(ui('blog_empty')) ?></p>
    <?php else: ?>
      <ul class="blog-grid">
        <?php foreach ($posts as $i => $item): $title = pick($item, 'title'); ?>
          <li data-reveal style="--delay:<?= ($i % 3) * .08 ?>s">
            <a class="post-card" href="<?= e(post_url($item)) ?>">
              <?= $cover($item['cover'], $title, 'post-card__media', 'lazy') ?>
              <div class="post-card__body">
                <time class="post-card__date" datetime="<?= e(substr((string)$item['published_at'], 0, 10)) ?>"><?= e(post_date($item)) ?></time>
                <h2 class="post-card__title"><?= e($title) ?></h2>
                <?php if (pick($item, 'excerpt') !== ''): ?><p class="post-card__text"><?= e(plain(pick($item, 'excerpt'))) ?></p><?php endif; ?>
                <span class="link-arrow"><?= e(ui('blog_read')) ?> <svg class="icon"><use href="#i-arrow"/></svg></span>
              </div>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>
