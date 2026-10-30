<?php
/** Small helpers: escaping, requests, flash messages, CSRF, settings, uploads. */

declare(strict_types=1);
defined('CCS') || exit;

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function redirect(string $url): void
{
    header('Location: ' . $url, true, 303);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Trimmed string from $_POST, cut to $max characters. */
function post_str(string $key, int $max = 2000): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? mb_substr(trim($value), 0, $max) : '';
}

function post_int(string $key, int $default = 0): int
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    return is_int($value) ? $value : $default;
}

function get_str(string $key, int $max = 200): string
{
    $value = $_GET[$key] ?? '';
    return is_string($value) ? mb_substr(trim($value), 0, $max) : '';
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/**
 * Visitor IP. Behind Cloudflare the real address is in CF-Connecting-IP, but that header
 * is trusted only when the request really comes from a Cloudflare edge address.
 */
function client_ip(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP) && ip_in_ranges($remote, CLOUDFLARE_RANGES)) {
        return $cf;
    }
    return $remote;
}

const CLOUDFLARE_RANGES = [
    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
    '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
    '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
    '2a06:98c0::/29', '2c0f:f248::/32',
];

function ip_in_ranges(string $ip, array $ranges): bool
{
    $bin = @inet_pton($ip);
    if ($bin === false) {
        return false;
    }
    foreach ($ranges as $range) {
        [$subnet, $bits] = explode('/', $range);
        $net = @inet_pton($subnet);
        if ($net === false || strlen($net) !== strlen($bin)) {
            continue;
        }
        $bits = (int)$bits;
        $bytes = intdiv($bits, 8);
        if (substr($bin, 0, $bytes) !== substr($net, 0, $bytes)) {
            continue;
        }
        $rest = $bits % 8;
        if ($rest === 0 || ((ord($bin[$bytes]) ^ ord($net[$bytes])) & (0xFF << (8 - $rest)) & 0xFF) === 0) {
            return true;
        }
    }
    return false;
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------- Flash messages & CSRF (need an active session) ----------

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Every state-changing request must carry the session's CSRF token. */
function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Invalid CSRF token. Reload the page and try again.');
    }
}

// ---------- Settings & texts ----------

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = db()->query('SELECT key, value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return array_key_exists($key, $cache) && $cache[$key] !== null ? (string)$cache[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    // INSERT OR REPLACE instead of an upsert: older SQLite builds on shared hosting lack ON CONFLICT … DO UPDATE.
    db()->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)')->execute([$key, $value]);
}

/** Site text in the given content language, falling back to Azerbaijani. */
function text(string $key, string $lang = 'az'): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT * FROM texts') as $row) {
            $cache[$row['key']] = $row;
        }
    }
    $row = $cache[$key] ?? null;
    if (!$row) {
        return '';
    }
    $value = (string)($row['value_' . $lang] ?? '');
    return $value !== '' ? $value : (string)$row['value_az'];
}

// ---------- Text markup ----------

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

/**
 * Plain-text markup of a blog post as HTML (posts written before the editor, or typed as text in its
 * source view). Blocks are separated by an empty line: "## " and "### " start a heading, "- " lines make
 * a list, "1. " lines a numbered list, anything else a paragraph. Inside them works the markup of rich()
 * and links: [text](https://…) or [text](/elaqe).
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

/** Text of a blog post as HTML: what the editor saved as it is, the plain markup of older posts converted. */
function post_body_html(string $body): string
{
    return preg_match('~<(p|h[1-6]|ul|ol|li|br|strong|b|em|i|a|div)\b~i', $body) ? $body : article_html($body);
}

/**
 * HTML from the blog editor, reduced to what the site styles: paragraphs, H2/H3, lists, bold, italic,
 * line breaks and links (https, mailto, tel or a site address such as /elaqe). Other tags keep only
 * their text, and scripts, styles, images and frames are dropped, so text pasted from Word or Google Docs
 * comes out clean; their bold and italic set with styles are kept.
 */
