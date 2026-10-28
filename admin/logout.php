<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();

if (is_post()) {
    csrf_check();
    logout_user();
    redirect(ADMIN_URL . '/login.php');
}
redirect(ADMIN_URL . '/');
