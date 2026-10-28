/* Instagram feed: fills every [data-ig-feed] grid with the latest posts.
   Live posts come from /api/instagram.php; when it is unavailable (no token yet,
   static preview without PHP) the bundled snapshot in /assets/data/ is shown. */
(function () {
  'use strict';

  var grids = document.querySelectorAll('[data-ig-feed]');
  if (!grids.length) return;

  var SOURCES = ['/api/instagram.php', '/assets/data/instagram.json'];
  var MONTHS = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avqust', 'sentyabr', 'oktyabr', 'noyabr', 'dekabr'];
  var KIND = { VIDEO: 'Video', CAROUSEL_ALBUM: 'Albom', IMAGE: 'Foto' };
  var SVG = 'http://www.w3.org/2000/svg';
  // Static, trusted markup only.
  var ICONS = {
    VIDEO: '<path fill="currentColor" d="M8 5.5v13l10.5-6.5z"/>',
    CAROUSEL_ALBUM: '<path fill="currentColor" d="M7 3h11a3 3 0 0 1 3 3v11h-2V6a1 1 0 0 0-1-1H7zM3 9a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
    INSTAGRAM: '<g fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/></g><circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"/>'
  };

  function icon(name, className) {
    var svg = document.createElementNS(SVG, 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('class', className);
    svg.innerHTML = ICONS[name];
    return svg;
  }

  function formatDate(iso) {
    var d = new Date(iso);
    if (isNaN(d)) return '';
    return d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear();
  }

  function safeUrl(url, prefixes) {
    url = String(url || '');
    return prefixes.some(function (p) { return url.indexOf(p) === 0; }) ? url : '';
  }

  function tile(post) {
    var link = safeUrl(post.permalink, ['https://www.instagram.com/']);
    var image = safeUrl(post.image, ['/', 'https://']);
    if (!link || !image) return null;

    var type = KIND[post.type] ? post.type : 'IMAGE';
    var date = formatDate(post.timestamp);
    var a = document.createElement('a');
    a.className = 'ig-feed__item';
    a.href = link;
    a.target = '_blank';
    a.rel = 'noopener';

    var img = document.createElement('img');
    img.src = image;
    img.alt = post.caption || ('Instagram · ' + KIND[type] + (date ? ', ' + date : ''));
    img.loading = 'lazy';
    img.decoding = 'async';
    img.width = 360;
    img.height = 450;
    img.addEventListener('load', function () { a.classList.add('is-loaded'); });
    a.appendChild(img);

    if (type !== 'IMAGE') a.appendChild(icon(type, 'ig-feed__badge'));
    var overlay = document.createElement('span');
    overlay.className = 'ig-feed__overlay';
    overlay.appendChild(icon('INSTAGRAM', 'ig-feed__glyph'));
    if (date) {
      var time = document.createElement('time');
      time.dateTime = post.timestamp;
      time.textContent = date;
      overlay.appendChild(time);
    }
    a.appendChild(overlay);
    return a;
  }

  function skeleton(grid, count) {
    grid.setAttribute('aria-busy', 'true');
    for (var i = 0; i < count; i++) {
      var s = document.createElement('span');
      s.className = 'ig-feed__item ig-feed__item--skeleton';
      s.setAttribute('aria-hidden', 'true');
      grid.appendChild(s);
    }
  }

  function load(i) {
    if (i >= SOURCES.length) return Promise.reject(new Error('no feed'));
    return fetch(SOURCES[i], { headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (data) {
        if (!data || !Array.isArray(data.items) || !data.items.length) throw new Error('empty');
        return data;
      })
      .catch(function () { return load(i + 1); });
  }

  grids.forEach(function (grid) {
    skeleton(grid, parseInt(grid.getAttribute('data-ig-limit'), 10) || 12);
  });

  load(0).then(function (data) {
    grids.forEach(function (grid) {
      var limit = parseInt(grid.getAttribute('data-ig-limit'), 10) || 12;
      var frag = document.createDocumentFragment();
      data.items.slice(0, limit).forEach(function (post) {
        var el = tile(post);
        if (el) frag.appendChild(el);
      });
      grid.textContent = '';
      grid.appendChild(frag);
      grid.removeAttribute('aria-busy');
    });
  }).catch(function () {
    grids.forEach(function (grid) {
      var section = grid.closest('[data-ig-section]');
      (section || grid).hidden = true;
    });
  });
})();
