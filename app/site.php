<?php
/**
 * Public site: current language, page addresses, texts and content from the database.
 * The front controller is /index.php; templates are in app/views.
 */

declare(strict_types=1);
defined('CCS') || exit;

const SITE_NAME = 'Cost Control Services';

/** Share image (og:image) for pages without their own one and without one on the home page (Admin → Pages). */
const DEFAULT_OG_IMAGE = '/assets/img/og-image.jpg';

/** Service images that ship with the site: [panel background, extra image style] (see .svc__media in site.css). */
const SERVICE_MEDIA = [
    '/assets/img/render/wallet.jpg' => ['dark', 'object-position:50% 85%'],
    '/assets/img/render/chart.jpg' => ['light', ''],
    '/assets/img/render/gear.jpg' => ['dark svc__media--bottom', ''],
    '/assets/img/render/document.jpg' => ['light', ''],
    '/assets/img/render/box.jpg' => ['dark', ''],
    '/assets/img/render/lock.jpg' => ['light', 'object-position:50% 60%'],
];

function site_lang(?string $set = null): string
{
    static $lang = 'az';
    if ($set !== null && isset(CONTENT_LANGS[$set])) {
        $lang = $set;
    }
    return $lang;
}

/**
 * Interface string of the public site (app/site_lang/{lang}.php). Strings listed in UI_FIELDS can be
 * changed in Admin → Texts; an empty value there keeps the default of that language.
 */
function ui(string $key, array $vars = []): string
{
    static $dict = [];
    $lang = site_lang();
    $dict[$lang] ??= require __DIR__ . "/site_lang/{$lang}.php";
    $custom = isset(UI_FIELDS[$key]) ? trim((string)(text_rows()['ui.' . $key]['value_' . $lang] ?? '')) : '';
    $text = $custom !== '' ? $custom : ($dict[$lang][$key] ?? $key);
    foreach ($vars as $name => $value) {
        $text = str_replace('{' . $name . '}', (string)$value, $text);
    }
    return $text;
}

/** Rows of Admin → Texts by key. */
function text_rows(): array
{
    static $rows = null;
    if ($rows === null) {
        $rows = [];
        foreach (db()->query('SELECT * FROM texts') as $row) {
            $rows[$row['key']] = $row;
        }
    }
    return $rows;
}

/** Site text from Admin → Texts in the current language; an empty translation falls back to AZ. */
function txt(string $key): string
{
    $rows = text_rows();
    if (!isset($rows[$key])) {
        return TEXT_FIELDS[$key][4][site_lang()] ?? '';
    }
    return pick($rows[$key], 'value');
}

/** Column col_{lang} of a row, falling back to col_az. */
function pick(array $row, string $col): string
{
    $value = trim((string)($row[$col . '_' . site_lang()] ?? ''));
    return $value !== '' ? $value : trim((string)($row[$col . '_az'] ?? ''));
}

/** Escapes a text and applies its light markup: **bold**, *accent colour*, line breaks. */
function rich(string $text): string
{
    $html = e($text);
    $html = preg_replace('/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/su', '<span class="hl">$1</span>', $html);
    return nl2br($html, false);
}

/** The text without markup, for titles, meta tags and alt texts. */
function plain(string $text): string
{
    return trim(preg_replace('/\s+/u', ' ', str_replace('*', '', $text)));
}

// ---------- Pages and addresses ----------

/** Pages from Admin → Pages: key => row, in PAGES order. */
function site_pages(): array
{
    static $pages = null;
    if ($pages === null) {
        $rows = [];
        foreach (db()->query('SELECT * FROM pages') as $row) {
            $rows[$row['key']] = $row;
        }
        $pages = [];
        foreach (PAGES as $key => $def) {
            $pages[$key] = $rows[$key] ?? ['key' => $key, 'slug' => $def['slug'], 'slug_ru' => $def['slug_ru'], 'slug_en' => $def['slug_en']];
        }
    }
    return $pages;
}

/** Address part of a page or blog post in a language: the column slug is AZ, slug_ru and slug_en the others (empty — the AZ one). */
function page_slug(array $page, string $lang): string
{
    $slug = $lang === 'az' ? '' : trim((string)($page['slug_' . $lang] ?? ''));
    return $slug !== '' ? $slug : (string)$page['slug'];
}

