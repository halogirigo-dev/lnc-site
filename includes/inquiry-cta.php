<?php // includes/inquiry-cta.php — Inquiry form section ?>
<section class="section section--grad cta-section" id="inquiry">
  <div class="cta-section__deco"></div>
  <div class="container--narrow" style="position:relative;z-index:1;">

    <div style="text-align:center;margin-bottom:56px;" class="reveal">
      <span class="eyebrow" style="color:rgba(255,255,255,.5);">Start Planning</span>
      <h2 class="section-title section-title--light" style="margin-bottom:16px;">
        Your Lombok Journey<br>Starts with a Conversation
      </h2>
      <p class="section-body" style="color:rgba(255,255,255,.6);max-width:480px;margin:0 auto;">
        Tell us your dates, interests, and travel style. We'll reply within 24 hours with a personalised itinerary — no commitment required.
      </p>
      <div style="display:flex;gap:24px;justify-content:center;margin-top:20px;">
        <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.4);">⟶ Response within 24h</span>
        <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.4);">⟶ 500+ travellers guided</span>
        <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.4);">⟶ 100% tailor-made</span>
      </div>
    </div>

    <form action="/process-booking.php" method="POST" class="reveal reveal--d1" novalidate>
      <?php if (!empty($_SESSION['csrf_token'])): ?>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <?php endif; ?>
      <div class="inquiry-form">
        <div>
          <label for="inquiry-name" class="sr-only">Full Name</label>
          <input type="text" id="inquiry-name" name="name" class="form-input" placeholder="Full Name" required aria-required="true" autocomplete="name">
        </div>
        <div>
          <label for="inquiry-email" class="sr-only">Email Address</label>
          <input type="email" id="inquiry-email" name="email" class="form-input" placeholder="Email Address" required aria-required="true" autocomplete="email">
        </div>
        <div>
          <label for="inquiry-dates" class="sr-only">Travel Dates</label>
          <input type="text" id="inquiry-dates" name="dates" class="form-input" placeholder="Travel Dates (e.g. Aug 2026)" autocomplete="off">
        </div>
        <div>
          <label for="inquiry-guests" class="sr-only">Number of Guests</label>
          <input type="number" id="inquiry-guests" name="guests" class="form-input" placeholder="No. of Guests" min="1" max="20">
        </div>
        <div class="form-input--full">
          <label for="inquiry-experience" class="sr-only">Select Experience</label>
          <select id="inquiry-experience" name="experience" class="form-input form-input--full">
            <option value="" disabled selected>Select Experience</option>
            <optgroup label="Short Stay (3–5 Days)">
              <option value="LNC-01">Lombok Signature — 3 Days / 2 Nights</option>
              <option value="LNC-02">The Sasak Living Heritage — 4 Days / 3 Nights</option>
              <option value="LNC-03">Mandalika Legends — 4 Days / 3 Nights</option>
              <option value="LNC-04">Gili Meno Serenity (Honeymoon) — 4 Days / 3 Nights</option>
              <option value="LNC-07">Lombok Surf Retreat — 3 Days / 2 Nights</option>
            </optgroup>
            <optgroup label="Long Stay (7–14 Days)">
              <option value="LNC-13">The Grand Lombok Odyssey — 10 Days / 9 Nights</option>
              <option value="LNC-14">Slow Travel — Village &amp; Sea — 7 Days / 6 Nights</option>
            </optgroup>
            <option value="custom">Custom / Not Sure Yet</option>
          </select>
        </div>
        <div class="form-input--full">
          <label for="inquiry-message" class="sr-only">Tell us about your dream journey</label>
          <textarea id="inquiry-message" name="message" class="form-input form-input--full" placeholder="Tell us about your dream journey — destinations, pace, special occasions, dietary needs…" rows="4"></textarea>
        </div>
      </div>
      <button type="submit" class="btn btn--primary btn--full" style="font-size:13px;padding:18px 32px;letter-spacing:.18em;">
        SEND MY ENQUIRY &nbsp;→
      </button>
      <p style="text-align:center;margin-top:16px;font-family:'MuseoModerno',sans-serif;font-size:12px;color:rgba(255,255,255,.3);letter-spacing:.06em;" aria-hidden="true">
        Your details are private. No spam — ever.
      </p>
    </form>

  </div>
</section>