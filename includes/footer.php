<?php // includes/footer.php — Shared footer + scripts ?>
<!-- Pre-Footer CTA Zone -->
<div class="prefooter-cta">
  <h2>Still planning your Lombok journey?</h2>
  <p>Let's talk. We respond within 24 hours — or chat with us now on WhatsApp.</p>
  <div class="prefooter-cta__actions">
    <a href="booking.php" class="btn btn--primary">Plan Your Journey</a>
    <a href="https://wa.me/6281200000000?text=Hi%20LNC%2C%20I%27d%20like%20to%20enquire%20about%20a%20trip%20to%20Lombok" 
       class="btn-ghost" target="_blank" rel="noopener">
      💬 WhatsApp Us
    </a>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <!-- Brand -->
      <div>
        <div class="footer__logo">
          <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" alt="<?= SITE_NAME ?>">
          <div>
            <div class="footer__brand-name">Lombok Nature</div>
            <div class="footer__brand-sub">Culture</div>
          </div>
        </div>
        <p class="footer__desc">
          <?= SITE_TAGLINE ?>.<br>
          Private tours, mountain treks, cultural experiences, and honeymoon escapes — all exclusively yours.
        </p>
        <div class="footer__socials">
          <a href="https://wa.me/<?= SITE_WA ?>" class="footer__social" target="_blank">WhatsApp</a>
          <a href="#" class="footer__social">Instagram</a>
          <a href="#" class="footer__social">TripAdvisor</a>
        </div>
      </div>
      <!-- Experiences -->
      <div>
        <p class="footer__col-title">Experiences</p>
        <a href="experiences.php?cat=culture"   class="footer__link">Culture &amp; Heritage</a>
        <a href="experiences.php?cat=island"    class="footer__link">Island Escape</a>
        <a href="experiences.php?cat=adventure" class="footer__link">Adventure</a>
        <a href="experiences.php?cat=honeymoon" class="footer__link">Honeymoon</a>
        <a href="experiences.php?cat=long"      class="footer__link">Long Stay</a>
      </div>
      <!-- Company -->
      <div>
        <p class="footer__col-title">Company</p>
        <a href="team.php"     class="footer__link">Our Team</a>
        <a href="hotels.php"   class="footer__link">Hotel Partners</a>
        <a href="legal.php"    class="footer__link">Legal &amp; Policies</a>
        <a href="booking.php"  class="footer__link">Book a Journey</a>
        <a href="invoice.php"  class="footer__link">Invoice Portal</a>
      </div>
      <!-- Contact -->
      <div>
        <p class="footer__col-title">Contact</p>
        <p class="footer__link" style="cursor:default;">
          <a href="mailto:<?= SITE_EMAIL ?>" class="footer__link"><?= SITE_EMAIL ?></a>
        </p>
        <p class="footer__link" style="cursor:default;">
          <a href="https://wa.me/<?= SITE_WA ?>" class="footer__link"><?= SITE_PHONE ?></a>
        </p>
        <p class="footer__link" style="cursor:default;line-height:1.6;"><?= SITE_ADDRESS ?></p>
        <p class="footer__link" style="cursor:default;">Mon–Sat 08:00–20:00 WITA</p>
      </div>
    </div>
    <div class="footer__bottom">
      <p class="footer__copy">© <?= SITE_YEAR ?> <?= SITE_COMPANY ?>. All rights reserved.</p>
      <div class="footer__legal">
        <a href="legal.php#terms">Terms of Service</a>
        <a href="legal.php#privacy">Privacy Policy</a>
        <a href="legal.php#cancellation">Cancellation Policy</a>
      </div>
    </div>
  </div>
</footer>

<!-- WhatsApp Floating Button (single — consolidated) -->
<a href="https://wa.me/<?= SITE_WA ?>?text=Hi%20LNC%2C%20I%27d%20like%20to%20enquire%20about%20a%20tour%20to%20Lombok."
   class="whatsapp-float no-print"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chat with us on WhatsApp">
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.118 1.528 5.845L.057 23.882l6.204-1.627A11.938 11.938 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.806 9.806 0 01-5.031-1.388l-.361-.214-3.732.979 1.001-3.635-.235-.374A9.77 9.77 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/>
  </svg>
</a>

<!-- Scripts -->
<script src="<?= ASSETS_URL ?>/js/main.js?v=<?= filemtime(__DIR__.'/../assets/js/main.js') ?>"></script>

  <!-- Mobile Sticky CTA -->
  <div class="mobile-sticky-cta" id="mobile-sticky-cta">
    <a href="booking.php">Plan Your Journey &rarr;</a>
  </div>

</body>
</html>
