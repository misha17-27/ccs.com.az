<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/pages.php';

/** Content tables a page shows: [menu label key, admin page, [[query, translated columns], …]] */
const PAGE_ITEMS = [
    'services' => ['nav.services', 'services.php', [
        ['SELECT * FROM services WHERE is_active = 1', ['title', 'lead']],
        ['SELECT p.* FROM service_points p JOIN services s ON s.id = p.service_id WHERE s.is_active = 1', ['title', 'text']],
    ]],
    'faqs' => ['nav.faq', 'faq.php', [
        ['SELECT * FROM faqs WHERE is_active = 1', ['question', 'answer']],
    ]],
    'projects' => ['nav.projects', 'projects.php', [
        ['SELECT * FROM projects WHERE is_active = 1', ['tag', 'text', 'caption']],
    ]],
    'posts' => ['nav.blog', 'blog.php', [
        ['SELECT * FROM posts WHERE is_active = 1', ['title', 'excerpt', 'body']],
    ]],
];

/** Per language [filled, total] for a set of values; optional values count only where the AZ text exists. */
function count_filled(array $counts, array $values, bool $required): array
{
    if (!$required && trim((string)$values['az']) === '') {
        return $counts;
    }
    foreach (array_keys(CONTENT_LANGS) as $lang) {
        $counts[$lang][1]++;
        $counts[$lang][0] += trim((string)$values[$lang]) !== '' ? 1 : 0;
    }
    return $counts;
}

/** Address column of a language: slug is the AZ address, slug_ru and slug_en follow /ru/ and /en/. */
function slug_col(string $lang): string
{
    return $lang === 'az' ? 'slug' : 'slug_' . $lang;
}

/** Path of a page in a language: /haqqimizda, /ru/o-nas … */
function page_path(array $page, string $lang): string
{
    return '/' . ($lang === 'az' ? '' : $lang . '/') . ($page[slug_col($lang)] ?? '');
}

function empty_counts(): array
{
    return array_fill_keys(array_keys(CONTENT_LANGS), [0, 0]);
}

/** Where the page text is edited, with translation progress: [[label, url, counts], …] */
function page_sources(PDO $db, string $key): array
{
    static $texts = null;
    if ($texts === null) {
        $texts = [];
        foreach ($db->query('SELECT * FROM texts') as $row) {
            $texts[$row['key']] = $row;
        }
    }
    $sources = [];
    foreach (PAGES[$key]['groups'] as $group) {
        $counts = empty_counts();
        foreach (TEXT_FIELDS as $field => $def) {
            if ($def[0] === $group) {
                $counts = count_filled($counts, row_ml($texts[$field] ?? [], 'value'), true);
            }
        }
        $sources[] = [tl(TEXT_GROUPS[$group]), 'texts.php?g=' . $group, $counts];
    }
    foreach (PAGES[$key]['items'] as $item) {
        [$label, $url, $queries] = PAGE_ITEMS[$item];
        $counts = empty_counts();
        foreach ($queries as [$sql, $cols]) {
            foreach ($db->query($sql) as $row) {
                foreach ($cols as $col) {
                    $counts = count_filled($counts, row_ml($row, $col), false);
                }
            }
        }
        $sources[] = [t($label), $url, $counts];
    }
    return $sources;
}

function sum_counts(array $sources): array
{
    $sum = empty_counts();
    foreach ($sources as [, , $counts]) {
        foreach ($counts as $lang => [$filled, $total]) {
            $sum[$lang][0] += $filled;
            $sum[$lang][1] += $total;
        }
    }
    return $sum;
}

function seo_counts(array $page): array
{
    $counts = empty_counts();
    $counts = count_filled($counts, row_ml($page, 'seo_title'), true);
    return count_filled($counts, row_ml($page, 'seo_description'), true);
}

/** ok: every language filled; partial: AZ filled, a translation missing; missing: AZ text missing. */
function fill_status(array $counts): string
{
    if ($counts['az'][0] < $counts['az'][1]) {
        return 'missing';
    }
    foreach ($counts as [$filled, $total]) {
        if ($filled < $total) {
            return 'partial';
        }
    }
    return 'ok';
}

