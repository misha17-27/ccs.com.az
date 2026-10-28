/* Cost Control Services — Versiya 2 */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var header = document.querySelector('[data-header]');
  var burger = document.querySelector('.burger');
  var nav = document.getElementById('site-nav');

  /* ---------- Header: scrolled state ---------- */
  function onScroll() {
    header.classList.toggle('is-scrolled', window.scrollY > 8);
  }
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ---------- Mobile menu ---------- */
  function setMenu(open) {
    header.classList.toggle('nav-open', open);
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Menyunu bağla' : 'Menyunu aç');
  }
  burger.addEventListener('click', function () {
    setMenu(!header.classList.contains('nav-open'));
  });
  nav.addEventListener('click', function (e) {
    if (e.target.closest('a')) setMenu(false);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && header.classList.contains('nav-open')) {
      setMenu(false);
      burger.focus();
    }
  });
  document.addEventListener('click', function (e) {
    if (header.classList.contains('nav-open') && !header.contains(e.target)) setMenu(false);
  });
  window.addEventListener('resize', function () {
    if (window.innerWidth > 1020) setMenu(false);
  });

  /* ---------- Language switch (visual only for now) ---------- */
  document.querySelectorAll('.lang__link').forEach(function (link) {
    link.addEventListener('click', function (e) { e.preventDefault(); });
  });

  /* ---------- Active nav link ---------- */
  var navLinks = Array.prototype.slice.call(document.querySelectorAll('.nav__link'));
  if ('IntersectionObserver' in window) {
    var sections = navLinks
      .map(function (a) { return document.querySelector(a.getAttribute('href')); })
      .filter(Boolean);
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        navLinks.forEach(function (a) {
          a.classList.toggle('is-active', a.getAttribute('href') === '#' + entry.target.id);
        });
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    sections.forEach(function (s) { spy.observe(s); });
  }

  /* ---------- Scroll reveal ---------- */
  var revealEls = document.querySelectorAll('[data-reveal]');
  function finishReveal(el) {
    el.removeAttribute('data-reveal');
    el.classList.remove('is-visible');
  }
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealEls.forEach(finishReveal);
  } else {
    var revealer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        revealer.unobserve(el);
        el.addEventListener('transitionend', function handler(e) {
          if (e.target !== el || e.propertyName !== 'opacity') return;
          el.removeEventListener('transitionend', handler);
          finishReveal(el);
        });
        el.classList.add('is-visible');
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealEls.forEach(function (el) { revealer.observe(el); });
  }

  /* ---------- Hero parallax (pointer + scroll) ---------- */
  var stage = document.querySelector('[data-parallax]');
  if (stage && !reduceMotion) {
    var layers = Array.prototype.slice.call(stage.querySelectorAll('[data-depth]'));
    var hero = stage.closest('.hero');
    var finePointer = window.matchMedia('(pointer: fine)').matches;
    var target = { x: 0, y: 0 };
    var current = { x: 0, y: 0 };
    var heroVisible = true;
    var ticking = false;
    var scrollDirty = true;

    function render() {
      current.x += (target.x - current.x) * 0.08;
      current.y += (target.y - current.y) * 0.08;
      var scrollShift = Math.min(window.scrollY, 900);
      layers.forEach(function (layer) {
        var depth = parseFloat(layer.getAttribute('data-depth')) || 1;
        var tx = current.x * depth * 6;
        var ty = current.y * depth * 6 - scrollShift * 0.03 * depth;
        layer.style.transform = 'translate3d(' + tx.toFixed(2) + 'px,' + ty.toFixed(2) + 'px,0)';
      });
      var settled = Math.abs(target.x - current.x) < 0.001 && Math.abs(target.y - current.y) < 0.001;
      var needsFrame = !settled || scrollDirty;
      scrollDirty = false;
      if (needsFrame && heroVisible) {
        requestAnimationFrame(render);
      } else {
        ticking = false;
      }
    }
    function kick() {
      if (!ticking && heroVisible) {
        ticking = true;
        requestAnimationFrame(render);
      }
    }

    if (finePointer) {
      hero.addEventListener('pointermove', function (e) {
        var r = hero.getBoundingClientRect();
        target.x = ((e.clientX - r.left) / r.width - 0.5) * 2;
        target.y = ((e.clientY - r.top) / r.height - 0.5) * 2;
        kick();
      });
      hero.addEventListener('pointerleave', function () {
        target.x = 0;
        target.y = 0;
        kick();
      });
    }
    window.addEventListener('scroll', function () {
      if (!heroVisible) return;
      scrollDirty = true;
      kick();
    }, { passive: true });

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        heroVisible = entries[0].isIntersecting;
        if (heroVisible) kick();
      }).observe(hero);
    }
  }

  /* ---------- FAQ: keep one item open ---------- */
  var accordion = document.querySelector('[data-accordion]');
  if (accordion) {
    var items = accordion.querySelectorAll('details');
    items.forEach(function (d) {
      d.addEventListener('toggle', function () {
        if (!d.open) return;
        items.forEach(function (other) {
          if (other !== d) other.open = false;
        });
      });
    });
  }

  /* ---------- Contact form → /api/contact.php ---------- */
  var form = document.querySelector('[data-form]');
  var success = document.querySelector('[data-form-success]');
  if (form && success) {
    var submitBtn = form.querySelector('[type="submit"]');
    var submitLabel = submitBtn.querySelector('.btn__label');
    var defaultLabel = submitLabel.textContent;
    var errorBox = document.createElement('p');
    errorBox.setAttribute('role', 'alert');
    errorBox.hidden = true;
    errorBox.style.cssText = 'margin:14px 0 0;color:#D93036;font-weight:600;font-size:.92rem';
    errorBox.textContent = 'Göndərmək alınmadı. Bir az sonra yenidən cəhd edin və ya WhatsApp ilə yazın: +994 55 855 61 52.';
    form.appendChild(errorBox);

    var rules = {
      name: function (v) {
        return v.trim().length >= 2 ? '' : 'Zəhmət olmasa, adınızı daxil edin.';
      },
      phone: function (v) {
        return v.replace(/\D/g, '').length >= 9 ? '' : 'Düzgün telefon nömrəsi daxil edin.';
      }
    };

    function setError(input, message) {
      var field = input.closest('.field');
      var error = field.querySelector('.field__error');
      field.classList.toggle('is-invalid', Boolean(message));
      input.setAttribute('aria-invalid', message ? 'true' : 'false');
      if (error) {
        error.textContent = message;
        if (message) input.setAttribute('aria-describedby', error.id);
        else input.removeAttribute('aria-describedby');
      }
    }

    Object.keys(rules).forEach(function (name) {
      var input = form.elements[name];
      input.addEventListener('input', function () {
        if (input.closest('.field').classList.contains('is-invalid')) {
          setError(input, rules[name](input.value));
        }
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var firstInvalid = null;
      Object.keys(rules).forEach(function (name) {
        var input = form.elements[name];
        var message = rules[name](input.value);
        setError(input, message);
        if (message && !firstInvalid) firstInvalid = input;
      });
      if (firstInvalid) {
        firstInvalid.focus();
        return;
      }

      submitBtn.disabled = true;
      submitLabel.textContent = 'Göndərilir…';
      errorBox.hidden = true;
      fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } })
        .then(function (res) {
          return res.json().catch(function () { return {}; }).then(function (data) {
            if (!res.ok || !data.ok) throw new Error(data.error || res.status);
          });
        })
        .then(function () {
          form.hidden = true;
          success.hidden = false;
          success.focus();
        })
        .catch(function () { errorBox.hidden = false; })
        .then(function () {
          submitBtn.disabled = false;
          submitLabel.textContent = defaultLabel;
        });
    });

    success.querySelector('[data-form-reset]').addEventListener('click', function () {
      form.reset();
      success.hidden = true;
      form.hidden = false;
      form.elements.name.focus();
    });
  }
})();
