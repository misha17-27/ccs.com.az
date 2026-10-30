<?php
/** /sitemap.xml: the address of every page in every language (AZ, RU, EN in turn), then the blog posts while the blog is shown. */
defined('CCS') || exit;

$origin = site_origin();
$langs = array_keys(CONTENT_LANGS);
$paths = [];
foreach (array_keys(site_pages()) as $key) {
    if ($key !== 'blog' || blog_visible()) {
        foreach ($langs as $lang) {
            $paths[] = url($key, $lang);
        }
    }
}
foreach (blog_visible() ? site_posts() : [] as $post) {
    foreach ($langs as $lang) {
        $paths[] = post_url($post, $lang);
    }
}
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($paths as $path): ?>
  <url>
    <loc><?= e($origin . $path) ?></loc>
  </url>
<?php endforeach; ?>
</urlset>
