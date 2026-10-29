<?php
/** Food cost calculator (logic in site.js; the messages come from data-i18n). */
defined('CCS') || exit;

$i18n = [
    'ok' => ui('calc_ok'),
    'warn' => ui('calc_warn'),
    'idle' => ui('calc_idle'),
    'hintInvalid' => ui('calc_hint_invalid'),
    'hintOk' => ui('calc_hint_ok'),
    'hintWarn' => ui('calc_hint_warn'),
];
$note = txt('calc.note');
// The button preselects the food costing service in the form below
$costing = current(array_filter(site_services(), static fn($s) => $s['icon'] === 'recipe')) ?: [];
?>
<!-- SECTION: calc -->
<section class="calc section" id="kalkulyator" aria-labelledby="calc-title">
  <div class="calc__glow" aria-hidden="true"></div>
  <div class="container calc__grid">
    <div class="calc__intro" data-reveal>
      <p class="eyebrow"><?= e(txt('calc.eyebrow')) ?></p>
      <h2 class="section-title" id="calc-title"><?= rich(txt('calc.title')) ?></h2>
      <p class="section-lead"><?= rich(txt('calc.lead')) ?></p>
      <?php if ($note !== ''): ?><p class="calc__note"><?= rich($note) ?></p><?php endif; ?>
      <a class="btn btn--primary" href="#elaqe" data-service="<?= e($costing['title_az'] ?? '') ?>"><?= e(txt('calc.cta')) ?> <svg class="icon"><use href="#i-arrow"/></svg></a>
    </div>

    <form class="calc__card" novalidate data-calc data-i18n="<?= e(json_encode($i18n, JSON_UNESCAPED_UNICODE)) ?>" data-reveal style="--delay:.1s">
      <div class="calc__fields">
        <div class="cfield">
          <label for="c-cost"><?= e(ui('calc_cost')) ?></label>
          <div class="cfield__input">
            <input id="c-cost" name="cost" type="text" inputmode="decimal" autocomplete="off" value="<?= e(ui('calc_cost_default')) ?>">
            <span class="cfield__unit">₼</span>
          </div>
        </div>
        <div class="cfield">
          <label for="c-price"><?= e(ui('calc_price')) ?></label>
          <div class="cfield__input">
            <input id="c-price" name="price" type="text" inputmode="decimal" autocomplete="off" value="<?= e(ui('calc_price_default')) ?>">
            <span class="cfield__unit">₼</span>
          </div>
        </div>
        <div class="cfield cfield--wide">
          <label for="c-target"><?= e(ui('calc_target')) ?></label>
          <div class="cfield__range">
            <input type="range" min="15" max="50" step="1" value="30" aria-label="<?= e(ui('calc_target_aria')) ?>" data-calc-range>
            <div class="cfield__input cfield__input--sm">
              <input id="c-target" name="target" type="text" inputmode="decimal" autocomplete="off" value="30">
              <span class="cfield__unit">%</span>
            </div>
          </div>
        </div>
      </div>

      <div class="calc__result">
        <div class="calc__fc">
          <div>
            <span class="calc__label"><?= e(ui('calc_fc')) ?></span>
            <output class="calc__fc-val" for="c-cost c-price" data-out="fc">35,0%</output>
          </div>
          <span class="pill pill--warn" data-out="status"><?= e(ui('calc_warn')) ?></span>
        </div>

        <div class="meter" aria-hidden="true">
          <div class="meter__fill" data-out="meter"></div>
          <div class="meter__target" data-out="target"><span><?= e(ui('calc_target_mark')) ?></span></div>
        </div>

        <dl class="calc__stats">
          <div class="cstat"><dt><?= e(ui('calc_margin')) ?></dt><dd data-out="margin">—</dd></div>
          <div class="cstat"><dt><?= e(ui('calc_margin_pct')) ?></dt><dd data-out="marginPct">—</dd></div>
          <div class="cstat cstat--accent"><dt><?= e(ui('calc_rec')) ?></dt><dd data-out="rec">—</dd></div>
        </dl>

        <p class="calc__hint" aria-live="polite" data-out="hint"></p>
      </div>
    </form>
  </div>
</section>
