<?php
require_once 'config.php';
require_once 'data.php';
$page_title = 'Plan Your Journey';
$page_desc  = 'Submit a journey request to PT Lombok Nature Culture. We reply within 24 hours with a personalised proposal.';
include 'includes/head.php';

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$preselect = $_GET['package'] ?? '';
$prefill_name  = htmlspecialchars($_GET['name']  ?? '');
$prefill_email = htmlspecialchars($_GET['email'] ?? '');
$prefill_dates = htmlspecialchars($_GET['dates'] ?? '');
$prefill_guests= htmlspecialchars($_GET['guests'] ?? '');
$prefill_exp   = htmlspecialchars($_GET['experience'] ?? $preselect);
$prefill_msg   = htmlspecialchars($_GET['message'] ?? '');

// Error message display
$error_msgs = [
  'invalid_name'    => 'Please enter your full name (at least 2 characters).',
  'invalid_email'   => 'Please enter a valid email address.',
  'invalid_phone'   => 'Please enter a valid phone number.',
  'invalid_request' => 'Your session has expired. Please try again.',
];
$error = isset($_GET['error'], $error_msgs[$_GET['error']]) ? $error_msgs[$_GET['error']] : '';
?>

<!-- Top Bar -->
<?php if ($error): ?>
<div class="booking-error-banner" role="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<div class="booking-topbar">
  <a href="index.php" class="booking-topbar__brand">
    <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" class="booking-topbar__logo" alt="<?= SITE_NAME ?>">
    <div>
      <div class="booking-topbar__name">Lombok Nature</div>
      <div class="booking-topbar__sub">Culture</div>
    </div>
  </a>
  <a href="index.php" class="booking-topbar__back">← Back to Site</a>
</div>

<!-- Progress Bar -->
<div class="booking-progress" id="booking-progress-bar">
  <?php $steps = ['Experience','Details','Your Info','Your Vision','Review']; ?>
  <?php foreach ($steps as $i => $s): ?>
  <div class="progress-step">
    <div class="progress-step__bubble<?= $i===0 ? ' progress-step__bubble--active' : '' ?>"><?= $i+1 ?></div>
    <span class="progress-step__label<?= $i===0 ? ' progress-step__label--active' : '' ?>"><?= $s ?></span>
  </div>
  <?php if ($i < count($steps)-1): ?>
  <div class="progress-connector"></div>
  <?php endif; ?>
  <?php endforeach; ?>
</div>