function counts_summary(array $counts): string
{
    $parts = [];
    foreach ($counts as $lang => [$filled, $total]) {
        $parts[] = CONTENT_LANGS[$lang] . ' ' . $filled . '/' . $total;
    }
    return implode(' · ', $parts);
}

function status_dot(array $counts): string
{
    $status = fill_status($counts);
    $label = t('pages.status_' . $status) . ' — ' . counts_summary($counts);
    return '<span class="dot dot--' . $status . '" title="' . e($label) . '"></span><span class="sr-only">' . e($label) . '</span>';
}

function lang_chips(array $counts): string
{
    $html = '<span class="chips">';
    foreach ($counts as $lang => [$filled, $total]) {
        $state = $filled >= $total ? 'ok' : ($lang === 'az' ? 'missing' : 'partial');
        $html .= '<b class="chip chip--' . $state . '">' . CONTENT_LANGS[$lang] . ($filled >= $total ? '' : ' ' . $filled . '/' . $total) . '</b>';
    }
    return $html . '</span>';
}

$find = static function (string $key) use ($db): ?array {
    if (!isset(PAGES[$key])) {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM pages WHERE key = ?');
    $stmt->execute([$key]);
    return $stmt->fetch() ?: null;
};

$host = preg_replace('/[^a-z0-9.:-]/i', '', (string)($_SERVER['HTTP_HOST'] ?? '')) ?: 'ccs.com.az';
$page = null;

// ---------- Save ----------
if (is_post()) {
    csrf_check();
    $page = $find(post_str('key', 20));
    if (!$page) {
        flash('error', t('not_found'));
        redirect($self);
    }
    $key = $page['key'];
    $slugs = [];
    foreach (array_keys(CONTENT_LANGS) as $lang) {
        $slugs[$lang] = $key === 'home' ? '' : strtolower(trim(post_str('slug_' . $lang, 80), " /"));
    }
    $values = [
        'name' => post_ml('name', 80),
        'h1' => post_ml('h1', 160),
        'lead' => post_ml('lead', 600),
        'seo_title' => post_ml('seo_title', 200),
        'seo_description' => post_ml('seo_description', 400),
        'keywords' => post_ml('keywords', 400),
    ];

    $errors = [];
    foreach ($key === 'home' ? [] : $slugs as $lang => $slug) {
        // addresses are unique within a language: /ru/uslugi may repeat an AZ address, not another RU one
        $col = slug_col($lang);
        $taken = $db->prepare("SELECT 1 FROM pages WHERE {$col} = ? AND key <> ?");
        $taken->execute([$slug, $key]);
        $shown = ltrim(page_path([$col => $slug], $lang), '/');
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 60) {
            $errors[] = CONTENT_LANGS[$lang] . ': ' . t('error.slug_invalid');
        } elseif (in_array($slug, RESERVED_SLUGS, true)) {
            $errors[] = t('error.slug_reserved', ['slug' => $shown]);
        } elseif ($taken->fetchColumn()) {
            $errors[] = t('error.slug_taken', ['slug' => $shown]);
        }
    }
    if ($values['name']['az'] === '') {
        $errors[] = t('error.name_required');
    }
    try {
        $upload = store_upload('og_image', 'pages');
    } catch (RuntimeException $ex) {
        $errors[] = $ex->getMessage();
        $upload = null;
    }

    if (!$errors) {
        $og = $page['og_image'];
        if ($upload || isset($_POST['og_remove'])) {
            delete_upload($og);
            $og = $upload;
        }
        $sets = [];
        $args = [];
        foreach ($slugs as $lang => $slug) {
            $sets[] = slug_col($lang) . ' = ?';
            $args[] = $slug;
        }
        foreach ($values as $col => $ml) {
            foreach ($ml as $lang => $value) {
                $sets[] = "{$col}_{$lang} = ?";
                $args[] = $value;
            }
        }
        $db->prepare('UPDATE pages SET ' . implode(', ', $sets) . ', og_image = ?, updated_at = ? WHERE key = ?')
            ->execute([...$args, $og, now(), $key]);
        flash('success', t('saved'));
        redirect($self . '?key=' . $key);
    }

    // Show the form again with what was typed
    delete_upload($upload);
    foreach ($errors as $err) {
        flash('error', $err);
    }
    foreach ($slugs as $lang => $slug) {
        $page[slug_col($lang)] = $slug;
    }
    foreach ($values as $col => $ml) {
        foreach ($ml as $lang => $value) {
            $page["{$col}_{$lang}"] = $value;
        }
    }
}

