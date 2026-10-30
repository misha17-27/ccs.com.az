<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
admin_boot();
$user = require_login();
$db = db();
$self = ADMIN_URL . '/blog.php';
$langs = array_keys(CONTENT_LANGS);

/** Address column of a language, as for the pages: slug is AZ, slug_ru and slug_en the others. */
$slugCol = static fn(string $lang): string => $lang === 'az' ? 'slug' : 'slug_' . $lang;

$find = static function (int $id) use ($db): ?array {
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
};

/** The wanted address of a post in a language, or the same with -2, -3 … when another post has it. */
$freeSlug = static function (string $slug, string $lang, int $id) use ($db, $slugCol): string {
    $taken = $db->prepare('SELECT 1 FROM posts WHERE ' . $slugCol($lang) . ' = ? AND id <> ?');
    for ($base = $slug, $n = 2; ; $n++) {
        $taken->execute([$slug, $id]);
        if (!$taken->fetchColumn()) {
            return $slug;
        }
        $slug = $base . '-' . $n;
    }
};

// Address of the blog in each language: /bloq/, /ru/blog/ …
$blogPage = $db->query("SELECT * FROM pages WHERE key = 'blog'")->fetch() ?: [];
$blogPath = static function (string $lang) use ($blogPage, $slugCol): string {
    $slug = trim((string)($blogPage[$slugCol($lang)] ?? '')) ?: (string)($blogPage['slug'] ?? 'bloq');
    return '/' . ($lang === 'az' ? '' : $lang . '/') . $slug . '/';
};

// ---------- Actions ----------
if (is_post()) {
    csrf_check();
    $action = post_str('action', 20);
    $id = post_int('id');

    if ($action === 'visibility') {
        save_setting('blog_visible', isset($_POST['blog_visible']) ? '1' : '0');
        flash('success', t('saved'));
        redirect($self);
    }
    if ($action === 'delete') {
        if ($post = $find($id)) {
            $db->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
            delete_upload($post['cover']);
        }
        flash('success', t('deleted'));
        redirect($self);
    }
    if ($action === 'toggle') {
        $db->prepare('UPDATE posts SET is_active = 1 - is_active, updated_at = ? WHERE id = ?')->execute([now(), $id]);
        redirect($self);
    }
    if ($action === 'save') {
        $post = $id ? $find($id) : null;
        if ($id && !$post) {
            flash('error', t('not_found'));
            redirect($self);
        }
        $values = [
            'title' => post_ml('title', 200),
            'excerpt' => post_ml('excerpt', 600),
            // the editor's HTML, cleaned; text typed in the source view without tags is read as the plain markup
            'body' => array_map(static fn(string $html) => clean_html(post_body_html($html)), post_ml('body', 200000)),
            'seo_title' => post_ml('seo_title', 200),
            'seo_description' => post_ml('seo_description', 400),
        ];
        if ($values['title']['az'] === '') {
            flash('error', t('blog.title_required'));
            redirect($self . ($id ? '?id=' . $id : '?action=new'));
        }
        $date = post_str('published_at', 10);
        $cols = [
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'published_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) ? $date : date('Y-m-d'),
            'updated_at' => now(),
        ];
        // An empty address is made from the title of that language (or the AZ title)
        foreach ($langs as $lang) {
            $slug = slugify(post_str('slug_' . $lang, 120)) ?: slugify($values['title'][$lang] ?: $values['title']['az']) ?: 'post';
            $cols[$slugCol($lang)] = $freeSlug($slug, $lang, $id);
        }
        foreach ($values as $col => $ml) {
            foreach ($ml as $lang => $value) {
                $cols["{$col}_{$lang}"] = $value;
            }
        }

        $oldCover = $post['cover'] ?? null;
        $cols['cover'] = isset($_POST['cover_remove']) ? null : $oldCover;
        try {
            if ($upload = store_upload('cover', 'blog')) {
                shrink_photo($upload);
                $cols['cover'] = $upload;
            }
        } catch (RuntimeException $ex) {
            flash('error', $ex->getMessage());
        }

        if ($id) {
            $set = implode(', ', array_map(static fn($c) => "$c = ?", array_keys($cols)));
            $db->prepare("UPDATE posts SET {$set} WHERE id = ?")->execute([...array_values($cols), $id]);
        } else {
            $cols['created_at'] = now();
            $db->prepare('INSERT INTO posts (' . implode(', ', array_keys($cols)) . ') VALUES (' . rtrim(str_repeat('?, ', count($cols)), ', ') . ')')
                ->execute(array_values($cols));
            $id = (int)$db->lastInsertId();
        }
        if ($oldCover && $oldCover !== $cols['cover']) {
            delete_upload($oldCover);
        }
        flash('success', t('saved'));
        redirect($self . '?id=' . $id);
    }
    redirect($self);
}