function clean_html(string $html): string
{
    if (trim(strip_tags($html)) === '') {
        return '';
    }
    $doc = new DOMDocument();
    $errors = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($errors);
    $root = null;
    foreach ($doc->childNodes as $node) {
        if ($node instanceof DOMElement) {
            $root = $node;
            break;
        }
    }
    if (!$root) {
        return '';
    }

    $blocks = ['p' => 'p', 'div' => 'p', 'blockquote' => 'p', 'li' => 'li', 'ul' => 'ul', 'ol' => 'ol',
        'h1' => 'h2', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h3', 'h5' => 'h3', 'h6' => 'h3'];
    $drop = ['script', 'style', 'head', 'title', 'meta', 'link', 'iframe', 'frame', 'object', 'embed', 'svg', 'math',
        'noscript', 'template', 'img', 'picture', 'video', 'audio', 'canvas', 'form', 'input', 'button', 'select', 'textarea'];
    $xpath = new DOMXPath($doc);
    $hasBlocks = static fn(DOMElement $el): bool => $xpath->query('.//p|.//div|.//blockquote|.//li|.//ul|.//ol|.//h1|.//h2|.//h3|.//h4|.//h5|.//h6', $el)->length > 0;
    $hasText = static fn(string $s): bool => trim(str_replace('&nbsp;', ' ', strip_tags($s))) !== '';
    // bold and italic: tags, or styles from Word and Google Docs (whose whole paste sits in <b style="font-weight:normal">)
    $marks = static function (DOMElement $el, string $tag): array {
        $style = strtolower($el->getAttribute('style'));
        $normal = preg_match('/(?<![\w-])font-weight\s*:\s*(normal|[1-4]00)\b/', $style);
        return [
            !$normal && (in_array($tag, ['b', 'strong'], true) || preg_match('/(?<![\w-])font-weight\s*:\s*(bold|[6-9]00)\b/', $style)),
            in_array($tag, ['i', 'em'], true) || preg_match('/(?<![\w-])font-style\s*:\s*italic/', $style),
        ];
    };

    // One node inside a block: $in is the block it goes into (p, h2, h3, li, ul or ol)
    $node = function (DOMNode $n, string $in) use (&$node, $blocks, $drop, $marks, $hasText): string {
        if ($n instanceof DOMText) {
            return $in === 'ul' || $in === 'ol' ? '' : e(str_replace("\u{a0}", ' ', $n->nodeValue));
        }
        if (!$n instanceof DOMElement) {
            return '';
        }
        $tag = strtolower($n->tagName);
        if (in_array($tag, $drop, true)) {
            return '';
        }
        if ($tag === 'br') {
            return '<br>';
        }
        $children = static function (string $into) use ($n, &$node): string {
            $html = '';
            foreach ($n->childNodes as $child) {
                $html .= $node($child, $into);
            }
            return $html;
        };
        $block = $blocks[$tag] ?? null;
        if ($block) {
            // a list holds items, an item may hold a nested list; any other block keeps just its content
            $allowed = in_array($in, ['ul', 'ol'], true) ? $block === 'li' : ($in === 'li' && in_array($block, ['ul', 'ol'], true));
            $inner = $children($allowed ? $block : $in);
            return $allowed ? "<{$block}>{$inner}</{$block}>" : $inner . ($block === 'p' ? ' ' : '');
        }
        $inner = $children($in);
        if ($tag === 'a') {
            $href = trim($n->getAttribute('href'));
            if ($hasText($inner) && preg_match('~^(https?://|mailto:|tel:|/(?!/))~i', $href)) {
                $external = (bool)preg_match('~^https?://~i', $href);
                return '<a href="' . e($href) . '"' . ($external ? ' target="_blank" rel="noopener"' : '') . '>' . $inner . '</a>';
            }
            return $inner;
        }
        [$bold, $italic] = $marks($n, $tag);
        if ($hasText($inner)) {
            $inner = $italic ? "<em>{$inner}</em>" : $inner;
            $inner = $bold ? "<strong>{$inner}</strong>" : $inner;
        }
        return $inner;
    };

    // Top level: blocks as they are, loose text and inline tags gathered into paragraphs
    $parts = [];
    $top = function (DOMNode $parent) use (&$top, &$parts, $node, $blocks, $drop, $hasBlocks): void {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, $drop, true)) {
                    continue;
                }
                $block = $blocks[$tag] ?? null;
                // a <div> around paragraphs, or a list the browser left inside a paragraph: take its parts one by one
                if (in_array($block, ['p', 'h2', 'h3', 'li'], true) && $hasBlocks($child)) {
                    $top($child);
                    continue;
                }
                if ($block) {
                    $block = $block === 'li' ? 'p' : $block;
                    $inner = '';
                    foreach ($child->childNodes as $grand) {
                        $inner .= $node($grand, $block);
                    }
                    $parts[] = [$block, $inner];
                    continue;
                }
                if ($hasBlocks($child)) {
                    $top($child);
                    continue;
                }
            }
            $parts[] = ['', $node($child, 'p')];
        }
    };
    $top($root);

    $out = [];
    $buffer = '';
    $trimBreaks = static fn(string $s): string => trim(preg_replace('~^(\s*<br>)+|(<br>\s*)+$~', '', trim($s)));
    $flush = static function () use (&$buffer, &$out, $trimBreaks, $hasText): void {
        if ($hasText($buffer)) {
            $out[] = '<p>' . $trimBreaks($buffer) . '</p>';
        }
        $buffer = '';
    };
    foreach ($parts as [$block, $inner]) {
        if ($block === '') {
            $buffer .= $inner;
            continue;
        }
        $flush();
        if (in_array($block, ['ul', 'ol'], true) ? str_contains($inner, '<li>') : $hasText($inner)) {
            $out[] = "<{$block}>" . ($block === 'ul' || $block === 'ol' ? $inner : $trimBreaks($inner)) . "</{$block}>";
        }
    }
    $flush();
    return implode("\n", $out);
}