/** Page key by its address in a language; with $anyLang, an address of another language also matches. */
function page_by_slug(string $slug, string $lang, bool $anyLang = false): ?string
{
    foreach (site_pages() as $key => $page) {
        foreach ($anyLang ? array_keys(CONTENT_LANGS) : [$lang] as $l) {
            if (page_slug($page, $l) === $slug) {
                return $key;
            }
        }
    }
    return null;
}

/** Address of a page: / and /haqqimizda in AZ, /ru/ and /ru/o-nas, /en/ and /en/about-us in the others. */
function url(string $pageKey = 'home', ?string $lang = null, string $hash = ''): string
{
    $lang ??= site_lang();
    $page = site_pages()[$pageKey] ?? ['slug' => ''];
    return '/' . ($lang === 'az' ? '' : $lang . '/') . page_slug($page, $lang) . ($hash !== '' ? '#' . $hash : '');
}

function site_origin(): string
{
    $host = preg_replace('/[^a-z0-9.:-]/i', '', (string)($_SERVER['HTTP_HOST'] ?? '')) ?: 'ccs.com.az';
    $local = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host);
    return ($local && !is_https() ? 'http' : 'https') . '://' . $host;
}

/** Asset URL with a file-time version: a deploy changes it, so browsers and Cloudflare fetch the new file. */
function site_asset(string $path): string
{
    $file = BASE_PATH . $path;
    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

/** [width, height] of a local image, for the img attributes. */
function img_size(string $src): array
{
    $info = str_starts_with($src, '/') ? @getimagesize(BASE_PATH . $src) : false;
    return $info ? [$info[0], $info[1]] : [0, 0];
}

function img_size_attrs(string $src): string
{
    [$w, $h] = img_size($src);
    return $w ? ' width="' . $w . '" height="' . $h . '"' : '';
}

// ---------- Content ----------

/** Active services with their points, in admin order. */
function site_services(): array
{
    static $services = null;
    if ($services === null) {
        $services = db()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort, id')->fetchAll();
        $points = db()->prepare('SELECT * FROM service_points WHERE service_id = ? ORDER BY sort, id');
        foreach ($services as &$service) {
            $points->execute([$service['id']]);
            $service['points'] = $points->fetchAll();
        }
        unset($service);
    }
    return $services;
}

function site_partners(): array
{
    return db()->query('SELECT * FROM partners WHERE is_active = 1 ORDER BY sort, id')->fetchAll();
}

function site_faqs(): array
{
    return db()->query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort, id')->fetchAll();
}

/** Active projects of the experience block, each with its 'photos' (at most PROJECT_PHOTOS_MAX). */
function site_projects(): array
{
    $db = db();
    $photos = [];
    foreach ($db->query('SELECT * FROM project_photos ORDER BY sort, id') as $photo) {
        $photos[$photo['project_id']][] = $photo;
    }
    return array_map(
        static fn(array $p) => $p + ['photos' => array_slice($photos[$p['id']] ?? [], 0, PROJECT_PHOTOS_MAX)],
        $db->query('SELECT * FROM projects WHERE is_active = 1 ORDER BY sort, id')->fetchAll()
    );
}

// ---------- Blog (Admin → Blog) ----------

/** Whether the blog is shown on the site: in the menu, the footer, the sitemap and to search engines. */
function blog_visible(): bool
{
    return setting('blog_visible') === '1';
}

/** Published posts, newest first; a post dated in the future appears on that day. */
function site_posts(): array
{
    static $posts = null;
    if ($posts === null) {
        $stmt = db()->prepare('SELECT * FROM posts WHERE is_active = 1 AND published_at <= ? ORDER BY published_at DESC, id DESC');
        $stmt->execute([date('Y-m-d')]);
        $posts = $stmt->fetchAll();
    }
    return $posts;
}

/** Published post by its address in a language; with $anyLang, an address of another language also matches. */
function post_by_slug(string $slug, string $lang, bool $anyLang = false): ?array
{
    foreach (site_posts() as $post) {
        foreach ($anyLang ? array_keys(CONTENT_LANGS) : [$lang] as $l) {
            if (page_slug($post, $l) === $slug) {
                return $post;
            }
        }
    }
    return null;
}

/** Address of a post: /bloq/<post> in AZ, /ru/blog/<post>, /en/blog/<post>. */
function post_url(array $post, ?string $lang = null): string
{
    $lang ??= site_lang();
    return url('blog', $lang) . '/' . page_slug($post, $lang);
}

/** Date of a post as 30.10.2026. */
function post_date(array $post): string
{
    return date('d.m.Y', strtotime((string)$post['published_at']) ?: time());
}

/**
 * Text of a post as HTML. Blocks are separated by an empty line: "## " and "### " start a heading,
 * "- " lines make a list, "1. " lines a numbered list, anything else a paragraph. Inside them works
 * the markup of rich() and links: [text](https://…) or [text](/elaqe).
 */
function article_html(string $text): string
{
    $inline = static fn(string $s): string => preg_replace_callback(
        '~\[([^\]\n]+)\]\(((?:https?://|/)[^\s)]+)\)~u',
        static fn(array $m) => '<a href="' . $m[2] . '"' . ($m[2][0] === '/' ? '' : ' target="_blank" rel="noopener"') . '>' . $m[1] . '</a>',
        rich($s)
    );
    $text = str_replace("\r", '', $text);
    // a heading, and a list right after a line of text, also start a block without an empty line
    $text = preg_replace('/^(#{2,3}\s[^\n]*)$/mu', "\n$1\n", $text);
    $text = preg_replace('/^((?!-\s|\d+\.\s)[^\n]+)\n(?=(?:-|\d+\.)\s)/mu', "$1\n\n", $text);
    $html = '';
    foreach (preg_split('/\n\s*\n/u', trim($text)) as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (preg_match('/^(#{2,3})\s+(.+)$/u', $block, $m)) {
            $tag = 'h' . strlen($m[1]);
            $html .= "<{$tag}>" . $inline($m[2]) . "</{$tag}>\n";
        } elseif (preg_match('/^(-|\d+\.)\s/u', $block, $m)) {
            $tag = $m[1] === '-' ? 'ul' : 'ol';
            $items = preg_split('/\n(?=(?:-|\d+\.)\s)/u', $block);
            $html .= "<{$tag}>" . implode('', array_map(
                static fn(string $item) => '<li>' . $inline(preg_replace('/^(?:-|\d+\.)\s+/u', '', $item)) . '</li>',
                $items
            )) . "</{$tag}>\n";
        } else {
            $html .= '<p>' . $inline($block) . "</p>\n";
        }
    }
    return $html;
}

// ---------- Contacts (Admin → Settings) ----------

function phone_display(): string
{
    return setting('contact_phone', '+994 55 855 61 52');
}

function phone_href(): string
{
    return 'tel:+' . preg_replace('/\D+/', '', phone_display());
}

function whatsapp_url(bool $withGreeting = false): string
{
    $number = preg_replace('/\D+/', '', setting('contact_whatsapp', '994558556152'));
    return 'https://wa.me/' . $number . ($withGreeting ? '?text=' . rawurlencode(ui('wa_greeting')) : '');
}

function instagram_url(): string
{
    return setting('contact_instagram', 'https://www.instagram.com/costcontrol.services/');
}

/** "@costcontrol.services" from the Instagram link. */
function instagram_handle(): string
{
    $path = trim((string)parse_url(instagram_url(), PHP_URL_PATH), '/');
    return '@' . ($path !== '' ? explode('/', $path)[0] : 'costcontrol.services');
}

function tiktok_url(): string
{
    return setting('contact_tiktok', 'https://www.tiktok.com/@costcontrol.services');
}

/** "@costcontrol.services" from the TikTok link. */
function tiktok_handle(): string
{
    $path = trim((string)parse_url(tiktok_url(), PHP_URL_PATH), '/');
    $first = explode('/', $path)[0];
    return preg_match('/^@[\w.]+$/', $first) ? $first : '@costcontrol.services';
}

// ---------- Structured data ----------

/**
 * schema.org data of a page (JSON-LD): the company with its services on every page, the website on the
 * home page, breadcrumbs on inner pages, the questions on pages with the FAQ block and the article on a blog post.
 */
function site_schema(string $pageKey, ?array $post = null): array
{
    $origin = site_origin();
    $pages = site_pages();
    $home = $pages['home'];
    $company = $origin . '/#company';

    $org = [
        '@type' => 'ProfessionalService',
        '@id' => $company,
        'name' => SITE_NAME,
        'alternateName' => 'Cost Control',
        'url' => $origin . url('home'),
        'logo' => $origin . '/assets/img/logo-mark.svg',
        'description' => pick($home, 'seo_description'),
        'telephone' => '+' . preg_replace('/\D+/', '', phone_display()),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => txt('contact.address'),
            'addressLocality' => ui('city'),
            'addressCountry' => 'AZ',
        ],
        'areaServed' => ['@type' => 'Country', 'name' => ui('country')],
        'foundingDate' => '2021',
        'knowsAbout' => array_values(array_filter(array_map('trim', explode(',', pick($home, 'keywords'))), 'strlen')),
        'sameAs' => [instagram_url(), tiktok_url()],
        'hasOfferCatalog' => [
            '@type' => 'OfferCatalog',
            'name' => plain(pick($pages['services'], 'h1') ?: pick($pages['services'], 'name')),
            'itemListElement' => array_map(static fn(array $service) => [
                '@type' => 'Offer',
                'itemOffered' => ['@type' => 'Service', 'name' => pick($service, 'title'), 'description' => plain(pick($service, 'lead'))],
            ], site_services()),
        ],
    ];
    if (setting('contact_email') !== '') {
        $org['email'] = setting('contact_email');
    }
    if (!empty($home['og_image'])) {
        $org['image'] = $origin . $home['og_image'];
    }
    $graph = [$org];

    if ($pageKey === 'home') {
        $graph[] = ['@type' => 'WebSite', '@id' => $origin . '/#website', 'name' => SITE_NAME, 'url' => $origin . url('home'),
            'inLanguage' => site_lang(), 'publisher' => ['@id' => $company]];
    } elseif (isset($pages[$pageKey])) {
        $crumbs = [[pick($home, 'name'), url('home')], [pick($pages[$pageKey], 'name'), url($pageKey)]];
        if ($post) {
            $crumbs[] = [pick($post, 'title'), post_url($post)];
        }
        $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(
            static fn(array $crumb, int $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb[0], 'item' => $origin . $crumb[1]],
            $crumbs, array_keys($crumbs)
        )];
    }

    if ($post) {
        $article = [
            '@type' => 'BlogPosting',
            'headline' => pick($post, 'title'),
            'description' => plain(pick($post, 'seo_description') ?: pick($post, 'excerpt')),
            'datePublished' => date('Y-m-d', strtotime((string)$post['published_at']) ?: time()),
            'dateModified' => date('c', strtotime((string)$post['updated_at']) ?: time()),
            'inLanguage' => site_lang(),
            'mainEntityOfPage' => $origin . post_url($post),
            'author' => ['@id' => $company],
            'publisher' => ['@id' => $company],
        ];
        if ($post['cover']) {
            $article['image'] = $origin . $post['cover'];
        }
        $graph[] = $article;
    }

    $faqs = in_array('faq', PAGES[$pageKey]['sections'] ?? [], true) ? site_faqs() : [];
    if ($faqs) {
        $graph[] = ['@type' => 'FAQPage', 'mainEntity' => array_map(static fn(array $faq) => [
            '@type' => 'Question',
            'name' => plain(pick($faq, 'question')),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => plain(pick($faq, 'answer'))],
        ], $faqs)];
    }
    return ['@context' => 'https://schema.org', '@graph' => $graph];
}

/** Renders app/views/sections/{$section}.php with $vars as its variables. */
function section(string $section, array $vars = []): void
{
    (static function (string $__file, array $__vars): void {
        extract($__vars, EXTR_SKIP);
        require $__file;
    })(__DIR__ . '/views/sections/' . $section . '.php', $vars);
}
