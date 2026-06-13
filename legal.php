<?php
require_once 'config.php';
require_once 'data.php';
$page_title = 'Legal & Policies';
$page_desc  = 'Terms & Conditions, Privacy Policy, Cancellation Policy and Cookie Policy of PT Lombok Nature Culture.';
$legal_sections = [
  ['id'=>'terms',       'label'=>'Terms & Conditions', 'color'=>'#2cb896'],
  ['id'=>'privacy',     'label'=>'Privacy Policy',     'color'=>'#c4964a'],
  ['id'=>'cancellation','label'=>'Cancellation Policy','color'=>'#8b6f4e'],
  ['id'=>'cookies',     'label'=>'Cookie Policy',      'color'=>'#8a7d6e'],
];
include 'includes/head.php';
include 'includes/nav.php';
?>

<!-- Page Header -->
<div style="background:#1a2118;padding:120px 72px 48px;margin-top:72px;">
  <div class="container">
    <span class="eyebrow" style="color:rgba(255,255,255,.4);"><?= SITE_COMPANY ?></span>
    <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:44px;color:#fff;line-height:1.1;margin-bottom:10px;">Legal &amp; Policies</h1>
    <p class="section-body" style="color:rgba(255,255,255,.45);max-width:540px;">Our policies are written to be clear and human. If you have questions, email us — we respond personally.</p>
  </div>
</div>

<!-- Tab bar -->
<div style="background:#fff;border-bottom:1px solid rgba(0,0,0,.08);padding:0 72px;position:sticky;top:0;z-index:50;overflow-x:auto;" class="no-scroll-tabs">
  <div style="display:flex;gap:0;">
    <?php foreach ($legal_sections as $s): ?>
    <button class="sidebar-nav-link" data-target="<?= $s['id'] ?>"
      style="display:inline-block;border:none;background:transparent;padding:16px 28px;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;cursor:pointer;color:#8a7d6e;border-bottom:2px solid transparent;transition:all .2s;white-space:nowrap;">
      <?= $s['label'] ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>