<!-- Booking Body -->
<div class="booking-body" id="booking-wrapper">
  <!-- Form -->
  <div class="booking-content">
    <form id="booking-form" method="POST" action="thank-you.php">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

      <!-- Step 1: Choose Experience -->
      <div class="booking-step">
        <h2 class="bk-step__title">Choose Your Experience</h2>
        <p class="bk-step__sub">Select the journey that speaks to you. All experiences are fully private and customisable.</p>
        <?php $icons=['culture'=>'◈','island'=>'◎','adventure'=>'⛰','honeymoon'=>'♡']; ?>
        <?php foreach (array_merge($packages_short, $packages_long) as $pkg): ?>
        <div class="pkg-option<?= $pkg['id'] === $prefill_exp ? ' selected' : '' ?>"
          data-title="<?= htmlspecialchars($pkg['title']) ?>"
          data-price="<?= fmt_idr($pkg['price']) ?>"
          data-duration="<?= htmlspecialchars($pkg['duration']) ?>"
          data-id="<?= $pkg['id'] ?>">
          <div class="pkg-option__icon" aria-hidden="true"><?= $icons[$pkg['category']] ?? '◉' ?></div>
          <div class="pkg-option__body">
            <p class="pkg-option__id"><?= $pkg['id'] ?> · <?= htmlspecialchars($pkg['duration']) ?></p>
            <p class="pkg-option__title"><?= htmlspecialchars($pkg['title']) ?></p>
            <p class="pkg-option__sub"><?= htmlspecialchars($pkg['subtitle']) ?></p>
          </div>
          <div class="pkg-option__pricing">
            <p class="pkg-option__price"><?= fmt_idr($pkg['price']) ?></p>
            <p class="pkg-option__per">/pax · excl. hotel</p>
            <div class="pkg-radio-dot<?= $pkg['id'] === $prefill_exp ? ' pkg-radio-dot--active' : '' ?>"></div>
          </div>
          <input type="radio" name="package" value="<?= $pkg['id'] ?>" class="sr-only" <?= $pkg['id'] === $prefill_exp ? 'checked' : '' ?>>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Step 2: Travel Details -->
      <div class="booking-step">
        <h2 class="bk-step__title">Travel Details</h2>
        <p class="bk-step__sub">Help us plan around your schedule and preferences.</p>
        <div class="grid-2">
          <div>
            <label class="bk-label">Preferred Dates</label>
            <input class="form-input bk-input" type="text" name="dates" placeholder="e.g. Aug 10–20, 2026 or flexible" value="<?= $prefill_dates ?>">
          </div>
          <div>
            <label class="bk-label">Number of Guests</label>
            <select class="form-input bk-input" name="guests">
              <option value="">Select</option>
              <?php for ($n=1;$n<=10;$n++): ?>
              <option value="<?= $n ?>" <?= $prefill_guests==$n?'selected':'' ?>><?= $n ?> guest<?= $n>1?'s':'' ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div>
            <label class="bk-label">Trip Duration</label>
            <input class="form-input bk-input" type="text" name="duration" placeholder="e.g. 7 days or flexible">
          </div>
          <div>
            <label class="bk-label">Date Flexibility</label>
            <select class="form-input bk-input" name="flexibility">
              <option value="">Select</option>
              <option>Fixed dates — no flexibility</option>
              <option>+/- 1 week flexibility</option>
              <option>Fully flexible on dates</option>
            </select>
          </div>
        </div>
        <div class="bk-field-group">
          <label class="bk-label">Accommodation Preference</label>
          <div class="bk-radio-grid">
            <?php foreach (['Eco / Authentic (2–3★)','Comfort (3–4★)','Luxury (5★ / Private Villa)'] as $opt): ?>
            <label class="bk-radio-label">
              <input type="radio" name="accommodation" value="<?= htmlspecialchars($opt) ?>"> <?= htmlspecialchars($opt) ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Step 3: Personal Info -->
      <div class="booking-step">
        <h2 class="bk-step__title">Your Details</h2>
        <p class="bk-step__sub">We need just the essentials to send you a personalised proposal.</p>
        <div class="grid-2">
          <?php $fields=[['name','Full Name','text','e.g. James Thornton'],['email','Email Address','email','james@email.com'],['phone','Phone / WhatsApp','text','+44 7700 000000'],['country','Country of Residence','text','e.g. United Kingdom'],['nationality','Nationality','text','e.g. British']]; ?>
          <?php foreach ($fields as [$fname,$label,$type,$ph]): ?>
          <div>
            <label class="bk-label" for="field-<?= $fname ?>"><?= $label ?></label>
            <input class="form-input bk-input" id="field-<?= $fname ?>" type="<?= $type ?>" name="<?= $fname ?>" placeholder="<?= $ph ?>" value="<?= $fname==='name'?$prefill_name:($fname==='email'?$prefill_email:'') ?>">
          </div>
          <?php endforeach; ?>
          <div>
            <label class="bk-label" for="field-age_range">Age Range</label>
            <select class="form-input bk-input" id="field-age_range" name="age_range">
              <option>Select</option>
              <?php foreach(['18–25','26–35','36–45','46–55','56+'] as $r): ?><option><?= $r ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="bk-field-group">
          <p class="bk-label">How did you hear about us?</p>
          <div class="bk-source-pills">
            <?php foreach(['Instagram','Google','TripAdvisor','Friend / Referral','Travel Agent','Other'] as $s): ?>
            <label class="bk-source-pill">
              <input type="radio" name="source" value="<?= htmlspecialchars($s) ?>" class="sr-only"> <?= $s ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Step 4: Vision -->
      <div class="booking-step">
        <h2 class="bk-step__title">Your Vision</h2>
        <p class="bk-step__sub">The more detail you share, the better we can craft your proposal.</p>
        <div class="bk-vision-stack">
          <div>
            <label class="bk-label" for="field-message">Describe Your Dream Journey</label>
            <textarea class="form-input bk-input" id="field-message" name="message" rows="5" placeholder="e.g. We'd love to reach the Rinjani summit on day 4, then spend a day at the crater lake..."><?= $prefill_msg ?></textarea>
          </div>
          <div>
            <label class="bk-label" for="field-special">Special Requirements</label>
            <textarea class="form-input bk-input" id="field-special" name="special" rows="3" placeholder="Dietary needs, mobility considerations, anniversary or birthday, photography requests..."></textarea>
          </div>
          <div>
            <label class="bk-label" for="field-budget">Budget Range (per person, IDR)</label>
            <select class="form-input bk-input" id="field-budget" name="budget">
              <option value="">Prefer not to say</option>
              <option>Under Rp 3.000.000</option>
              <option>Rp 3.000.000 – 6.000.000</option>
              <option>Rp 6.000.000 – 10.000.000</option>
              <option>Rp 10.000.000 – 20.000.000</option>
              <option>Above Rp 20.000.000</option>
            </select>
          </div>
          <div class="bk-next-steps">
            <p class="bk-next-steps__label">What Happens Next</p>
            <p class="bk-next-steps__body">After you submit, our team personally reviews your request and sends a bespoke itinerary within <strong>24–48 hours</strong>. A real human journey designer will reach out via email and WhatsApp.</p>
          </div>
        </div>
      </div>

      <!-- Step 5: Review -->
      <div class="booking-step">
        <h2 class="bk-step__title">Review &amp; Submit</h2>
        <p class="bk-step__sub">Please review before submitting. We'll craft your proposal within 24–48 hours.</p>
        <div class="bk-review-table">
          <?php $review_rows=[['Experience','summary-experience'],['Duration','summary-duration'],['Dates','summary-dates'],['Guests','summary-guests'],['Accommodation','summary-accommodation'],['Name','summary-name'],['Email','summary-email'],['Phone','summary-phone']]; ?>
          <?php foreach ($review_rows as [$k,$id]): ?>
          <div class="bk-review-row">
            <span class="bk-review-key"><?= $k ?></span>
            <span id="<?= $id ?>" class="bk-review-val">—</span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="bk-terms-note">
          <p>By submitting, you agree to our <a href="legal.php#terms">Terms &amp; Conditions</a>. No payment is required at this stage.</p>
        </div>
      </div>

    </form>

  </div>

  <!-- Sidebar -->
  <div class="booking-sidebar">
    <p class="bk-sidebar__label">Journey Summary</p>
    <div id="booking-summary-content">
      <p class="bk-sidebar__hint">Select an experience to see your journey summary here.</p>
    </div>
    <?php foreach ([['Package','summary-experience'],['Price','summary-price'],['Dates','summary-dates'],['Guests','summary-guests']] as [$k,$id]): ?>
    <div id="<?= $id ?>-row" class="bk-sidebar__row" style="display:none;">
      <p class="bk-sidebar__row-key"><?= $k ?></p>
      <p id="<?= $id ?>-sidebar" class="bk-sidebar__row-val">—</p>
    </div>
    <?php endforeach; ?>
    <div class="bk-sidebar__contact">
      <p class="bk-sidebar__contact-note">Questions? We reply within 24 hours.</p>
      <a href="https://wa.me/<?= SITE_WA ?>" class="bk-sidebar__wa"><?= SITE_PHONE ?></a>
    </div>
  </div>
</div>

<!-- Bottom Navigation -->
<div class="booking-foot" id="booking-foot">
  <button id="btn-back" class="btn btn--outline btn--sm" style="display:none;">← Back</button>
  <a href="index.php" class="btn btn--outline btn--sm" id="btn-cancel">Cancel</a>
  <button id="btn-next" class="btn btn--primary btn--sm">Continue →</button>
</div>

<?php include 'includes/footer.php'; ?>