// ---------- Form ----------
$id = (int)($_GET['id'] ?? 0);
if ($id || get_str('action', 10) === 'new') {
    $post = ['is_active' => 1, 'published_at' => date('Y-m-d'), 'cover' => null];
    if ($id) {
        $post = $find($id);
        if (!$post) {
            flash('error', t('not_found'));
            redirect($self);
        }
    }
    $host = preg_replace('/[^a-z0-9.:-]/i', '', (string)($_SERVER['HTTP_HOST'] ?? '')) ?: 'ccs.com.az';
    $actions = '<a class="btn btn--ghost" href="' . $self . '">' . icon('back') . '<span>' . e(t('back')) . '</span></a>';
    if ($id && $post['is_active'] && $post['published_at'] <= date('Y-m-d')) {
        $actions .= '<a class="btn btn--ghost" href="' . e($blogPath('az') . $post['slug']) . '" target="_blank" rel="noopener">' . icon('external') . '<span>' . e(t('open')) . '</span></a>';
    }
    admin_header($id ? t('blog.edit') : t('blog.new'), 'blog', $user, $actions);
    ?>
    <form method="post" enctype="multipart/form-data" class="split">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <div class="stack">
        <section class="card stack">
          <?= ml_field('title', t('blog.title'), row_ml($post, 'title'), 'line', true, 70) ?>
          <small class="hint"><?= e(t('blog.title_hint')) ?></small>
          <?= ml_field('excerpt', t('blog.excerpt'), row_ml($post, 'excerpt'), 'area', false, 200) ?>
          <small class="hint"><?= e(t('blog.excerpt_hint')) ?></small>
          <?= ml_field('body', t('blog.body'), array_map('post_body_html', row_ml($post, 'body')), 'rich') ?>
          <small class="hint"><?= e(t('blog.body_hint')) ?></small>
        </section>
        <section class="card stack">
          <div class="card__head"><h2>SEO</h2></div>
          <p class="muted small"><?= e(t('seo.hint')) ?></p>
          <?= ml_field('seo_title', t('pages.seo_title'), row_ml($post, 'seo_title'), 'line', false, 60) ?>
          <?= ml_field('seo_description', t('pages.seo_description'), row_ml($post, 'seo_description'), 'area', false, 160) ?>
          <small class="hint"><?= e(t('blog.seo_hint')) ?></small>
          <div class="field">
            <label class="label" for="slug-az"><?= e(t('pages.address')) ?></label>
            <div class="affix-group">
              <?php foreach (CONTENT_LANGS as $lang => $short): ?>
                <div class="affix">
                  <span class="affix__pre"><?= e($host . $blogPath($lang)) ?></span>
                  <input id="slug-<?= $lang ?>" type="text" name="slug_<?= $lang ?>" value="<?= e($post[$slugCol($lang)] ?? '') ?>" maxlength="80"
                    pattern="[a-z0-9]+(-[a-z0-9]+)*" spellcheck="false" autocapitalize="off" autocomplete="off" lang="<?= $lang ?>"
                    aria-label="<?= e(t('pages.address') . ' (' . $short . ')') ?>">
                </div>
              <?php endforeach; ?>
            </div>
            <small class="hint"><?= e(t('blog.slug_hint')) ?></small>
          </div>
        </section>
      </div>
      <aside class="split__side">
        <section class="card stack">
          <?= checkbox('is_active', (bool)$post['is_active'], t('blog.published')) ?>
          <label class="field">
            <span class="label"><?= e(t('blog.date')) ?></span>
            <input type="date" name="published_at" value="<?= e(substr((string)$post['published_at'], 0, 10)) ?>">
            <small class="hint"><?= e(t('blog.date_hint')) ?></small>
          </label>
          <div class="field">
            <span class="label"><?= e(t('blog.cover')) ?></span>
            <div class="picker">
              <div class="picker__preview picker__preview--wide" id="cover-preview"><?php if ($post['cover']): ?><img src="<?= e($post['cover']) ?>" alt=""><?php else: ?><?= icon('image') ?><?php endif; ?></div>
            </div>
            <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-preview="cover-preview">
            <small class="hint"><?= e(t('blog.cover_hint')) ?></small>
            <?php if ($post['cover']): ?><?= checkbox('cover_remove', false, t('pages.og_remove')) ?><?php endif; ?>
          </div>
          <button class="btn btn--dark btn--block" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button>
        </section>
      </aside>
    </form>
    <?php if ($id): ?>
      <div><?= action_button($self, ['id' => $id, 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger', t('confirm.delete')) ?></div>
    <?php endif; ?>
    <?php
    admin_footer();
    exit;
}

// ---------- List ----------
$rows = $db->query('SELECT * FROM posts ORDER BY published_at DESC, id DESC')->fetchAll();
$visible = setting('blog_visible') === '1';
$today = date('Y-m-d');

admin_header(t('nav.blog'), 'blog', $user,
    '<a class="btn btn--primary" href="' . $self . '?action=new">' . icon('plus') . '<span>' . e(t('blog.new')) . '</span></a>');
?>
<form method="post" class="card stack" style="max-width: 980px">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="visibility">
  <div class="card__head">
    <h2><?= e(t('blog.visibility')) ?></h2>
    <a class="btn btn--ghost btn--sm" href="<?= e($blogPath('az')) ?>" target="_blank" rel="noopener"><?= icon('external') ?><span><?= e(t('blog.open')) ?></span></a>
  </div>
  <p class="status-line"><span class="dot <?= $visible ? 'dot--ok' : '' ?>"></span><?= e(t($visible ? 'blog.visible_on' : 'blog.visible_off')) ?></p>
  <?= checkbox('blog_visible', $visible, t('blog.visible')) ?>
  <small class="hint"><?= e(t('blog.visible_hint')) ?></small>
  <div class="form-actions"><button class="btn btn--dark" type="submit"><?= icon('check') ?><?= e(t('save')) ?></button></div>
</form>

<section class="card card--flush">
  <?php if (!$rows): ?>
    <p class="empty"><?= e(t('blog.none')) ?></p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th></th><th><?= e(t('blog.title')) ?></th><th class="hide-sm"><?= e(t('blog.date')) ?></th><th><?= e(t('field.status')) ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $date = substr((string)$r['published_at'], 0, 10); ?>
          <tr class="<?= $r['is_active'] ? '' : 'is-off' ?>">
            <td><?php if ($r['cover']): ?><img class="thumb" src="<?= e($r['cover']) ?>" alt=""><?php else: ?><?= icon('blog') ?><?php endif; ?></td>
            <td><a class="row-link" href="<?= $self ?>?id=<?= (int)$r['id'] ?>"><?= e($r['title_az']) ?></a>
              <div class="muted small"><?= e(mb_strimwidth(str_replace('*', '', (string)$r['excerpt_az']), 0, 120, '…')) ?></div></td>
            <td class="hide-sm muted"><?= e(format_dt($date, false)) ?><?php if ($r['is_active'] && $date > $today): ?><div class="small"><?= e(t('blog.scheduled')) ?></div><?php endif; ?></td>
            <td><?= action_button($self, ['id' => $r['id'], 'action' => 'toggle'], $r['is_active'] ? t('blog.published') : t('blog.draft'), $r['is_active'] ? 'check' : 'x', 'btn btn--ghost btn--sm') ?></td>
            <td class="actions">
              <a class="btn btn--ghost btn--sm btn--icon" href="<?= $self ?>?id=<?= (int)$r['id'] ?>" title="<?= e(t('edit')) ?>"><?= icon('edit') ?><span><?= e(t('edit')) ?></span></a>
              <?= action_button($self, ['id' => $r['id'], 'action' => 'delete'], t('delete'), 'trash', 'btn btn--danger btn--sm btn--icon', t('confirm.delete')) ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php
admin_footer();
