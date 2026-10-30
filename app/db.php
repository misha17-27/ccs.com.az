<?php
/**
 * SQLite database in PRIVATE_DIR. Tables are created and filled with the current
 * site content on first use; PRAGMA user_version tracks the schema version.
 * Queries stay within SQLite 3.7 features so older shared-hosting builds work.
 */

declare(strict_types=1);
defined('CCS') || exit;

const SCHEMA_VERSION = 8;

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
        if ($version < 5) {
            create_schema_v5($pdo);
            seed_projects($pdo);
        }
        if ($version < 6) {
            create_schema_v6($pdo);
            refresh_audit_content($pdo);
        }
        if ($version < 7) {
            create_schema_v7($pdo);
        }
        if ($version < 8) {
            create_schema_v8($pdo);
            seed_blog($pdo);
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

/** v5: projects of the experience block (Admin → Projects) with up to four photos each. */
function create_schema_v5(PDO $pdo): void
{
    $ml = 'ml_columns';
    $pdo->exec("CREATE TABLE projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sort INTEGER NOT NULL DEFAULT 0,
        is_active INTEGER NOT NULL DEFAULT 1,
        {$ml('tag')},
        {$ml('text', ' NOT NULL')},
        {$ml('caption')},
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");

    $pdo->exec("CREATE TABLE project_photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        path TEXT NOT NULL,
        focus TEXT NOT NULL DEFAULT 'center',
        sort INTEGER NOT NULL DEFAULT 0)");
}

/** The two paragraphs of the experience block become its first projects; texts edited in the admin are kept. */
function seed_projects(PDO $pdo): void
{
    $t = now();
    $old = [];
    foreach ($pdo->query("SELECT * FROM texts WHERE key IN ('experience.tag1', 'experience.p1', 'experience.tag2', 'experience.p2')") as $row) {
        $old[$row['key']] = ['az' => (string)$row['value_az'], 'ru' => (string)$row['value_ru'], 'en' => (string)$row['value_en']];
    }

    $project = $pdo->prepare('INSERT INTO projects (sort, tag_az, tag_ru, tag_en, text_az, text_ru, text_en, caption_az, caption_ru, caption_en,
        created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $photo = $pdo->prepare('INSERT INTO project_photos (project_id, path, focus, sort) VALUES (?, ?, ?, ?)');
    foreach (SEED_PROJECTS as $i => [$tag, $text, $caption, $photos]) {
        $n = $i + 1;
        $tag = $old["experience.tag{$n}"] ?? $tag;
        $text = $old["experience.p{$n}"] ?? $text;
        if (trim($text['az']) === '') {
            continue;   // the paragraph was emptied in the admin
        }
        $project->execute([$n * 10, ...ml_values($tag), ...ml_values($text), ...ml_values($caption), $t, $t]);
        $id = (int)$pdo->lastInsertId();
        foreach ($photos as $j => [$file, $focus]) {
            $photo->execute([$id, '/assets/img/experience/' . $file, $focus, ($j + 1) * 10]);
        }
    }
    $pdo->exec("DELETE FROM texts WHERE key IN ('experience.tag1', 'experience.p1', 'experience.tag2', 'experience.p2')");
}

/** v6: a page gets its own title (H1), text under the title and meta keywords. */
function create_schema_v6(PDO $pdo): void
{
    foreach (['h1', 'lead', 'keywords'] as $col) {
        foreach (array_keys(CONTENT_LANGS) as $lang) {
            $pdo->exec("ALTER TABLE pages ADD COLUMN {$col}_{$lang} TEXT");
        }
    }
}

/**
 * v6: texts and page SEO aimed at "restaurant audit". A value that still equals its v5 default (seed_v5.php)
 * takes the new default, each language on its own; edited values are kept. Pages get their H1 and keywords,
 * and the FAQ gets the questions about the audit.
 */
function refresh_audit_content(PDO $pdo): void
{
    $old = require __DIR__ . '/seed_v5.php';
    $t = now();
    $merge = static function (array $row, string $col, array $new, array $oldValues): array {
        return array_map(static function (string $lang) use ($row, $col, $new, $oldValues) {
            $value = (string)($row["{$col}_{$lang}"] ?? '');
            return $value === $oldValues[$lang] ? $new[$lang] : $value;
        }, array_keys(CONTENT_LANGS));
    };

    $find = $pdo->prepare('SELECT * FROM texts WHERE key = ?');
    $update = $pdo->prepare('UPDATE texts SET value_az = ?, value_ru = ?, value_en = ?, updated_at = ? WHERE key = ?');
    foreach ($old['texts'] as $key => $oldValues) {
        $find->execute([$key]);
        if ($row = $find->fetch()) {
            $update->execute([...$merge($row, 'value', TEXT_FIELDS[$key][4], $oldValues), $t, $key]);
        }
    }

    $pages = $pdo->prepare('SELECT * FROM pages WHERE key = ?');
    $updatePage = $pdo->prepare('UPDATE pages SET seo_title_az = ?, seo_title_ru = ?, seo_title_en = ?,
        seo_description_az = ?, seo_description_ru = ?, seo_description_en = ?,
        h1_az = ?, h1_ru = ?, h1_en = ?, keywords_az = ?, keywords_ru = ?, keywords_en = ?, updated_at = ? WHERE key = ?');
    foreach (SEED_PAGES as $key => [, $title, $description, $h1, $keywords]) {
        $pages->execute([$key]);
        // pages added after v5 (the blog) get their H1 and keywords in a later step
        if (!isset($old['pages'][$key]) || !$page = $pages->fetch()) {
            continue;
        }
        [$oldTitle, $oldDescription] = $old['pages'][$key];
        $updatePage->execute([
            ...$merge($page, 'seo_title', $title, $oldTitle),
            ...$merge($page, 'seo_description', $description, $oldDescription),
            ...ml_values($h1), ...ml_values($keywords), $t, $key,
        ]);
    }

    // New seed questions go first, before the questions the site already has.
    $new = array_values(array_filter(SEED_FAQS, static fn($faq) => !in_array($faq[0]['az'], $old['faqs'], true)));
    $exists = $pdo->prepare('SELECT 1 FROM faqs WHERE question_az = ?');
    $faq = $pdo->prepare('INSERT INTO faqs (sort, question_az, question_ru, question_en, answer_az, answer_ru, answer_en, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $first = (int)$pdo->query('SELECT MIN(sort) FROM faqs')->fetchColumn();
    foreach ($new as $i => [$question, $answer]) {
        $exists->execute([$question['az']]);
        if (!$exists->fetchColumn()) {
            $faq->execute([$first - count($new) + $i, ...ml_values($question), ...ml_values($answer), $t, $t]);
        }
    }
}

/**
 * v7: pages get their own address in RU and EN (/ru/o-nas, /en/about-us); the column slug stays the AZ address.
 * Until now RU and EN used the AZ address; index.php redirects those links to the new ones.
 */
function create_schema_v7(PDO $pdo): void
{
    $langs = array_diff(array_keys(CONTENT_LANGS), ['az']);
    foreach ($langs as $lang) {
        $pdo->exec("ALTER TABLE pages ADD COLUMN slug_{$lang} TEXT");
    }
    $update = $pdo->prepare('UPDATE pages SET ' . implode(', ', array_map(static fn($l) => "slug_{$l} = ?", $langs)) . ' WHERE key = ?');
    foreach (PAGES as $key => $def) {
        $update->execute([...array_map(static fn($l) => $def["slug_{$l}"], $langs), $key]);
    }
}

/** v8: blog posts (Admin → Blog), each with its own address per language like the pages. */
function create_schema_v8(PDO $pdo): void
{
    $ml = 'ml_columns';
    $slugs = implode(', ', array_map(static fn($l) => "slug_{$l} TEXT", array_diff(array_keys(CONTENT_LANGS), ['az'])));
    $pdo->exec("CREATE TABLE posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        {$slugs},
        is_active INTEGER NOT NULL DEFAULT 1,
        published_at TEXT NOT NULL,
        cover TEXT,
        {$ml('title', ' NOT NULL')},
        {$ml('excerpt')},
        {$ml('body')},
        {$ml('seo_title')},
        {$ml('seo_description')},
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL)");
    $pdo->exec('CREATE INDEX idx_posts_published ON posts (is_active, published_at)');
}

/** The blog page, hidden from the site until Admin → Blog shows it. On a fresh install the page exists already. */
function seed_blog(PDO $pdo): void
{
    $def = PAGES['blog'];
    [$name, $title, $description, $h1, $keywords] = SEED_PAGES['blog'];
    $pdo->prepare('INSERT OR IGNORE INTO pages (key, slug, slug_ru, slug_en, name_az, name_ru, name_en, seo_title_az, seo_title_ru, seo_title_en,
        seo_description_az, seo_description_ru, seo_description_en, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute(['blog', $def['slug'], $def['slug_ru'], $def['slug_en'], ...ml_values($name), ...ml_values($title), ...ml_values($description), now()]);
    $pdo->prepare("UPDATE pages SET h1_az = ?, h1_ru = ?, h1_en = ?, keywords_az = ?, keywords_ru = ?, keywords_en = ?
        WHERE key = 'blog' AND COALESCE(h1_az, '') = ''")->execute([...ml_values($h1), ...ml_values($keywords)]);
    $pdo->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('blog_visible', '0')");
}
