<?php
/** Admin interface language (AZ/RU), remembered in a cookie. */

declare(strict_types=1);
defined('CCS') || exit;

function admin_lang(): string
{
    static $lang = null;
    if ($lang === null) {
        $cookie = $_COOKIE['ccs_admin_lang'] ?? 'az';
        $lang = in_array($cookie, ADMIN_LANGS, true) ? $cookie : 'az';
    }
    return $lang;
}

function set_admin_lang(string $lang): void
{
    setcookie('ccs_admin_lang', $lang, [
        'expires' => time() + 365 * 86400,
        'path' => ADMIN_URL . '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Translated UI string; {name} placeholders are replaced from $vars. */
function t(string $key, array $vars = []): string
{
    static $dict = [];
    $lang = admin_lang();
    $dict[$lang] ??= require __DIR__ . "/lang/{$lang}.php";
    $dict['az'] ??= require __DIR__ . '/lang/az.php';
    $text = $dict[$lang][$key] ?? $dict['az'][$key] ?? $key;
    foreach ($vars as $name => $value) {
        $text = str_replace('{' . $name . '}', (string)$value, $text);
    }
    return $text;
}

/** Picks the current admin language from an ['az' => …, 'ru' => …] pair. */
function tl(array $labels): string
{
    return $labels[admin_lang()] ?? $labels['az'] ?? '';
}
