<?php
/**
 * SQLite database in PRIVATE_DIR. Tables are created and filled with the current
 * site content on first use; PRAGMA user_version tracks the schema version.
 * Queries stay within SQLite 3.7 features so older shared-hosting builds work.
 */

declare(strict_types=1);
defined('CCS') || exit;

const SCHEMA_VERSION = 4;

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
        if ($version < 2) {
            create_schema_v2($pdo);
            seed_pages($pdo);
        }
        if ($version < 3) {
            refresh_seed_content($pdo);
        }
        if ($version < 4) {
            seed_new_texts($pdo);
        }
        $pdo->exec('PRAGMA user_version = ' . SCHEMA_VERSION);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** ['az' => …, 'ru' => …, 'en' => …] → values in CONTENT_LANGS order, for col_az, col_ru, col_en placeholders. */
function ml_values(array $ml): array
{
    return array_map(static fn($l) => $ml[$l] ?? '', array_keys(CONTENT_LANGS));
}

/** Column definitions col_az, col_ru, col_en; $extra applies to the AZ column only. */
function ml_columns(string $col, string $extra = ''): string
{
    return implode(', ', array_map(static fn($l) => "{$col}_{$l} TEXT" . ($l === 'az' ? $extra : ''), array_keys(CONTENT_LANGS)));
}

