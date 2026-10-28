<?php
/** Entry point for every admin page and API endpoint. */

declare(strict_types=1);

define('CCS', true);

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/content.php';
require __DIR__ . '/i18n.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/layout.php';

date_default_timezone_set('Asia/Baku');
mb_internal_encoding('UTF-8');
