<?php
/**
 * SQLite database in PRIVATE_DIR. Tables are created and filled with the current
 * site content on first use; PRAGMA user_version tracks the schema version.
 * Queries stay within SQLite 3.7 features so older shared-hosting builds work.
 */

declare(strict_types=1);
defined('CCS') || exit;

const SCHEMA_VERSION = 1;

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    if (!is_dir(PRIVATE_DIR) && !@mkdir(PRIVATE_DIR, 0700, true)) {
        throw new RuntimeException('Cannot create the private data directory: ' . PRIVATE_DIR);
    }
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $version = (int)$pdo->query('PRAGMA user_version')->fetchColumn();
    if ($version >= SCHEMA_VERSION) {
        return;
    }
    $pdo->beginTransaction();
    try {
        if ($version < 1) {
            create_schema_v1($pdo);
            seed_content($pdo);
        }
        $pdo->exec('PRAGMA user_version = ' . SCHEMA_VERSION);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function create_schema_v1(PDO $pdo): void
{
    $ml = static fn(string $col, string $extra = ''): string =>
        implode(', ', array_map(static fn($l) => "{$col}_{$l} TEXT" . ($l === 'az' ? $extra : ''), array_keys(CONTENT_LANGS)));

    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE COLLATE NOCASE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'editor',
        is_active INTEGER NOT NULL DEFAULT 1,
        last_login_at TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");

    $pdo->exec("CREATE TABLE login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT NOT NULL,
        email TEXT,
        success INTEGER NOT NULL,
        created_at TEXT NOT NULL)");
    $pdo->exec('CREATE INDEX idx_login_attempts_ip ON login_attempts (ip, created_at)');

    $pdo->exec("CREATE TABLE requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        company TEXT,
        service TEXT,
        message TEXT,
        source TEXT,
        ip TEXT,
        user_agent TEXT,
        status TEXT NOT NULL DEFAULT 'new',
        note TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");
    $pdo->exec('CREATE INDEX idx_requests_created ON requests (created_at)');
    $pdo->exec('CREATE INDEX idx_requests_status ON requests (status)');

    $pdo->exec("CREATE TABLE services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        icon TEXT,
        image TEXT,
        sort INTEGER NOT NULL DEFAULT 0,
        is_active INTEGER NOT NULL DEFAULT 1,
        {$ml('title', ' NOT NULL')},
        {$ml('lead')},
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");

    $pdo->exec("CREATE TABLE service_points (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        service_id INTEGER NOT NULL REFERENCES services (id) ON DELETE CASCADE,
        sort INTEGER NOT NULL DEFAULT 0,
        {$ml('title')},
        {$ml('text')})");

    $pdo->exec("CREATE TABLE partners (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        logo TEXT,
        url TEXT,
        sort INTEGER NOT NULL DEFAULT 0,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");

    $pdo->exec("CREATE TABLE faqs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sort INTEGER NOT NULL DEFAULT 0,
        is_active INTEGER NOT NULL DEFAULT 1,
        {$ml('question', ' NOT NULL')},
        {$ml('answer')},
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");

    $pdo->exec("CREATE TABLE texts (
        key TEXT PRIMARY KEY,
        {$ml('value')},
        updated_at TEXT)");

    $pdo->exec('CREATE TABLE settings (key TEXT PRIMARY KEY, value TEXT)');
}

function seed_content(PDO $pdo): void
{
    $t = now();

    $service = $pdo->prepare('INSERT INTO services (icon, image, sort, title_az, lead_az, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $point = $pdo->prepare('INSERT INTO service_points (service_id, sort, title_az, text_az) VALUES (?, ?, ?, ?)');
    foreach (SEED_SERVICES as $i => [$icon, $image, $title, $lead, $points]) {
        $service->execute([$icon, $image, ($i + 1) * 10, $title, $lead, $t, $t]);
        $id = (int)$pdo->lastInsertId();
        foreach ($points as $j => [$pt, $px]) {
            $point->execute([$id, ($j + 1) * 10, $pt, $px]);
        }
    }

    $partner = $pdo->prepare('INSERT INTO partners (name, logo, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach (SEED_PARTNERS as $i => [$name, $file]) {
        $partner->execute([$name, "/assets/img/clients/{$file}.jpg", ($i + 1) * 10, $t, $t]);
    }

    $faq = $pdo->prepare('INSERT INTO faqs (sort, question_az, answer_az, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach (SEED_FAQS as $i => [$q, $a]) {
        $faq->execute([($i + 1) * 10, $q, $a, $t, $t]);
    }

    $text = $pdo->prepare('INSERT INTO texts (key, value_az, updated_at) VALUES (?, ?, ?)');
    foreach (TEXT_FIELDS as $key => $def) {
        $text->execute([$key, $def[4], $t]);
    }

    $setting = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
    foreach (SETTING_FIELDS as $key => $def) {
        $setting->execute([$key, $def[4]]);
    }
}
