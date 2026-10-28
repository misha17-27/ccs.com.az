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
          var field = ml.querySelector('[data-ml-pane="' + lang + '"] input, [data-ml-pane="' + lang + '"] textarea');
          if (field) field.focus();
        });
      });
      ml.querySelectorAll('[data-ml-pane] input, [data-ml-pane] textarea').forEach(function (field) {
        field.addEventListener('input', function () {
          var lang = field.closest('[data-ml-pane]').dataset.mlPane;
          var tab = ml.querySelector('[data-ml-tab="' + lang + '"]');
          if (tab) tab.classList.toggle('is-filled', field.value.trim() !== '');
        });
      });
    });
  }
  initMl(document);

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
