/* Cost Control Services — admin panel behaviour (no inline scripts: the CSP forbids them). */
(function () {
  'use strict';

  // Mobile sidebar
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.querySelector('[data-sidebar-close]');
  function setSidebar(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('is-open', open);
    if (backdrop) backdrop.classList.toggle('is-open', open);
  }
  document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () { setSidebar(!sidebar.classList.contains('is-open')); });
  });
  if (backdrop) backdrop.addEventListener('click', function () { setSidebar(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setSidebar(false); });

  // "42 / 60" counter on multi-language fields with a length limit
  function updateCount(ml) {
    var out = ml.querySelector('[data-ml-count]');
    if (!out) return;
    var field = ml.querySelector('[data-ml-pane]:not([hidden]) input, [data-ml-pane]:not([hidden]) textarea');
    var len = field ? Array.from(field.value.trim()).length : 0;
    var limit = Number(ml.dataset.limit);
    out.textContent = len + ' / ' + limit;
    out.classList.toggle('is-over', len > limit);
  }

  // Language tabs on multi-language fields
  function initMl(root) {
    root.querySelectorAll('[data-ml]').forEach(function (ml) {
      if (ml.dataset.mlReady) return;
      ml.dataset.mlReady = '1';
      ml.querySelectorAll('[data-ml-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
          var lang = tab.dataset.mlTab;
          ml.querySelectorAll('[data-ml-tab]').forEach(function (t) {
            var on = t === tab;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', String(on));
          });
          ml.querySelectorAll('[data-ml-pane]').forEach(function (p) { p.hidden = p.dataset.mlPane !== lang; });
          updateCount(ml);
          var field = ml.querySelector('[data-ml-pane="' + lang + '"] input, [data-ml-pane="' + lang + '"] textarea');
          if (field) field.focus();
        });
      });
      ml.querySelectorAll('[data-ml-pane] input, [data-ml-pane] textarea').forEach(function (field) {
        field.addEventListener('input', function () {
          var lang = field.closest('[data-ml-pane]').dataset.mlPane;
          var tab = ml.querySelector('[data-ml-tab="' + lang + '"]');
          if (tab) tab.classList.toggle('is-filled', field.value.trim() !== '');
          updateCount(ml);
        });
      });
      updateCount(ml);
    });
  }
  initMl(document);

  // Rich text editor of blog posts: a toolbar over an editable area. The hidden textarea keeps the HTML
  // and goes with the form; the server cleans it (clean_html). "Show source" swaps the area for the textarea.
  function initRich(textarea) {
    var labels = JSON.parse(textarea.dataset.rich || '{}');
    var wrap = document.createElement('div');
    var bar = document.createElement('div');
    var area = document.createElement('div');
    wrap.className = 'rte';
    bar.className = 'rte__bar';
    bar.setAttribute('role', 'toolbar');
    area.className = 'rte__area';
    area.contentEditable = 'true';
    area.lang = textarea.lang;
    area.setAttribute('role', 'textbox');
    area.setAttribute('aria-multiline', 'true');
    area.setAttribute('aria-label', textarea.getAttribute('aria-label') || '');
    area.innerHTML = textarea.value;
    wrap.appendChild(bar);
    wrap.appendChild(area);
    textarea.parentNode.insertBefore(wrap, textarea);
    textarea.hidden = true;

    var source = document.createElement('button');
    source.type = 'button';
    source.className = 'btn btn--ghost btn--sm rte__source';
    source.textContent = labels.source;
    textarea.parentNode.insertBefore(source, textarea.nextSibling);

    function sync() {
      var html = area.innerHTML.trim();
      textarea.value = /^(<p>)?(<br>)?(<\/p>)?$/.test(html) ? '' : html;
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    var buttons = [
      { text: 'B', title: labels.bold, cmd: 'bold', cls: 'rte__btn--b' },
      { text: 'I', title: labels.italic, cmd: 'italic', cls: 'rte__btn--i' },
      { text: 'H2', title: labels.h2, block: 'h2' },
      { text: 'H3', title: labels.h3, block: 'h3' },
      { text: '¶', title: labels.p, block: 'p' },
      { text: '• ' + labels.list, title: labels.ul, cmd: 'insertUnorderedList' },
      { text: '1. ' + labels.list, title: labels.ol, cmd: 'insertOrderedList' },
      { text: labels.link, title: labels.link, cmd: 'link' },
      { text: labels.unlink, title: labels.unlink_title, cmd: 'unlink' },
      { text: labels.clear, title: labels.clear, cmd: 'clear' }
    ];

    function blockValue() {
      try { return String(document.queryCommandValue('formatBlock')).toLowerCase(); } catch (e) { return ''; }
    }
    function formatBlock(tag) {
      // browsers differ on "h2" vs "<h2>"
      if (!document.execCommand('formatBlock', false, tag)) document.execCommand('formatBlock', false, '<' + tag + '>');
    }
    function updateState() {
      buttons.forEach(function (b) {
        var on = false;
        try {
          if (b.block) on = blockValue() === b.block;
          else if (b.cmd === 'bold' || b.cmd === 'italic' || b.cmd === 'insertUnorderedList' || b.cmd === 'insertOrderedList') on = document.queryCommandState(b.cmd);
        } catch (e) { on = false; }
        b.el.classList.toggle('is-active', on);
      });
    }
    function run(b) {
      area.focus();
      document.execCommand('styleWithCSS', false, false);
      if (b.block) {
        // a second click on H2 / H3 turns the heading back into a paragraph
        formatBlock(b.block !== 'p' && blockValue() === b.block ? 'p' : b.block);
      } else if (b.cmd === 'link') {
        var url = (window.prompt(labels.link_prompt, 'https://') || '').trim();
        if (url && url !== 'https://') document.execCommand('createLink', false, url);
      } else if (b.cmd === 'clear') {
        document.execCommand('removeFormat');
        document.execCommand('unlink');
        formatBlock('p');
      } else {
        document.execCommand(b.cmd);
        if (b.cmd === 'insertUnorderedList' || b.cmd === 'insertOrderedList') fixLists();
      }
      sync();
      updateState();
    }
    // Chrome may put a new list inside the paragraph it came from: lift it out, keeping the caret in its item
    function fixLists() {
      area.querySelectorAll('p > ul, p > ol').forEach(function (list) {
        var sel = window.getSelection();
        var at = sel.anchorNode;
        var li = at && (at.nodeType === 1 ? at : at.parentNode).closest('li');
        unwrap(list.parentNode);
        if (li && area.contains(li)) {
          var range = document.createRange();
          range.selectNodeContents(li);
          range.collapse(false);
          sel.removeAllRanges();
          sel.addRange(range);
        }
      });
    }

    buttons.forEach(function (b) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'rte__btn' + (b.cls ? ' ' + b.cls : '');
      btn.textContent = b.text;
      btn.title = b.title;
      // keep the selection in the text while clicking a button
      btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
      btn.addEventListener('click', function () { run(b); });
      b.el = btn;
      bar.appendChild(btn);
    });

    area.addEventListener('focus', function () {
      document.execCommand('defaultParagraphSeparator', false, 'p');
      if (area.innerHTML.trim() === '') {
        // typing into an empty editor starts a paragraph
        area.innerHTML = '<p><br></p>';
        var range = document.createRange();
        range.setStart(area.firstChild, 0);
        range.collapse(true);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
      }
    });
    area.addEventListener('input', sync);
    // Text pasted from Word or Google Docs: bold and italic set by styles become tags, then fonts and colours
    // go right away (the server cleans the rest). Docs puts the whole paste in <b style="font-weight:normal">.
    function unwrap(el) {
      while (el.firstChild) el.parentNode.insertBefore(el.firstChild, el);
      el.remove();
    }
    function wrapIn(el, tag) {
      var outer = document.createElement(tag);
      el.parentNode.insertBefore(outer, el);
      outer.appendChild(el);
    }
    area.addEventListener('paste', function () {
      setTimeout(function () {
        area.querySelectorAll('style, meta, script').forEach(function (el) { el.remove(); });
        area.querySelectorAll('b, strong').forEach(function (el) {
          if (/(^|;)\s*font-weight\s*:\s*(normal|[1-4]00)\b/i.test(el.getAttribute('style') || '')) unwrap(el);
        });
        area.querySelectorAll('span[style]').forEach(function (el) {
          var style = el.getAttribute('style');
          if (/(^|;)\s*font-weight\s*:\s*(bold|[6-9]00)\b/i.test(style)) wrapIn(el, 'b');
          if (/(^|;)\s*font-style\s*:\s*italic/i.test(style)) wrapIn(el, 'i');
        });
        area.querySelectorAll('[style], [class], [id], font').forEach(function (el) {
          el.removeAttribute('style');
          el.removeAttribute('class');
          el.removeAttribute('id');
        });
        sync();
      }, 0);
    });
    document.addEventListener('selectionchange', function () {
      if (document.activeElement === area) updateState();
    });

    source.addEventListener('click', function () {
      var showSource = textarea.hidden;
      if (showSource) sync(); else area.innerHTML = textarea.value;
      textarea.hidden = !showSource;
      wrap.hidden = showSource;
      source.textContent = showSource ? labels.editor : labels.source;
      (showSource ? textarea : area).focus();
    });
  }
  document.querySelectorAll('textarea[data-rich]').forEach(initRich);

  // Google snippet preview on the page form: follows the language tab last picked
  var serp = document.querySelector('[data-serp]');
  if (serp) {
    var serpForm = serp.closest('form');
    var serpLang = 'az';
    var serpValue = function (name) {
      var field = serpForm.querySelector('[name="' + name + '[' + serpLang + ']"]');
      return field ? field.value.trim() : '';
    };
    var renderSerp = function () {
      var name = serpValue('name');
      serp.querySelector('[data-serp-title]').textContent = serpValue('seo_title') || (name ? name + ' — Cost Control Services' : '—');
      serp.querySelector('[data-serp-desc]').textContent = serpValue('seo_description') || '—';
      var slug = serpForm.querySelector('[data-serp-slug]');
      if (slug) serp.querySelector('[data-serp-path]').textContent = slug.value.trim() ? ' › ' + slug.value.trim() : '';
    };
    serpForm.addEventListener('input', renderSerp);
    serpForm.addEventListener('click', function (e) {
      var tab = e.target.closest('[data-ml-tab]');
      if (tab) { serpLang = tab.dataset.mlTab; renderSerp(); }
    });
    renderSerp();
  }

  // "Switch all fields to language X" buttons
  document.querySelectorAll('[data-ml-all]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var lang = btn.dataset.mlAll;
      document.querySelectorAll('[data-ml-tab="' + lang + '"]').forEach(function (tab) { tab.click(); });
      btn.parentElement.querySelectorAll('[data-ml-all]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
    });
  });

  // Confirm before destructive actions
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

  // Image preview for file inputs
  document.querySelectorAll('[data-preview]').forEach(function (input) {
    var target = document.getElementById(input.dataset.preview);
    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      if (!file || !target) return;
      var img = target.querySelector('img') || target.appendChild(document.createElement('img'));
      img.alt = '';
      img.src = URL.createObjectURL(file);
      target.textContent = '';
      target.appendChild(img);
    });
  });

  // Picking a ready-made image fills the path field
  document.querySelectorAll('[data-image-choice]').forEach(function (select) {
    var target = document.getElementById(select.dataset.imageChoice);
    select.addEventListener('change', function () {
      if (!target || !select.value) return;
      var img = target.querySelector('img') || document.createElement('img');
      img.alt = '';
      img.src = select.value;
      target.textContent = '';
      target.appendChild(img);
    });
  });

  // Repeater: add / remove service points
  document.querySelectorAll('[data-repeater]').forEach(function (rep) {
    var list = rep.querySelector('[data-repeater-list]');
    var tpl = rep.querySelector('template');
    var counter = list.children.length;
    function renumber() {
      list.querySelectorAll('[data-repeater-num]').forEach(function (el, i) { el.textContent = String(i + 1); });
    }
    rep.querySelector('[data-repeater-add]').addEventListener('click', function () {
      var html = tpl.innerHTML.replace(/__i__/g, 'n' + (counter++));
      var wrap = document.createElement('div');
      wrap.innerHTML = html.trim();
      var item = wrap.firstElementChild;
      list.appendChild(item);
      initMl(item);
      renumber();
      var first = item.querySelector('input, textarea');
      if (first) first.focus();
    });
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-repeater-remove]');
      if (!btn) return;
      btn.closest('.repeater__item').remove();
      renumber();
    });
  });

  // Auto-hide success messages
  document.querySelectorAll('.flash--success').forEach(function (el) {
    setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 400); }, 4000);
  });
})();
