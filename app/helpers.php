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
