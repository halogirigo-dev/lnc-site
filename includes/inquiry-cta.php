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

    <form action="/process-booking.php" method="POST" class="reveal reveal--d1">
      <?php if (!empty($_SESSION['csrf_token'])): ?>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <?php endif; ?>
      <div class="inquiry-form">
        <input type="text"   name="name"    class="form-input" placeholder="Full Name" required>
        <input type="email"  name="email"   class="form-input" placeholder="Email Address" required>
        <input type="text"   name="dates"   class="form-input" placeholder="Travel Dates (e.g. Aug 2026)">
        <input type="number" name="guests"  class="form-input" placeholder="No. of Guests" min="1" max="20">
        <select name="experience" class="form-input form-input--full">
          <option value="" disabled selected>Select Experience</option>
          <option value="lombok-signature">Lombok Signature (3D/2N)</option>
          <option value="rinjani-summit">Rinjani Summit Trek (4D/3N)</option>
          <option value="sasak-culture">Sasak Village Immersion (2D/1N)</option>
          <option value="honeymoon">Honeymoon Escape</option>
          <option value="long-stay">Long Stay Collection (7+ nights)</option>
          <option value="custom">Custom / Not Sure Yet</option>
        </select>
        <textarea name="message" class="form-input form-input--full" placeholder="Tell us about your dream journey — destinations, pace, special occasions, dietary needs…" rows="4"></textarea>
      </div>
      <button type="submit" class="btn btn--primary btn--full" style="font-size:13px;padding:18px 32px;letter-spacing:.18em;">
        SEND MY ENQUIRY &nbsp;→
      </button>
      <p style="text-align:center;margin-top:16px;font-family:'MuseoModerno',sans-serif;font-size:12px;color:rgba(255,255,255,.3);letter-spacing:.06em;">
        Your details are private. No spam — ever.
      </p>
    </form>

  </div>
</section>