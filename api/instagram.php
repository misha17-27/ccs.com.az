<?php
/**
 * Instagram feed proxy for ccs.com.az.
 *
 * GET /api/instagram.php → latest posts of @costcontrol.services as JSON:
 *   { "source": "live", "updated": "2026-10-28T10:00:00+04:00",
 *     "items": [ { "id", "permalink", "image", "type", "caption", "timestamp" } ] }
 *
 * Uses the Instagram API with Instagram Login (graph.instagram.com). The access token
 * stays in a private directory outside public_html and never reaches the browser.
 * Responses are cached on disk, cover images are stored under /media/instagram/,
 * and the long-lived token (valid 60 days) is refreshed automatically.
 *
 * Setup: see README.md → "Instagram".
 */

declare(strict_types=1);

// PRIVATE_DIR (/home/<user>/ccs-private, outside public_html) is shared with the admin panel.
define('CCS', true);
require dirname(__DIR__) . '/app/config.php';
define('MEDIA_DIR', dirname(__DIR__) . '/media/instagram');
const MEDIA_URL = '/media/instagram/';

const TOKEN_TXT = PRIVATE_DIR . '/instagram-token.txt';   // written by Admin → Instagram (or by hand)
const STATE_FILE = PRIVATE_DIR . '/instagram-state.json'; // token + last refresh, managed here
const CACHE_FILE = PRIVATE_DIR . '/instagram-cache.json';
const LOCK_FILE = PRIVATE_DIR . '/instagram.lock';

const CACHE_TTL = 1800;           // seconds between Instagram API calls
const REFRESH_EVERY = 7 * 86400;  // refresh the token weekly; it expires after 60 days
const POST_LIMIT = 12;
const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
const FIELDS = 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$cache = read_json(CACHE_FILE);
if ($cache && time() - (int)$cache['fetched_at'] < CACHE_TTL) {
    respond($cache['payload'], CACHE_TTL - (time() - (int)$cache['fetched_at']));
}

// Only one request rebuilds the cache; concurrent visitors get the previous copy.
@mkdir(PRIVATE_DIR, 0700, true);
$lock = @fopen(LOCK_FILE, 'c');
if ($lock && !flock($lock, LOCK_EX | LOCK_NB)) {
    if ($cache) {
        respond($cache['payload'], 60);
    }
    flock($lock, LOCK_EX);
    $cache = read_json(CACHE_FILE);
    if ($cache && time() - (int)$cache['fetched_at'] < CACHE_TTL) {
        respond($cache['payload'], 60);
    }
}

$state = load_token_state();
if (!$state) {
    fail($cache, 503, 'not_configured');
}
$state = maybe_refresh_token($state);

[$status, $body] = http_get(api_base() . '/me/media?' . http_build_query([
    'fields' => FIELDS,
    'limit' => POST_LIMIT,
    'access_token' => $state['access_token'],
]));
$data = json_decode((string)$body, true);
if ($status !== 200 || !isset($data['data']) || !is_array($data['data'])) {
    error_log('instagram.php: media request failed with HTTP ' . $status);
    fail($cache, 502, 'upstream');
}

$items = [];
foreach ($data['data'] as $post) {
    $item = build_item($post);
    if ($item) {
        $items[] = $item;
    }
}
prune_media(array_column($items, 'id'));

$payload = ['source' => 'live', 'updated' => date(DATE_ATOM), 'items' => $items];
write_json(CACHE_FILE, ['fetched_at' => time(), 'payload' => $payload]);
respond($payload, CACHE_TTL);


