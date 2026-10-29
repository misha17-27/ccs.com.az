<?php
/** AZ / RU / EN links to the same page. @var string $langClass @var callable $langUrl */
defined('CCS') || exit;
?>
<div class="lang <?= e($langClass) ?>" role="group" aria-label="<?= e(ui('lang_label')) ?>">
  <?php foreach (CONTENT_LANGS as $code => $short): ?>
    <a class="lang__btn<?= $code === site_lang() ? ' is-active' : '' ?>" href="<?= e($langUrl($code)) ?>" hreflang="<?= $code ?>" lang="<?= $code ?>"<?= $code === site_lang() ? ' aria-current="true"' : '' ?>><?= $short ?></a>
  <?php endforeach; ?>
</div>