<!-- Body -->
<div style="display:grid;grid-template-columns:240px 1fr;max-width:1200px;margin:0 auto;padding:0 0 80px;align-items:start;">
  <!-- Sidebar -->
  <div style="padding:40px 0;position:sticky;top:72px;">
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;padding:0 16px;margin-bottom:12px;">On This Page</p>
    <?php foreach ($legal_sections as $s): ?>
    <button class="sidebar-nav-link" data-target="<?= $s['id'] ?>"><?= $s['label'] ?></button>
    <?php endforeach; ?>
    <div style="margin:24px 16px 0;padding:16px;background:#ede9e1;">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:#8a7d6e;margin-bottom:6px;">Questions?</p>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#8a7d6e;line-height:1.7;margin-bottom:8px;">Happy to explain any policy in plain language.</p>
      <a href="mailto:<?= SITE_EMAIL ?>" style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#2cb896;"><?= SITE_EMAIL ?></a>
    </div>
  </div>

  <!-- Content -->
  <div style="padding:40px 48px 0;background:#fff;min-height:80vh;">

    <!-- TERMS -->
    <div id="terms" class="legal-section">
      <span class="tag tag--teal" style="margin-bottom:12px;display:inline-block;">Terms &amp; Conditions</span>
      <h2 class="legal-h2">Terms &amp; Conditions</h2>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#8a7d6e;margin-bottom:24px;">Last updated: <?= date('F j, Y') ?> · Effective immediately</p>

      <h3 class="legal-h3">1. About These Terms</h3>
      <p class="legal-body">These Terms and Conditions govern your use of the <?= SITE_COMPANY ?> website and services. By submitting a booking inquiry or making a payment, you agree to these terms in full. <?= SITE_COMPANY ?> is a registered travel operator in West Nusa Tenggara, Indonesia.</p>

      <h3 class="legal-h3">2. Booking Process</h3>
      <p class="legal-body">All bookings begin with an inquiry. We do not accept direct payments without a confirmed proposal. Once you accept a proposal:</p>
      <ul class="legal-list">
        <li>A deposit of 30% of the total journey value is required to confirm your booking</li>
        <li>The balance of 70% is due no later than 30 days before departure</li>
        <li>Your booking is only confirmed upon receipt of your deposit payment</li>
        <li>A written confirmation and deposit invoice will be sent within 24 hours of payment</li>
      </ul>

      <h3 class="legal-h3">3. Pricing &amp; Currency</h3>
      <p class="legal-body">All prices are quoted in Indonesian Rupiah (IDR) unless otherwise stated. Prices are per person based on minimum 2 guests unless marked otherwise. Hotel accommodation is priced and booked separately. Prices are subject to change until a proposal is formally accepted and deposit paid.</p>

      <h3 class="legal-h3">4. Travel Insurance</h3>
      <div class="legal-alert"><p><strong>Travel insurance is mandatory for all mountain trekking experiences,</strong> including Mount Rinjani. We require proof of coverage including emergency evacuation. For all other experiences, we strongly recommend comprehensive travel insurance.</p></div>

      <h3 class="legal-h3">5. Health &amp; Fitness</h3>
      <p class="legal-body">Guests must disclose any medical conditions, physical limitations, or dietary requirements at the time of booking. Mount Rinjani requires a good level of physical fitness. <?= SITE_COMPANY ?> reserves the right to modify or cancel a trek itinerary if a guide determines a guest is not fit to continue safely.</p>

      <h3 class="legal-h3">6. Force Majeure</h3>
      <p class="legal-body"><?= SITE_COMPANY ?> shall not be liable for failure to perform due to events beyond our control including natural disasters, volcanic eruptions, government travel restrictions, pandemics, or civil unrest. In such cases, we will offer rescheduling or a full credit.</p>

      <h3 class="legal-h3">7. Governing Law</h3>
      <p class="legal-body">These terms are governed by the laws of the Republic of Indonesia. Disputes shall be resolved in the courts of West Nusa Tenggara province. Our maximum liability is limited to the total amount paid for the relevant booking.</p>
    </div>

    <hr class="divider">

    <!-- PRIVACY -->
    <div id="privacy" class="legal-section">
      <span class="tag tag--gold" style="margin-bottom:12px;display:inline-block;">Privacy Policy</span>
      <h2 class="legal-h2">Privacy Policy</h2>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#8a7d6e;margin-bottom:24px;">Last updated: <?= date('F j, Y') ?></p>

      <h3 class="legal-h3">1. What We Collect</h3>
      <ul class="legal-list">
        <li>Personal identification: full name, nationality, passport number (for trekking permits)</li>
        <li>Contact information: email address, phone/WhatsApp number, country of residence</li>
        <li>Travel information: dates, group size, preferences, special requirements</li>
        <li>Payment records (we do not store card details — processed by third-party providers)</li>
      </ul>

      <h3 class="legal-h3">2. How We Use Your Data</h3>
      <ul class="legal-list">
        <li>Prepare and deliver your personalised journey proposal</li>
        <li>Coordinate logistics (accommodation, guides, permits, transfers)</li>
        <li>Comply with Indonesian government requirements for national park entry</li>
        <li>Send booking confirmations, invoices, and journey documents</li>
      </ul>

      <h3 class="legal-h3">3. We Do Not Sell Your Data</h3>
      <div class="legal-alert"><p>We do not sell, rent, or share your personal information with third parties for marketing purposes — ever. Your data is shared only with suppliers required for your journey.</p></div>

      <h3 class="legal-h3">4. Your Rights</h3>
      <ul class="legal-list">
        <li><strong>Access:</strong> Request a copy of all data we hold about you</li>
        <li><strong>Correction:</strong> Request correction of inaccurate information</li>
        <li><strong>Deletion:</strong> Request deletion (subject to legal retention requirements)</li>
        <li><strong>Opt-out:</strong> Unsubscribe from marketing at any time</li>
      </ul>

      <h3 class="legal-h3">5. Contact</h3>
      <p class="legal-body">Privacy enquiries: <strong>privacy@lnc-travel.com</strong></p>
    </div>

    <hr class="divider">

    <!-- CANCELLATION -->
    <div id="cancellation" class="legal-section">
      <span class="tag" style="background:#8b6f4e;margin-bottom:12px;display:inline-block;">Cancellation Policy</span>
      <h2 class="legal-h2">Cancellation Policy</h2>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#8a7d6e;margin-bottom:24px;">Last updated: <?= date('F j, Y') ?></p>

      <div class="legal-alert"><p>We designed our cancellation policy to be fair to both guests and our local team of guides and partners who depend on confirmed bookings.</p></div>

      <h3 class="legal-h3">Guest-Initiated Cancellations</h3>
      <div class="legal-table">
        <?php foreach ([
          ['90+ days before departure','Full deposit refunded (minus 5% processing fee)'],
          ['60–89 days before departure','50% of deposit refunded'],
          ['30–59 days before departure','No deposit refund; balance waived'],
          ['Under 30 days before departure','No refund — 100% of total cost charged'],
        ] as [$timing,$refund]): ?>
        <div class="legal-table-row" style="grid-template-columns:180px 1fr;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#1a2118;"><?= $timing ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-size:13px;color:#3d3228;"><?= $refund ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <h3 class="legal-h3">LNC-Initiated Cancellations</h3>
      <ul class="legal-list">
        <li><strong>Rescheduling offered first</strong> — alternative dates at no additional cost</li>
        <li><strong>Full credit issued</strong> — valid 24 months if you cannot reschedule</li>
        <li><strong>Full refund</strong> — processed within 14 business days if neither option works</li>
      </ul>

      <h3 class="legal-h3">Rescheduling</h3>
      <p class="legal-body">One free reschedule is permitted up to 60 days before departure, subject to availability. A second reschedule incurs a Rp 1.500.000 administration fee. Rescheduling within 30 days is treated as cancellation.</p>

      <h3 class="legal-h3">How to Cancel</h3>
      <p class="legal-body">Email <strong>bookings@lnc-travel.com</strong> with your booking reference. Cancellation is effective from the date of written request. Verbal cancellations are not accepted.</p>
    </div>

    <hr class="divider">

    <!-- COOKIES -->
    <div id="cookies" class="legal-section">
      <span class="tag" style="background:#8a7d6e;margin-bottom:12px;display:inline-block;">Cookie Policy</span>
      <h2 class="legal-h2">Cookie Policy</h2>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#8a7d6e;margin-bottom:24px;">Last updated: <?= date('F j, Y') ?></p>

      <h3 class="legal-h3">Cookies We Use</h3>
      <div class="legal-table">
        <?php foreach ([
          ['Essential',   'Session management, form state, security tokens','Cannot be disabled'],
          ['Analytics',   'Google Analytics (anonymised) — page views, session duration','Can be disabled'],
          ['Performance', 'Google Fonts, CDN resources','Necessary for rendering'],
          ['Marketing',   'None','We run no ad-tracking cookies'],
        ] as [$type,$purpose,$control]): ?>
        <div class="legal-table-row" style="grid-template-columns:120px 1fr 160px;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#1a2118;"><?= $type ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-size:13px;color:#3d3228;"><?= $purpose ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#8a7d6e;"><?= $control ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <h3 class="legal-h3">Contact</h3>
      <p class="legal-body">Cookie questions: <strong>privacy@lnc-travel.com</strong></p>
    </div>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
