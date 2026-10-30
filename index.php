<?php
/**
 * Front controller of the public site. Addresses:
 *   /, /haqqimizda, …        Azerbaijani (page addresses come from Admin → Pages)
 *   /ru/, /ru/haqqimizda, …  Russian; /en/… English
 *   /sitemap.xml
 * Apache sends every address that is not a real file here (deploy/htaccess.conf).
 */

declare(strict_types=1);

// PHP's built-in server (local preview): real files and folders such as /admin/ are served as they are.
if (PHP_SAPI === 'cli-server') {
    $file = realpath(__DIR__ . rawurldecode((string)parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($file && $file !== __DIR__ && $file !== __FILE__ && str_starts_with($file, __DIR__)) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/site.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

$path = rawurldecode((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$query = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_QUERY);
$segments = array_values(array_filter(explode('/', $path), 'strlen'));

$permanent = static function (string $to) use ($query): void {
    header('Location: ' . $to . ($query !== '' ? '?' . $query : ''), true, 301);
    exit;
};

// Design prototypes and the old version picker
if (in_array($segments[0] ?? '', ['v1', 'v2', 'v3', 'index.html', 'index.php'], true)) {
    $permanent('/');
}

if ($segments === ['sitemap.xml']) {
    require __DIR__ . '/app/views/sitemap.php';
    exit;
}

$lang = 'az';
if (in_array($segments[0] ?? '', ['ru', 'en'], true)) {
    $lang = array_shift($segments);
} elseif (($segments[0] ?? '') === 'az') {
    array_shift($segments);
    $permanent('/' . implode('/', $segments));
}
site_lang($lang);

$slug = $segments[0] ?? '';
$pageKey = null;
$post = null;
if (count($segments) <= 1) {
    $pageKey = page_by_slug($slug, $lang);
    if ($pageKey === null && count($segments) === 1 && ($moved = page_by_slug($slug, $lang, true))) {
        // An address of another language, e.g. the old /ru/haqqimizda → /ru/o-nas
        $permanent(url($moved));
    }
} elseif (count($segments) === 2 && page_by_slug($slug, $lang, true) === 'blog') {
    // A blog post: /bloq/<post>, /ru/blog/<post>; an address of another language leads to this one
    $post = post_by_slug($segments[1], $lang, true);
    if ($post && $path !== post_url($post)) {
        $permanent(post_url($post));
    }
    $pageKey = $post ? 'blog' : null;
}
if ($pageKey === null) {
    http_response_code(404);
    $pageKey = '404';
} elseif (!$post && $path !== url($pageKey)) {
    // One address per page: /ru → /ru/, /haqqimizda/ → /haqqimizda
    $permanent(url($pageKey));
}

require __DIR__ . '/app/views/page.php';
