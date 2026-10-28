(() => {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const desktop = window.matchMedia('(min-width: 960px)');
  const hasIO = 'IntersectionObserver' in window;

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
    burger.setAttribute('aria-label', open ? 'Menyunu bağla' : 'Menyunu aç');
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

  /* ---------- Language switch (visual only for now) ---------- */
  document.querySelectorAll('.lang__btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      // TODO: wire to RU / EN versions when they exist
    });
  });

  /* ---------- Active nav link on scroll ---------- */
  const navLinks = [...document.querySelectorAll('.nav__link')];
  if (hasIO) {
    const sectionObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const id = '#' + entry.target.id;
        navLinks.forEach((link) => link.classList.toggle('is-active', link.getAttribute('href') === id));
      });
    }, { rootMargin: '-45% 0px -50% 0px' });

    navLinks
      .map((link) => document.querySelector(link.getAttribute('href')))
      .filter(Boolean)
      .forEach((section) => sectionObserver.observe(section));
  }

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
  }

  /* ---------- "Apply for this service" links preselect the form ---------- */
  const serviceSelect = document.getElementById('f-service');
  document.querySelectorAll('[data-service]').forEach((link) => {
    link.addEventListener('click', () => {
      if (serviceSelect) serviceSelect.value = link.dataset.service;
    });
  });

  /* ---------- Clients marquee: duplicate tracks for a seamless loop ---------- */
  if (!reduceMotion) {
    document.querySelectorAll('.marquee__track').forEach((track) => {
      for (let i = 0; i < 2; i += 1) {
        const clone = track.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        clone.querySelectorAll('img').forEach((img) => { img.alt = ''; img.removeAttribute('title'); });
        track.parentElement.appendChild(clone);
      }
    });
  }

  /* ---------- Contact form (demo submit — replace with PHP handler) ---------- */
  const form = document.querySelector('[data-form]');
  if (form) {
    const success = form.querySelector('.form__success');
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

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const results = Object.keys(fields).map(validateField);
      if (results.includes(false)) {
        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        if (firstInvalid) firstInvalid.focus();
        return;
      }
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