// ---------- Uploads ----------

/**
 * Stores an uploaded image under /uploads/<folder>/ with a random name.
 * Returns the public path, null when no file was sent, or throws on invalid input.
 */
function store_upload(string $field, string $folder): ?string
{
    $file = $_FILES[$field] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    return store_upload_file($file, $folder);
}

/** Files of a multiple file input (name="field[]"), each as a single $_FILES entry; empty slots are skipped. */
function uploaded_files(string $field): array
{
    $files = $_FILES[$field] ?? null;
    if (!$files || !is_array($files['name'] ?? null)) {
        return [];
    }
    $out = [];
    foreach (array_keys($files['name']) as $i) {
        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $out[] = array_combine(['name', 'type', 'tmp_name', 'error', 'size'],
            [$files['name'][$i], $files['type'][$i], $files['tmp_name'][$i], $files['error'][$i], $files['size'][$i]]);
    }
    return $out;
}

/** Stores one uploaded file (a $_FILES entry) like store_upload(); throws on invalid input. */
function store_upload_file(array $file, string $folder): string
{
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException(t('upload.failed'));
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException(t('upload.too_big', ['mb' => MAX_UPLOAD_BYTES / 1048576]));
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($types[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException(t('upload.bad_type'));
    }
    $folder = preg_replace('/[^a-z0-9_-]/', '', $folder);
    $dir = UPLOADS_DIR . '/' . $folder;
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException(t('upload.failed'));
    }
    $name = bin2hex(random_bytes(10)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException(t('upload.failed'));
    }
    @chmod($dir . '/' . $name, 0644);
    return UPLOADS_URL . '/' . $folder . '/' . $name;
}

/** Latin address part from a title in AZ, RU or EN: "Restoran auditi nədir?" → restoran-auditi-nedir. */
function slugify(string $text, int $max = 80): string
{
    static $map = [
        'ə' => 'e', 'ı' => 'i', 'ö' => 'o', 'ü' => 'u', 'ğ' => 'g', 'ş' => 's', 'ç' => 'c',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    // İ and I first: lowercasing İ leaves a combining dot behind
    $text = strtr(mb_strtolower(str_replace(['İ', 'I'], 'i', $text)), $map);
    $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    return rtrim(substr($slug, 0, $max), '-');
}

/**
 * Scales a stored JPEG photo down to $max px on the long side (phone photos are often 3–5 MB) and
 * turns it upright. Left as it is without GD, or when its EXIF orientation cannot be read.
 */
function shrink_photo(string $path, int $max = 1600): void
{
    $file = UPLOADS_DIR . substr($path, strlen(UPLOADS_URL));
    $info = @getimagesize($file);
    if (!$info || $info[2] !== IMAGETYPE_JPEG || !function_exists('imagecreatefromjpeg')) {
        return;
    }
    if (function_exists('exif_read_data')) {
        $orientation = (int)(@exif_read_data($file)['Orientation'] ?? 1);
    } else {
        $orientation = str_contains((string)file_get_contents($file, false, null, 0, 65536), "Exif\0\0") ? 0 : 1;
    }
    $turn = [1 => 0, 3 => 180, 6 => -90, 8 => 90][$orientation] ?? null;   // mirrored or unknown: keep the file
    if ($turn === null || ($turn === 0 && max($info[0], $info[1]) <= $max)) {
        return;
    }
    $img = @imagecreatefromjpeg($file);
    if (!$img) {
        return;
    }
    if ($turn) {
        $turned = imagerotate($img, $turn, 0);
        imagedestroy($img);
        $img = $turned;
    }
    [$w, $h] = [imagesx($img), imagesy($img)];
    if (max($w, $h) > $max) {
        $k = $max / max($w, $h);
        $scaled = imagescale($img, (int)round($w * $k), (int)round($h * $k), IMG_BICUBIC);
        imagedestroy($img);
        $img = $scaled;
    }
    imageinterlace($img, true);
    imagejpeg($img, $file, 82);
    imagedestroy($img);
}

/** Deletes a file previously stored by store_upload(); other paths are left alone. */
function delete_upload(?string $path): void
{
    if ($path && preg_match('#^' . preg_quote(UPLOADS_URL, '#') . '/[a-z0-9_-]+/[a-f0-9]{20}\.(jpg|png|webp)$#', $path)) {
        @unlink(UPLOADS_DIR . substr($path, strlen(UPLOADS_URL)));
    }
}

// ---------- Formatting ----------

function format_bytes(float $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, $i ? 1 : 0) . ' ' . $units[$i];
}

function dir_size(string $dir): int
{
    if (!is_dir($dir)) {
        return 0;
    }
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

function http_post_form(string $url, array $fields): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    $data = is_string($body) ? json_decode($body, true) : null;
    return is_array($data) ? $data : null;
}
