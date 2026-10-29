<?php
/** /sitemap.xml: every page in every language, with hreflang alternates. */
defined('CCS') || exit;

$origin = site_origin();
$langs = array_keys(CONTENT_LANGS);
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach (site_pages() as $key => $page): ?>
<?php foreach ($langs as $lang): ?>
  <url>
    <loc><?= e($origin . url($key, $lang)) ?></loc>
<?php if (!empty($page['updated_at'])): ?>
    <lastmod><?= e(date('Y-m-d', strtotime($page['updated_at']) ?: time())) ?></lastmod>
<?php endif; ?>
<?php foreach ($langs as $alt): ?>
    <xhtml:link rel="alternate" hreflang="<?= $alt ?>" href="<?= e($origin . url($key, $alt)) ?>"/>
<?php endforeach; ?>
  </url>
<?php endforeach; ?>
<?php endforeach; ?>
</urlset>
