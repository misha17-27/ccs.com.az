(() => {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const desktop = window.matchMedia('(min-width: 960px)');
  const hasIO = 'IntersectionObserver' in window;
  const locale = { az: 'az-AZ', ru: 'ru-RU', en: 'en-US' }[document.documentElement.lang] || 'az-AZ';

  /* ---------- Header: scrolled state ---------- */
  const header = document.querySelector('[data-header]');
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 24);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ---------- Mobile menu ---------- */
  const burger = document.querySelector('[data-burger]');
  const nav = document.getElementById('site-nav');

  const setMenu = (open) => {
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? burger.dataset.labelClose : burger.dataset.labelOpen);
    document.body.classList.toggle('menu-open', open);
  };

  burger.addEventListener('click', () => setMenu(burger.getAttribute('aria-expanded') !== 'true'));
  nav.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && document.body.classList.contains('menu-open')) {
      setMenu(false);
      burger.focus();
    }
  });
  desktop.addEventListener('change', (e) => { if (e.matches) setMenu(false); });

  /* ---------- Scroll reveal ---------- */
  const revealEls = document.querySelectorAll('[data-reveal]');
  if (reduceMotion || !hasIO) {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  } else {
    const revealObserver = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
    revealEls.forEach((el) => revealObserver.observe(el));
  }

  /* ---------- Count-up numbers ---------- */
  const counters = document.querySelectorAll('[data-count]');
  const runCounter = (el) => {
    const target = Number(el.dataset.count);
    const duration = 1400;
    const start = performance.now();
    const tick = (now) => {
      const p = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = String(Math.round(target * eased));
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  if (!reduceMotion && hasIO) {
    const counterObserver = new IntersectionObserver((entries, obs) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        runCounter(entry.target);
        obs.unobserve(entry.target);
      });
    }, { threshold: 0.6 });
    counters.forEach((el) => {
      el.textContent = '0';
      counterObserver.observe(el);
    });
  }

  /* ---------- Services: tabs on desktop, accordion on mobile ---------- */
  const svc = document.querySelector('[data-svc]');
  if (svc) {
    const tabs = [...svc.querySelectorAll('.svc__tab')];
    const panelOf = (tab) => document.getElementById(tab.getAttribute('aria-controls'));

    const setOpen = (tab, open) => {
      tab.setAttribute('aria-expanded', String(open));
      panelOf(tab).classList.toggle('is-open', open);
    };

    const activate = (tab) => tabs.forEach((t) => setOpen(t, t === tab));

    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => {
        const isOpen = tab.getAttribute('aria-expanded') === 'true';
        if (desktop.matches) {
          if (!isOpen) activate(tab);
        } else if (isOpen) {
          setOpen(tab, false);
        } else {
          activate(tab);
          // keep the opened item in view on small screens
          requestAnimationFrame(() => {
            const top = tab.getBoundingClientRect().top;
            if (top < 70 || top > window.innerHeight * 0.6) {
              window.scrollBy({ top: top - 90, behavior: reduceMotion ? 'auto' : 'smooth' });
            }
          });
        }
      });

      tab.addEventListener('keydown', (e) => {
        if (!desktop.matches) return;
        let next = null;
        if (e.key === 'ArrowDown') next = tabs[(i + 1) % tabs.length];
        if (e.key === 'ArrowUp') next = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') next = tabs[0];
        if (e.key === 'End') next = tabs[tabs.length - 1];
        if (next) {
          e.preventDefault();
          next.focus();
          activate(next);
        }
      });
    });

    // desktop always shows one panel
    desktop.addEventListener('change', (e) => {
      if (e.matches && !tabs.some((t) => t.getAttribute('aria-expanded') === 'true')) activate(tabs[0]);
    });

    // Images of hidden panels wait for their tab, so they are fetched as the block comes near
    // the screen: a click then shows the picture at once.
    const loadImages = () => svc.querySelectorAll('[data-svc-img]').forEach((img) => { img.loading = 'eager'; });
    if (hasIO) {
      new IntersectionObserver((entries, obs) => {
        if (entries.some((e) => e.isIntersecting)) {
          loadImages();
          obs.disconnect();
        }
      }, { rootMargin: '600px 0px' }).observe(svc);
    } else {
      loadImages();
    }

    // footer links (/xidmetler#svc-tab-3) open that service
    const fromHash = () => {
      const tab = tabs.find((t) => '#' + t.id === window.location.hash);
      if (tab) activate(tab);
    };
    fromHash();
    window.addEventListener('hashchange', fromHash);
  }

  /* ---------- Experience: a click on a project shows its photos ---------- */
  const exp = document.querySelector('[data-exp]');
  if (exp) {
    const expTabs = [...exp.querySelectorAll('[data-exp-tab]')];
    const galleryOf = (tab) => document.getElementById(tab.getAttribute('aria-controls'));

    expTabs.forEach((tab) => tab.addEventListener('click', () => {
      if (tab.getAttribute('aria-pressed') === 'true') return;
      expTabs.forEach((t) => {
        t.setAttribute('aria-pressed', String(t === tab));
        galleryOf(t).hidden = t !== tab;
      });
      // on phones the gallery is under the list: bring it into view
      const rect = galleryOf(tab).getBoundingClientRect();
      if (rect.top > window.innerHeight - 120 || rect.bottom < 80) {
        galleryOf(tab).scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
      }
    }));

    // photos of the other projects are fetched as the block comes near, so a click shows them at once
    const loadPhotos = () => exp.querySelectorAll('[data-exp-img]').forEach((img) => { img.loading = 'eager'; });
    if (hasIO && expTabs.length) {
      new IntersectionObserver((entries, obs) => {
        if (entries.some((e) => e.isIntersecting)) {
          loadPhotos();
          obs.disconnect();
        }
      }, { rootMargin: '600px 0px' }).observe(exp);
    } else if (expTabs.length) {
      loadPhotos();
    }
  }

  /* ---------- "Apply for this service" links preselect the form ---------- */
  const serviceSelect = document.getElementById('f-service');
  document.querySelectorAll('[data-service]').forEach((link) => {
    link.addEventListener('click', () => {
      if (serviceSelect && link.dataset.service) serviceSelect.value = link.dataset.service;
    });
  });

  /* ---------- Clients marquee: duplicate tracks for a seamless loop ---------- */
  if (!reduceMotion) {
    document.querySelectorAll('.marquee__track').forEach((track) => {
      for (let i = 0; i < 2; i += 1) {
        const clone = track.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('img').forEach((img) => { img.alt = ''; img.removeAttribute('title'); });
        clone.querySelectorAll('a').forEach((a) => { a.tabIndex = -1; });
        track.parentElement.appendChild(clone);
      }
    });
  }

  /* ---------- Food cost calculator ---------- */
  const calc = document.querySelector('[data-calc]');
  if (calc) {
    const t = JSON.parse(calc.dataset.i18n);
    const inCost = calc.elements.cost;
    const inPrice = calc.elements.price;
    const inTarget = calc.elements.target;
    const inRange = calc.querySelector('[data-calc-range]');
    const out = {};
    calc.querySelectorAll('[data-out]').forEach((el) => { out[el.dataset.out] = el; });

    const nf2 = new Intl.NumberFormat(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const nf1 = new Intl.NumberFormat(locale, { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    const money = (n) => nf2.format(n) + ' ₼';
    const pct = (n) => nf1.format(n) + '%';
    const parse = (el) => {
      const v = parseFloat(String(el.value).replace(/\s/g, '').replace(',', '.'));
      return Number.isFinite(v) ? v : NaN;
    };
    const setInvalid = (el, bad) => {
      el.parentElement.classList.toggle('is-invalid', bad);
      el.setAttribute('aria-invalid', String(bad));
    };
    // Hint texts contain {rec} / {max}; the amounts are shown in bold
    const fill = (text, vars) => {
      const p = document.createElement('p');
      text.split(/(\{\w+\})/).forEach((part) => {
        const key = part.slice(1, -1);
        if (/^\{\w+\}$/.test(part) && key in vars) {
          const b = document.createElement('b');
          b.textContent = vars[key];
          p.appendChild(b);
        } else {
          p.appendChild(document.createTextNode(part));
        }
      });
      return [...p.childNodes];
    };

    const update = () => {
      const cost = parse(inCost);
      const price = parse(inPrice);
      const target = parse(inTarget);

      const costBad = !(cost >= 0);
      const priceBad = !(price > 0);
      const targetBad = !(target > 0 && target < 100);
      setInvalid(inCost, costBad);
      setInvalid(inPrice, priceBad);
      setInvalid(inTarget, targetBad);

      if (!targetBad) {
        out.target.style.left = Math.min(target, 100) + '%';
        if (target >= +inRange.min && target <= +inRange.max) inRange.value = String(Math.round(target));
      }

      if (costBad || priceBad || targetBad) {
        out.fc.textContent = '—';
        out.margin.textContent = out.marginPct.textContent = out.rec.textContent = '—';
        out.status.className = 'pill pill--idle';
        out.status.textContent = t.idle;
        out.meter.style.width = '0';
        out.hint.textContent = t.hintInvalid;
        return;
      }

      const fc = cost / price * 100;
      const margin = price - cost;
      const recPrice = cost / (target / 100);
      const maxCost = price * target / 100;
      const ok = fc <= target;

      out.fc.textContent = pct(fc);
      out.margin.textContent = money(margin);
      out.marginPct.textContent = pct(margin / price * 100);
      out.rec.textContent = money(recPrice);

      out.status.className = 'pill ' + (ok ? 'pill--ok' : 'pill--warn');
      out.status.textContent = ok ? t.ok : t.warn;

      out.meter.style.width = Math.min(fc, 100) + '%';
      out.meter.classList.toggle('is-warn', !ok);

      out.hint.replaceChildren(...fill(ok ? t.hintOk : t.hintWarn, { rec: money(recPrice), max: money(maxCost) }));
    };

    inRange.addEventListener('input', () => {
      inTarget.value = inRange.value;
      update();
    });
    [inCost, inPrice, inTarget].forEach((el) => el.addEventListener('input', update));
    calc.addEventListener('submit', (e) => e.preventDefault());
    update();
  }

  /* ---------- Contact form: sent from the visitor's WhatsApp, also saved via /api/contact.php ---------- */
  const form = document.querySelector('[data-form]');
  if (form) {
    const t = JSON.parse(form.dataset.i18n);
    const success = form.querySelector('.form__success');
    const waLink = form.querySelector('[data-wa-link]');
    const fields = {
      name: (v) => v.trim().length >= 2,
      phone: (v) => v.replace(/\D/g, '').length >= 9,
    };

    const validateField = (name) => {
      const input = form.elements[name];
      const ok = fields[name](input.value);
      input.setAttribute('aria-invalid', String(!ok));
      if (ok) input.removeAttribute('aria-describedby');
      else input.setAttribute('aria-describedby', input.id + '-err');
      input.closest('.field').classList.toggle('has-error', !ok);
      return ok;
    };

    Object.keys(fields).forEach((name) => {
      form.elements[name].addEventListener('input', () => {
        if (form.elements[name].getAttribute('aria-invalid') === 'true') validateField(name);
      });
    });

    // "Salam! Saytdan sorğu:" and a "label: value" line per filled-in field
    const waMessage = () => {
      const service = form.elements.service;
      const lines = [
        [t.name, form.elements.name.value],
        [t.phone, form.elements.phone.value],
        [t.company, form.elements.company.value],
        [t.service, service.value ? service.selectedOptions[0].textContent : ''],
        [t.message, form.elements.message.value],
      ].filter(([, value]) => value.trim() !== '').map(([label, value]) => label + ': ' + value.trim());
      return [t.intro, ...lines].join('\n');
    };

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const results = Object.keys(fields).map(validateField);
      if (results.includes(false)) {
        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      const url = form.dataset.wa + '?text=' + encodeURIComponent(waMessage());
      // Opened within the submit event, so popup blockers allow it; phones hand wa.me to the app
      window.open(url, '_blank', 'noopener');
      // The copy for Admin → Requests; the WhatsApp message is the main channel, so a failure stays silent
      fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, keepalive: true })
        .catch(() => {});
      // Fallback if the new window was blocked or closed
      waLink.href = url;
      form.classList.add('is-sent');
      success.hidden = false;
      form.reset();
    });

    form.querySelector('[data-form-reset]').addEventListener('click', () => {
      form.classList.remove('is-sent');
      success.hidden = true;
      form.elements.name.focus();
    });
  }
})();
