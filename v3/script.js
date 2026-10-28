/* Cost Control Services — Versiya 3 */
(function () {
  "use strict";

  var root = document.documentElement;
  root.classList.add("js");

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------- Header state ---------- */
  var header = document.getElementById("siteHeader");
  function onScroll() {
    header.classList.toggle("is-scrolled", window.scrollY > 8);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile menu ---------- */
  var burger = document.querySelector(".burger");
  var menu = document.getElementById("mobileMenu");

  function setMenu(open) {
    burger.setAttribute("aria-expanded", String(open));
    burger.setAttribute("aria-label", open ? "Menyunu bağla" : "Menyunu aç");
    menu.hidden = !open;
    document.body.style.overflow = open ? "hidden" : "";
  }
  burger.addEventListener("click", function () {
    setMenu(burger.getAttribute("aria-expanded") !== "true");
  });
  menu.addEventListener("click", function (e) {
    if (e.target.closest("a[href^='#']")) setMenu(false);
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !menu.hidden) {
      setMenu(false);
      burger.focus();
    }
  });
  window.addEventListener("resize", function () {
    if (window.innerWidth > 1180 && !menu.hidden) setMenu(false);
  });

  /* ---------- Language switch (visual only for now) ---------- */
  document.querySelectorAll(".lang__item").forEach(function (a) {
    a.addEventListener("click", function (e) { e.preventDefault(); });
  });

  /* ---------- Scroll reveal ---------- */
  var revealEls = document.querySelectorAll("[data-reveal]");
  if (reduceMotion || !("IntersectionObserver" in window)) {
    revealEls.forEach(function (el) { el.classList.add("is-in"); });
  } else {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-in");
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: "0px 0px -8% 0px", threshold: 0.12 });
    revealEls.forEach(function (el) { io.observe(el); });
  }

  /* ---------- Food cost calculator ---------- */
  var calc = document.getElementById("calcForm");
  if (calc) {
    var inCost = document.getElementById("cCost");
    var inPrice = document.getElementById("cPrice");
    var inTarget = document.getElementById("cTarget");
    var inRange = document.getElementById("cTargetRange");
    var oFc = document.getElementById("oFc");
    var oStatus = document.getElementById("oStatus");
    var oMeter = document.getElementById("oMeter");
    var oTargetMark = document.getElementById("oTargetMark");
    var oMargin = document.getElementById("oMargin");
    var oMarginPct = document.getElementById("oMarginPct");
    var oRec = document.getElementById("oRec");
    var oHint = document.getElementById("oHint");

    var nf2 = new Intl.NumberFormat("az-AZ", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var nf1 = new Intl.NumberFormat("az-AZ", { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    var money = function (n) { return nf2.format(n) + " ₼"; };
    var pct = function (n) { return nf1.format(n) + "%"; };

    var parse = function (el) {
      var v = parseFloat(String(el.value).replace(/\s/g, "").replace(",", "."));
      return isFinite(v) ? v : NaN;
    };

    var setInvalid = function (el, bad) {
      el.parentElement.classList.toggle("is-invalid", bad);
      el.setAttribute("aria-invalid", String(bad));
    };

    var update = function () {
      var cost = parse(inCost);
      var price = parse(inPrice);
      var target = parse(inTarget);

      var costBad = !(cost >= 0);
      var priceBad = !(price > 0);
      var targetBad = !(target > 0 && target < 100);
      setInvalid(inCost, costBad);
      setInvalid(inPrice, priceBad);
      setInvalid(inTarget, targetBad);

      if (!targetBad) {
        oTargetMark.style.left = Math.min(target, 100) + "%";
        if (target >= +inRange.min && target <= +inRange.max) inRange.value = String(Math.round(target));
      }

      if (costBad || priceBad || targetBad) {
        oFc.textContent = "—";
        oMargin.textContent = oMarginPct.textContent = oRec.textContent = "—";
        oStatus.className = "pill pill--idle";
        oStatus.textContent = "Rəqəmləri daxil edin";
        oMeter.style.width = "0";
        oHint.textContent = "Maya dəyəri, satış qiyməti və hədəf faizi müsbət rəqəm olmalıdır.";
        return;
      }

      var fc = cost / price * 100;
      var margin = price - cost;
      var marginPct = margin / price * 100;
      var recPrice = cost / (target / 100);
      var maxCost = price * target / 100;
      var ok = fc <= target;

      oFc.textContent = pct(fc);
      oMargin.textContent = money(margin);
      oMarginPct.textContent = pct(marginPct);
      oRec.textContent = money(recPrice);

      oStatus.className = "pill " + (ok ? "pill--ok" : "pill--warn");
      oStatus.textContent = ok ? "Hədəf daxilində" : "Hədəfdən yuxarı";

      oMeter.style.width = Math.min(fc, 100) + "%";
      oMeter.classList.toggle("is-warn", !ok);

      oHint.innerHTML = ok
        ? "Yemək hədəf daxilindədir. Xammal maya dəyəri <b>" + money(maxCost) + "</b>-a qədər artsa belə, hədəfdə qalırsınız."
        : "Hədəfə çatmaq üçün satış qiymətini <b>" + money(recPrice) + "</b>-a qaldırın və ya porsiyanın maya dəyərini <b>" + money(maxCost) + "</b>-a endirin.";
    };

    inRange.addEventListener("input", function () {
      inTarget.value = inRange.value;
      update();
    });
    [inCost, inPrice, inTarget].forEach(function (el) {
      el.addEventListener("input", update);
    });
    calc.addEventListener("submit", function (e) { e.preventDefault(); });
    update();
  }

  /* ---------- Contact form → /api/contact.php ---------- */
  var form = document.getElementById("contactForm");
  var success = document.getElementById("contactSuccess");
  var resetBtn = document.getElementById("contactReset");

  if (form) {
    var submitBtn = form.querySelector('[type="submit"]');
    var errorBox = document.createElement("p");
    errorBox.setAttribute("role", "alert");
    errorBox.hidden = true;
    errorBox.style.cssText = "margin:12px 0 0;color:#D93036;font-weight:600;font-size:.92rem";
    errorBox.textContent = "Göndərmək alınmadı. Bir az sonra yenidən cəhd edin və ya WhatsApp ilə yazın: +994 55 855 61 52.";
    form.appendChild(errorBox);

    var rules = {
      name: function (v) { return v.trim().length >= 2; },
      phone: function (v) { return v.replace(/\D/g, "").length >= 7; }
    };

    var check = function (input) {
      var rule = rules[input.name];
      if (!rule) return true;
      var valid = rule(input.value);
      input.closest(".field").classList.toggle("is-invalid", !valid);
      input.setAttribute("aria-invalid", String(!valid));
      return valid;
    };

    Object.keys(rules).forEach(function (name) {
      var input = form.elements[name];
      input.addEventListener("input", function () {
        if (input.closest(".field").classList.contains("is-invalid")) check(input);
      });
      input.addEventListener("blur", function () {
        if (input.value) check(input);
      });
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var firstInvalid = null;
      Object.keys(rules).forEach(function (name) {
        var input = form.elements[name];
        if (!check(input) && !firstInvalid) firstInvalid = input;
      });
      if (firstInvalid) {
        firstInvalid.focus();
        return;
      }
      submitBtn.disabled = true;
      errorBox.hidden = true;
      fetch(form.action, { method: "POST", body: new FormData(form), headers: { Accept: "application/json" } })
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
        .then(function () { submitBtn.disabled = false; });
    });

    resetBtn.addEventListener("click", function () {
      form.reset();
      form.querySelectorAll(".is-invalid").forEach(function (el) { el.classList.remove("is-invalid"); });
      success.hidden = true;
      form.hidden = false;
      form.elements.name.focus();
    });
  }

  /* ---------- Footer year ---------- */
  document.querySelectorAll("[data-year]").forEach(function (el) {
    el.textContent = String(new Date().getFullYear());
  });
})();