function respond(array $payload, int $maxAge): void
{
    header('Cache-Control: public, max-age=' . max(60, min($maxAge, CACHE_TTL)));
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Serve the last good copy if there is one, otherwise an error the front end can fall back from. */
function fail(?array $cache, int $status, string $error): void
{
    if ($cache) {
        respond($cache['payload'], 300);
    }
    http_response_code($status);
    header('Cache-Control: no-store');
    echo json_encode(['error' => $error]);
    exit;
}

function api_base(): string
{
    return rtrim(getenv('CCS_INSTAGRAM_API') ?: 'https://graph.instagram.com', '/');
}

function load_token_state(): ?array
{
    $state = read_json(STATE_FILE);
    if ($state && !empty($state['access_token'])) {
        return $state;
    }
    $token = is_file(TOKEN_TXT) ? trim((string)file_get_contents(TOKEN_TXT)) : '';
    if ($token === '') {
        return null;
    }
    $state = ['access_token' => $token, 'refreshed_at' => time()];
    write_json(STATE_FILE, $state);
    return $state;
}

function maybe_refresh_token(array $state): array
{
    if (time() - (int)($state['refreshed_at'] ?? 0) < REFRESH_EVERY) {
        return $state;
    }
    [$status, $body] = http_get(api_base() . '/refresh_access_token?' . http_build_query([
        'grant_type' => 'ig_refresh_token',
        'access_token' => $state['access_token'],
    ]));
    $data = json_decode((string)$body, true);
    if ($status === 200 && !empty($data['access_token'])) {
        $state = ['access_token' => $data['access_token'], 'refreshed_at' => time()];
        write_json(STATE_FILE, $state);
    } else {
        error_log('instagram.php: token refresh failed with HTTP ' . $status);
    }
    return $state;
}

function build_item(array $post): ?array
{
    $id = (string)($post['id'] ?? '');
    $permalink = (string)($post['permalink'] ?? '');
    $source = (string)($post['thumbnail_url'] ?? $post['media_url'] ?? '');
    if (!ctype_digit($id) || !str_starts_with($permalink, 'https://www.instagram.com/') || $source === '') {
        return null;
    }
    $type = (string)($post['media_type'] ?? 'IMAGE');
    $caption = trim((string)($post['caption'] ?? ''));
    if (mb_strlen($caption) > 160) {
        $caption = rtrim(mb_substr($caption, 0, 157)) . '…';
    }
    return [
        'id' => $id,
        'permalink' => $permalink,
        'image' => store_image($id, $source) ?? $source,
        'type' => in_array($type, ['IMAGE', 'VIDEO', 'CAROUSEL_ALBUM'], true) ? $type : 'IMAGE',
        'caption' => $caption,
        'timestamp' => (string)($post['timestamp'] ?? ''),
    ];
}

/** Keep a local copy of the cover: Instagram CDN links expire after a few days. */
function store_image(string $id, string $url): ?string
{
    $file = MEDIA_DIR . '/' . $id . '.jpg';
    if (is_file($file)) {
        return MEDIA_URL . $id . '.jpg';
    }
    if (!is_dir(MEDIA_DIR) && !@mkdir(MEDIA_DIR, 0755, true)) {
        return null;
    }
    [$status, $body, $type] = http_get($url);
    if ($status !== 200 || !str_starts_with($type, 'image/') || strlen((string)$body) > MAX_IMAGE_BYTES) {
        return null;
    }
    return @file_put_contents($file, $body) ? MEDIA_URL . $id . '.jpg' : null;
}

function prune_media(array $keepIds): void
{
    foreach (glob(MEDIA_DIR . '/*.jpg') ?: [] as $file) {
        if (!in_array(basename($file, '.jpg'), $keepIds, true)) {
            @unlink($file);
        }
    }
}

/** @return array{0:int,1:string|false,2:string} status, body, content type */
function http_get(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'ccs.com.az instagram feed',
    ]);
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $type = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return [$status, $body, $type];
}

function read_json(string $file): ?array
{
    if (!is_file($file)) {
        return null;
    }
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

function write_json(string $file, array $data): void
{
    $tmp = $file . '.' . getmypid() . '.tmp';
    if (@file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) !== false) {
        @rename($tmp, $file);
    }
}
