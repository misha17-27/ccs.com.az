<?php
/**
 * Paths and constants shared by the admin panel and the API endpoints.
 *
 * On the server: public_html/ holds the site, /home/<user>/ccs-private/ (next to it,
 * not web-accessible) holds the SQLite database, Instagram token and setup code.
 * For local overrides create app/config.local.php (git-ignored), e.g.
 *   <?php define('PRIVATE_DIR', __DIR__ . '/../.private');
 */

declare(strict_types=1);
defined('CCS') || exit;

define('BASE_PATH', dirname(__DIR__)); // public_html

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

defined('PRIVATE_DIR') || define('PRIVATE_DIR', rtrim(getenv('CCS_PRIVATE_DIR') ?: dirname(BASE_PATH) . '/ccs-private', '/\\'));
defined('DB_PATH') || define('DB_PATH', PRIVATE_DIR . '/ccs.sqlite');
defined('UPLOADS_DIR') || define('UPLOADS_DIR', BASE_PATH . '/uploads');

const UPLOADS_URL = '/uploads';
const ADMIN_URL = '/admin';
const SESSION_NAME = 'ccs_admin';
const SESSION_IDLE = 4 * 3600;         // log out after 4 h without activity
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

const CONTENT_LANGS = ['az' => 'AZ', 'ru' => 'RU', 'en' => 'EN'];
const ADMIN_LANGS = ['az', 'ru'];
const ROLES = ['admin', 'editor'];
const REQUEST_STATUSES = ['new', 'in_progress', 'done', 'spam'];