// ---------- Edit form ----------
$key = $page['key'] ?? get_str('key', 20);
if ($key !== '') {
    $page ??= $find($key);
    if (!$page) {
        flash('error', t('not_found'));
        redirect($self);
    }
    $preview = '/' . $page['slug'];
    admin_header(tl(PAGES[$key]), 'pages', $user,
        '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>'
        . '<a class="btn btn--ghost" href="' . e($preview) . '" target="_blank" rel="noopener">' . icon('external') . '<span>' . e(t('open')) . '</span></a>');
    ?>
    <form method="post" enctype="multipart/form-data" class="split">
      <?= csrf_field() ?>
      <input type="hidden" name="key" value="<?= e($key) ?>">
      <div class="stack">
        <section class="card stack">
          <div class="card__head"><h2><?= e(t('pages.main')) ?></h2></div>
          <div class="field">
            <label class="label" for="slug-az"><?= e(t('pages.address')) ?><?= $key === 'home' ? '' : ' <span class="req">*</span>' ?></label>
            <div class="affix-group">
              <?php foreach (CONTENT_LANGS as $lang => $short): ?>
                <div class="affix">
                  <span class="affix__pre"><?= e($host . page_path([], $lang)) ?></span>
                  <?php if ($key === 'home'): ?>
                    <input id="slug-<?= $lang ?>" type="text" value="" disabled aria-label="<?= e(t('pages.address') . ' (' . $short . ')') ?>">
                  <?php else: ?>
                    <input id="slug-<?= $lang ?>" type="text" name="slug_<?= $lang ?>" value="<?= e($page[slug_col($lang)] ?? '') ?>" required maxlength="60"
                      pattern="[a-z0-9]+(-[a-z0-9]+)*" spellcheck="false" autocapitalize="off" autocomplete="off" lang="<?= $lang ?>"
                      aria-label="<?= e(t('pages.address') . ' (' . $short . ')') ?>"<?= $lang === 'az' ? ' data-serp-slug' : '' ?>>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <small class="hint"><?= e(t($key === 'home' ? 'pages.home_slug' : 'pages.slug_hint')) ?></small>
          </div>
          <?= ml_field('name', t('pages.name'), row_ml($page, 'name'), 'line', true) ?>
        </section>
        <section class="card stack">
          <div class="card__head"><h2><?= e(t('pages.heading')) ?></h2></div>
          <?php if ($key === 'home'): ?>
            <p class="muted small"><?= e(t('pages.home_h1')) ?> <a class="inline-link" href="<?= ADMIN_URL ?>/texts.php?g=hero"><?= e(tl(TEXT_GROUPS['hero'])) ?></a></p>
          <?php else: ?>
            <?= ml_field('h1', t('pages.h1'), row_ml($page, 'h1'), 'line', false, 70) ?>
            <small class="hint"><?= e(t('pages.h1_hint')) ?></small>
            <?= ml_field('lead', t('pages.lead'), row_ml($page, 'lead'), 'area') ?>
            <small class="hint"><?= e(t('pages.lead_hint')) ?></small>
          <?php endif; ?>
        </section>
        <section class="card stack">
          <div class="card__head"><h2>SEO</h2></div>
          <p class="muted small"><?= e(t('seo.hint')) ?></p>
          <?= ml_field('seo_title', t('pages.seo_title'), row_ml($page, 'seo_title'), 'line', false, 60) ?>
          <?= ml_field('seo_description', t('pages.seo_description'), row_ml($page, 'seo_description'), 'area', false, 160) ?>
          <?= ml_field('keywords', t('pages.keywords'), row_ml($page, 'keywords'), 'area') ?>
          <small class="hint"><?= e(t('pages.keywords_hint')) ?></small>
          <div class="serp" data-serp>
            <p class="serp__label"><?= e(t('pages.serp')) ?></p>
            <div class="serp__url"><?= e($host) ?><span data-serp-path><?= $page['slug'] !== '' ? ' › ' . e($page['slug']) : '' ?></span></div>
            <div class="serp__title" data-serp-title><?= e($page['seo_title_az'] ?: $page['name_az']) ?></div>
            <div class="serp__desc" data-serp-desc><?= e($page['seo_description_az']) ?></div>
          </div>
        </section>
      </div>
      <aside class="split__side">
        <section class="card stack">
          <div class="card__head"><h2><?= e(t('pages.content')) ?></h2></div>
          <p class="muted small"><?= e(t('pages.content_hint')) ?></p>
          <ul class="sources">
            <?php foreach (page_sources($db, $key) as [$label, $url, $counts]): ?>
              <li>
                <a href="<?= ADMIN_URL . '/' . e($url) ?>"><?= e($label) ?><?= icon('edit') ?></a>
                <?= lang_chips($counts) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </section>
        <section class="card stack">
          <div class="field">
            <span class="label"><?= e(t('pages.og_image')) ?></span>
            <div class="picker">
              <div class="picker__preview picker__preview--wide" id="og-preview"><?php if ($page['og_image']): ?><img src="<?= e($page['og_image']) ?>" alt=""><?php else: ?><?= icon('image') ?><?php endif; ?></div>
            </div>
            <input type="file" name="og_image" accept="image/jpeg,image/png,image/webp" data-preview="og-preview">
            <small class="hint"><?= e(t('pages.og_hint')) ?></small>
            <?php if ($page['og_image']): ?><?= checkbox('og_remove', false, t('pages.og_remove')) ?><?php endif; ?>
          </div>
          <button class="btn btn--dark btn--block" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button>
        </section>
      </aside>
    </form>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$rows = [];
foreach ($db->query('SELECT * FROM pages') as $row) {
    $rows[$row['key']] = $row;
}
admin_header(t('nav.pages'), 'pages', $user);
?>
<section class="card card--flush">
  <div class="card__intro">
    <p><?= e(t('pages.intro')) ?></p>
  </div>
  <div class="table-wrap">
    <table class="table table--pages">
      <thead><tr><th><?= e(t('pages.col_page')) ?></th><th class="hide-sm"><?= e(t('pages.col_address')) ?></th><th><?= e(t('pages.col_text')) ?></th><th>SEO</th><th></th></tr></thead>
      <tbody>
        <?php foreach (PAGES as $key => $def): if (!isset($rows[$key])) continue; $row = $rows[$key]; ?>
          <tr>
            <td>
              <a class="row-link" href="<?= $self ?>?key=<?= e($key) ?>"><?= e(tl($def)) ?></a>
              <span class="slug show-sm">/<?= e($row['slug']) ?></span>
            </td>
            <td class="hide-sm"><span class="slugs"><?php foreach (array_keys(CONTENT_LANGS) as $lang): ?><span class="slug"><?= e(page_path($row, $lang)) ?></span><?php endforeach; ?></span></td>
            <td><?= status_dot(sum_counts(page_sources($db, $key))) ?></td>
            <td><?= status_dot(seo_counts($row)) ?></td>
            <td class="actions">
              <a class="btn btn--ghost btn--sm btn--collapse" href="<?= $self ?>?key=<?= e($key) ?>" title="<?= e(t('edit')) ?>"><?= icon('edit') ?><span><?= e(t('edit')) ?></span></a>
              <a class="btn btn--ghost btn--sm btn--collapse" href="/<?= e($row['slug']) ?>" target="_blank" rel="noopener" title="<?= e(t('open')) ?>"><?= icon('external') ?><span><?= e(t('open')) ?></span></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="legend">
    <span><i class="dot dot--ok"></i><?= e(t('pages.status_ok')) ?></span>
    <span><i class="dot dot--partial"></i><?= e(t('pages.status_partial')) ?></span>
    <span><i class="dot dot--missing"></i><?= e(t('pages.status_missing')) ?></span>
  </div>
</section>
<?php
admin_footer();