function create_schema_v1(PDO $pdo): void
{
    $ml = 'ml_columns';

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

    $service = $pdo->prepare('INSERT INTO services (icon, image, sort, title_az, title_ru, title_en, lead_az, lead_ru, lead_en, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $point = $pdo->prepare('INSERT INTO service_points (service_id, sort, title_az, title_ru, title_en, text_az, text_ru, text_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach (SEED_SERVICES as $i => [$icon, $image, $title, $lead, $points]) {
        $service->execute([$icon, $image, ($i + 1) * 10, ...ml_values($title), ...ml_values($lead), $t, $t]);
        $id = (int)$pdo->lastInsertId();
        foreach ($points as $j => [$pt, $px]) {
            $point->execute([$id, ($j + 1) * 10, ...ml_values($pt), ...ml_values($px)]);
        }
    }

    $partner = $pdo->prepare('INSERT INTO partners (name, logo, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach (SEED_PARTNERS as $i => [$name, $file]) {
        $partner->execute([$name, "/assets/img/clients/{$file}.jpg", ($i + 1) * 10, $t, $t]);
    }

    $faq = $pdo->prepare('INSERT INTO faqs (sort, question_az, question_ru, question_en, answer_az, answer_ru, answer_en, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach (SEED_FAQS as $i => [$q, $a]) {
        $faq->execute([($i + 1) * 10, ...ml_values($q), ...ml_values($a), $t, $t]);
    }

    $text = $pdo->prepare('INSERT INTO texts (key, value_az, value_ru, value_en, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach (TEXT_FIELDS as $key => $def) {
        $text->execute([$key, ...ml_values($def[4]), $t]);
    }

    $setting = $pdo->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
    foreach (SETTING_FIELDS as $key => $def) {
        $setting->execute([$key, $def[4]]);
    }
}

/** v2: pages with their own address and SEO (SEO used to be two site-wide texts). */
function create_schema_v2(PDO $pdo): void
{
    $ml = 'ml_columns';
    $pdo->exec("CREATE TABLE pages (
        key TEXT PRIMARY KEY,
        slug TEXT NOT NULL UNIQUE,
        {$ml('name')},
        {$ml('seo_title')},
        {$ml('seo_description')},
        og_image TEXT,
        updated_at TEXT)");
}

function seed_pages(PDO $pdo): void
{
    $t = now();

    // Texts added after v1 (the FAQ block)
    $text = $pdo->prepare('INSERT OR IGNORE INTO texts (key, value_az, value_ru, value_en, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach (TEXT_FIELDS as $key => $def) {
        $text->execute([$key, ...ml_values($def[4]), $t]);
    }

    // The home page keeps the SEO texts edited before pages existed.
    $old = [];
    foreach ($pdo->query("SELECT * FROM texts WHERE key IN ('seo.title', 'seo.description')") as $row) {
        $old[$row['key']] = [$row['value_az'], $row['value_ru'], $row['value_en']];
    }
    $pdo->exec("DELETE FROM texts WHERE key IN ('seo.title', 'seo.description')");

    $page = $pdo->prepare('INSERT INTO pages (key, slug, name_az, name_ru, name_en, seo_title_az, seo_title_ru, seo_title_en,
        seo_description_az, seo_description_ru, seo_description_en, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach (PAGES as $key => $def) {
        [$name, $title, $description] = SEED_PAGES[$key];
        $title = $key === 'home' && isset($old['seo.title']) ? $old['seo.title'] : ml_values($title);
        $description = $key === 'home' && isset($old['seo.description']) ? $old['seo.description'] : ml_values($description);
        $page->execute([$key, $def['slug'], ...ml_values($name), ...$title, ...$description, $t]);
    }
}

/**
 * v3: the site is built on design V1. Seed content nobody has edited (it still equals a v1/v2 seed,
 * see seed_v2.php) takes the V1 texts and their RU/EN translations; edited content is left alone,
 * and translations are only added where a field is still empty.
 */
function refresh_seed_content(PDO $pdo): void
{
    $old = require __DIR__ . '/seed_v2.php';
    $t = now();
    $langs = array_keys(CONTENT_LANGS);

    // col_az/ru/en values for an untouched field (AZ equals a seed): the new AZ, translations kept unless empty.
    $merge = static function (array $row, string $col, array $new, array $oldAz) use ($langs): ?array {
        if (!in_array((string)$row[$col . '_az'], [$new['az'], ...$oldAz], true)) {
            return null;
        }
        $out = [];
        foreach ($langs as $l) {
            $out[] = $l === 'az' || trim((string)$row["{$col}_{$l}"]) === '' ? $new[$l] : $row["{$col}_{$l}"];
        }
        return $out;
    };
    $keep = static fn(array $row, string $col): array => array_map(static fn($l) => $row["{$col}_{$l}"], $langs);

    $find = $pdo->prepare('SELECT * FROM texts WHERE key = ?');
    $insert = $pdo->prepare('INSERT INTO texts (key, value_az, value_ru, value_en, updated_at) VALUES (?, ?, ?, ?, ?)');
    $update = $pdo->prepare('UPDATE texts SET value_az = ?, value_ru = ?, value_en = ?, updated_at = ? WHERE key = ?');
    foreach (TEXT_FIELDS as $key => $def) {
        $find->execute([$key]);
        $row = $find->fetch();
        if (!$row) {
            $insert->execute([$key, ...ml_values($def[4]), $t]);
        } elseif ($values = $merge($row, 'value', $def[4], array_filter([$old['texts'][$key] ?? null]))) {
            $update->execute([...$values, $t, $key]);
        }
    }

    // Services are matched by their AZ title, points by their AZ text (which did not change).
    $services = $pdo->prepare('SELECT * FROM services WHERE title_az = ?');
    $updateService = $pdo->prepare('UPDATE services SET title_az = ?, title_ru = ?, title_en = ?, lead_az = ?, lead_ru = ?, lead_en = ?, updated_at = ? WHERE id = ?');
    $points = $pdo->prepare('SELECT * FROM service_points WHERE service_id = ?');
    $updatePoint = $pdo->prepare('UPDATE service_points SET title_az = ?, title_ru = ?, title_en = ?, text_az = ?, text_ru = ?, text_en = ? WHERE id = ?');
    foreach (SEED_SERVICES as [, , $title, $lead, $seedPoints]) {
        $oldService = $old['services'][$title['az']] ?? ['lead' => null, 'points' => []];
        $services->execute([$title['az']]);
        foreach ($services->fetchAll() as $service) {
            $updateService->execute([
                ...$merge($service, 'title', $title, []),
                ...($merge($service, 'lead', $lead, array_filter([$oldService['lead']])) ?? $keep($service, 'lead')),
                $t, $service['id'],
            ]);
            $points->execute([$service['id']]);
            foreach ($points->fetchAll() as $point) {
                foreach ($seedPoints as [$pTitle, $pText]) {
                    $titles = $point['text_az'] === $pText['az'] ? $merge($point, 'title', $pTitle, $oldService['points']) : null;
                    if ($titles) {
                        $updatePoint->execute([...$titles, ...$merge($point, 'text', $pText, []), $point['id']]);
                    }
                }
            }
        }
    }

    $faqs = $pdo->prepare('SELECT * FROM faqs WHERE question_az = ?');
    $updateFaq = $pdo->prepare('UPDATE faqs SET question_az = ?, question_ru = ?, question_en = ?, answer_az = ?, answer_ru = ?, answer_en = ?, updated_at = ? WHERE id = ?');
    foreach (SEED_FAQS as [$question, $answer]) {
        $faqs->execute([$question['az']]);
        foreach ($faqs->fetchAll() as $faq) {
            $updateFaq->execute([
                ...$merge($faq, 'question', $question, []),
                ...($merge($faq, 'answer', $answer, []) ?? $keep($faq, 'answer')),
                $t, $faq['id'],
            ]);
        }
    }

    $pages = $pdo->prepare('SELECT * FROM pages WHERE key = ?');
    $updatePage = $pdo->prepare('UPDATE pages SET name_az = ?, name_ru = ?, name_en = ?, seo_title_az = ?, seo_title_ru = ?, seo_title_en = ?,
        seo_description_az = ?, seo_description_ru = ?, seo_description_en = ?, updated_at = ? WHERE key = ?');
    foreach (SEED_PAGES as $key => [$name, $title, $description]) {
        $pages->execute([$key]);
        $page = $pages->fetch();
        if (!$page) {
            continue;
        }
        $oldSeo = $key === 'home' ? $old['home_seo'] : [null, null];
        $updatePage->execute([
            ...($merge($page, 'name', $name, []) ?? $keep($page, 'name')),
            ...($merge($page, 'seo_title', $title, array_filter([$oldSeo[0]])) ?? $keep($page, 'seo_title')),
            ...($merge($page, 'seo_description', $description, array_filter([$oldSeo[1]])) ?? $keep($page, 'seo_description')),
            $t, $key,
        ]);
    }

    $pdo->exec("UPDATE partners SET name = 'Cəviz Tea & Lounge' WHERE name = 'Caviz Tea & Lounge'");
}

/** v4: texts of blocks added later (the TikTok block) get their default values; existing texts are kept. */
function seed_new_texts(PDO $pdo): void
{
    $text = $pdo->prepare('INSERT OR IGNORE INTO texts (key, value_az, value_ru, value_en, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach (TEXT_FIELDS as $key => $def) {
        $text->execute([$key, ...ml_values($def[4]), now()]);
    }
}
