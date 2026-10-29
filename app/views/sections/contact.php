<?php
/**
 * Contacts from Admin → Settings and the request form: site.js saves it via /api/contact.php
 * and opens the visitor's WhatsApp with the request as a message to our number.
 */
defined('CCS') || exit;

$email = setting('contact_email');
$waLabels = [
    'intro' => ui('wa_msg_intro'),
    'name' => ui('wa_msg_name'),
    'phone' => ui('wa_msg_phone'),
    'company' => ui('wa_msg_company'),
    'service' => ui('wa_msg_service'),
    'message' => ui('wa_msg_message'),
];
?>
<!-- SECTION: contact -->
<section class="contact section" id="elaqe">
  <div class="contact__glow" aria-hidden="true"></div>
  <div class="container contact__grid">
    <div class="contact__info" data-reveal>
      <p class="eyebrow"><?= e(txt('contact.eyebrow')) ?></p>
      <h2 class="section-title"><?= rich(txt('contact.title')) ?></h2>
      <p class="section-lead"><?= rich(txt('contact.lead')) ?></p>
      <ul class="contact__list">
        <li><span class="contact__ic"><svg class="icon"><use href="#i-pin"/></svg></span><div><span class="contact__label"><?= e(ui('contact_address')) ?></span><?php if (setting('contact_map') !== ''): ?><a href="<?= e(setting('contact_map')) ?>" target="_blank" rel="noopener"><?= e(txt('contact.address')) ?></a><?php else: ?><?= e(txt('contact.address')) ?><?php endif; ?></div></li>
        <li><span class="contact__ic"><svg class="icon"><use href="#i-phone"/></svg></span><div><span class="contact__label"><?= e(ui('contact_phone')) ?></span><a href="<?= e(phone_href()) ?>"><?= e(phone_display()) ?></a></div></li>
        <li><span class="contact__ic"><svg class="icon icon--fill"><use href="#i-whatsapp"/></svg></span><div><span class="contact__label"><?= e(ui('contact_whatsapp')) ?></span><a href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener"><?= e(preg_replace('#^https://#', '', whatsapp_url())) ?></a></div></li>
        <?php if ($email !== ''): ?>
          <li><span class="contact__ic"><svg class="icon"><use href="#i-mail"/></svg></span><div><span class="contact__label"><?= e(ui('contact_email')) ?></span><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div></li>
        <?php endif; ?>
        <li><span class="contact__ic"><svg class="icon"><use href="#i-instagram"/></svg></span><div><span class="contact__label"><?= e(ui('contact_instagram')) ?></span><a href="<?= e(instagram_url()) ?>" target="_blank" rel="noopener"><?= e(instagram_handle()) ?></a></div></li>
      </ul>
    </div>

    <form class="form" method="post" action="/api/contact.php" novalidate data-form data-wa="<?= e(whatsapp_url()) ?>" data-i18n="<?= e(json_encode($waLabels, JSON_UNESCAPED_UNICODE)) ?>" data-reveal style="--delay:.1s">
      <input type="hidden" name="source" value="<?= e($pageKey . '-' . site_lang()) ?>">
      <div class="form__trap" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="form__fields">
        <h3 class="form__title"><?= e(txt('contact.form_title')) ?></h3>
        <div class="field">
          <label for="f-name"><?= e(ui('form_name')) ?> <span aria-hidden="true">*</span></label>
          <input id="f-name" name="name" type="text" autocomplete="name" required placeholder="<?= e(ui('form_name_ph')) ?>">
          <p class="field__error" id="f-name-err"><?= e(ui('form_name_err')) ?></p>
        </div>
        <div class="field">
          <label for="f-phone"><?= e(ui('form_phone')) ?> <span aria-hidden="true">*</span></label>
          <input id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" required placeholder="+994 __ ___ __ __">
          <p class="field__error" id="f-phone-err"><?= e(ui('form_phone_err')) ?></p>
        </div>
        <div class="field">
          <label for="f-company"><?= e(ui('form_company')) ?></label>
          <input id="f-company" name="company" type="text" autocomplete="organization" placeholder="<?= e(ui('form_company_ph')) ?>">
        </div>
        <div class="field">
          <label for="f-service"><?= e(ui('form_service')) ?></label>
          <div class="select">
            <select id="f-service" name="service">
              <option value=""><?= e(ui('form_service_ph')) ?></option>
              <?php // Values stay in AZ, so requests group by service whatever the visitor's language ?>
              <?php foreach (site_services() as $service): ?>
                <option value="<?= e($service['title_az']) ?>"><?= e(pick($service, 'title')) ?></option>
              <?php endforeach; ?>
              <option value="Kompleks"><?= e(ui('form_service_any')) ?></option>
            </select>
            <svg class="icon"><use href="#i-chevron"/></svg>
          </div>
        </div>
        <div class="field field--full">
          <label for="f-message"><?= e(ui('form_message')) ?></label>
          <textarea id="f-message" name="message" rows="4" placeholder="<?= e(ui('form_message_ph')) ?>"></textarea>
        </div>
        <div class="form__footer">
          <button class="btn btn--primary" type="submit"><svg class="icon icon--fill"><use href="#i-whatsapp"/></svg> <?= e(ui('form_submit')) ?></button>
          <p class="form__note"><?= e(ui('form_note')) ?></p>
        </div>
      </div>
      <div class="form__success" role="status" aria-live="polite" hidden>
        <span class="form__success-ic"><svg class="icon"><use href="#i-check"/></svg></span>
        <h3><?= e(txt('contact.success_title')) ?></h3>
        <p><?= rich(txt('contact.success')) ?></p>
        <p class="form__wa-hint"><?= e(ui('form_wa_hint')) ?></p>
        <a class="btn btn--primary" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener" data-wa-link><svg class="icon icon--fill"><use href="#i-whatsapp"/></svg> <?= e(ui('form_wa_open')) ?></a>
        <button class="btn btn--ghost btn--sm" type="button" data-form-reset><?= e(ui('form_again')) ?></button>
      </div>
    </form>
  </div>
</section>
