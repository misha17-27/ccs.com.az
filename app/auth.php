<?php
/** Admin sessions, login throttling, roles and Cloudflare Turnstile. */

declare(strict_types=1);
defined('CCS') || exit;

const LOGIN_WINDOW = 900;          // seconds
const LOGIN_MAX_PER_IP = 10;       // failed attempts per IP within the window
const LOGIN_MAX_PER_EMAIL = 5;     // failed attempts per e-mail within the window

/** Headers, session and language switch for every admin page. */
function admin_boot(): void
{
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store, private');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob: https:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; "
        . "script-src 'self' https://challenges.cloudflare.com; frame-src https://challenges.cloudflare.com; "
        . "connect-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");

    admin_session_start();

    $lang = $_GET['lang'] ?? null;
    if (is_string($lang) && in_array($lang, ADMIN_LANGS, true)) {
        set_admin_lang($lang);
        $query = $_GET;
        unset($query['lang']);
        redirect(strtok($_SERVER['REQUEST_URI'], '?') . ($query ? '?' . http_build_query($query) : ''));
    }
}

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => ADMIN_URL . '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();

    if (isset($_SESSION['seen']) && time() - $_SESSION['seen'] > SESSION_IDLE) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['seen'] = time();
}

function users_exist(): bool
{
    return (bool)db()->query('SELECT 1 FROM users LIMIT 1')->fetchColumn();
}

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['uid'])) {
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
            $stmt->execute([$_SESSION['uid']]);
            $user = $stmt->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if ($user) {
        return $user;
    }
    if (!users_exist()) {
        redirect(ADMIN_URL . '/setup.php');
    }
    $next = $_SERVER['REQUEST_URI'] ?? ADMIN_URL . '/';
    redirect(ADMIN_URL . '/login.php' . ($next !== ADMIN_URL . '/' ? '?next=' . rawurlencode($next) : ''));
}

function is_admin(array $user): bool
{
    return $user['role'] === 'admin';
}

function require_admin(array $user): void
{
    if (!is_admin($user)) {
        http_response_code(403);
        admin_header(t('error.forbidden'), '', $user);
        echo '<div class="card empty">' . e(t('error.forbidden_text')) . '</div>';
        admin_footer();
        exit;
    }
}

function login_blocked(string $ip, string $email): bool
{
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW);
    $byIp = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND ip = ? AND created_at > ?');
    $byIp->execute([$ip, $since]);
    $byEmail = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE success = 0 AND email = ? AND created_at > ?');
    $byEmail->execute([mb_strtolower($email), $since]);
    return $byIp->fetchColumn() >= LOGIN_MAX_PER_IP || $byEmail->fetchColumn() >= LOGIN_MAX_PER_EMAIL;
}

function record_login_attempt(string $ip, string $email, bool $success): void
{
    db()->prepare('INSERT INTO login_attempts (ip, email, success, created_at) VALUES (?, ?, ?, ?)')
        ->execute([$ip, mb_strtolower($email), (int)$success, now()]);
    if (random_int(1, 50) === 1) {
        db()->prepare('DELETE FROM login_attempts WHERE created_at < ?')->execute([date('Y-m-d H:i:s', time() - 30 * 86400)]);
    }
}

/** Returns the user on valid credentials, null otherwise (timing kept similar either way). */
function check_credentials(string $email, string $password): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch() ?: null;
    // Unknown e-mail: verify against a real hash of a random string so the response time is the same.
    $hash = $user['password_hash'] ?? '$2y$10$Ajqw4AFgDJjPggMXMFJYsuSbS9FMsFhZUka/zB1zujriTUfkup.V6';
    $ok = password_verify($password, $hash);
    if ($ok && $user && password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }
    return $ok ? $user : null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION = ['uid' => (int)$user['id'], 'seen' => time()];
    db()->prepare('UPDATE users SET last_login_at = ? WHERE id = ?')->execute([now(), $user['id']]);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

function password_problem(string $password, string $confirm): ?string
{
    if (mb_strlen($password) < 10) {
        return t('password.too_short');
    }
    if ($password !== $confirm) {
        return t('password.mismatch');
    }
    return null;
}

function turnstile_enabled(): bool
{
    return setting('turnstile_site_key') !== '' && setting('turnstile_secret_key') !== '';
}

function turnstile_verify(string $token): bool
{
    if ($token === '') {
        return false;
    }
    $result = http_post_form('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'secret' => setting('turnstile_secret_key'),
        'response' => $token,
        'remoteip' => client_ip(),
    ]);
    return (bool)($result['success'] ?? false);
}
