<?php
require_once 'config.php';
require_once 'data.php';
require_once 'db.php';
require_once 'lib/midtrans.php';

$ref = preg_replace('/[^A-Z0-9\-]/', '', strtoupper(trim($_GET['ref'] ?? '')));

// Load booking: DB first, session fallback
$booking = lnc_get_booking($ref);
if (!$booking && isset($_SESSION['lnc_booking']) && ($_SESSION['lnc_booking']['ref'] ?? '') === $ref) {
  $sess = $_SESSION['lnc_booking'];
  $booking = [
    'ref'             => $sess['ref'],
    'status'          => 'pending_payment',
    'package_id'      => $sess['package_id'] ?? '',
    'package_title'   => $sess['package_title'] ?? '',
    'package_duration'=> $sess['package_duration'] ?? '',
    'guests'          => $sess['guests'] ?? 1,
    'dates'           => $sess['dates'] ?? '',
    'total_amount'    => $sess['total_amount'] ?? $sess['subtotal'] ?? 0,
    'deposit_amount'  => $sess['deposit_amount'] ?? $sess['deposit'] ?? 0,
    'balance_amount'  => $sess['balance_amount'] ?? $sess['balance'] ?? 0,
    'name'            => $sess['name'] ?? '',
    'email'           => $sess['email'] ?? '',
    'phone'           => $sess['phone'] ?? '',
  ];
}

if (!$booking) {
  header('Location: booking.php');
  exit;
}

// If already paid, show a different message
$already_paid = in_array($booking['status'], ['deposit_paid', 'balance_paid', 'confirmed']);

// Get snap token
$snap_token = null;
$db = lnc_db();
if ($db) {
  $snap_token = lnc_get_snap_token_from_db($ref, 'deposit');
}
if (!$snap_token && isset($_SESSION['lnc_snap_token'])) {
  $snap_token = $_SESSION['lnc_snap_token'];
}

// If no snap token and not already paid, generate one
if (!$snap_token && !$already_paid) {
  $snap_params = lnc_format_snap_params($booking, 'deposit');
  $snap_result = lnc_get_snap_token($snap_params);
  if (!isset($snap_result['error'])) {
    $snap_token = $snap_result['token'];
    $_SESSION['lnc_snap_token'] = $snap_token;
    if ($db) {
      try {
        $db->prepare("
          INSERT INTO payments (booking_ref, payment_type, amount, midtrans_order_id, snap_token)
          VALUES ($1, 'deposit', $2, $3, $4)
          ON CONFLICT (booking_ref, payment_type) DO UPDATE SET snap_token = EXCLUDED.snap_token
        ")->execute([
          $ref,
          $booking['deposit_amount'],
          $ref . '-DEP',
          $snap_token,
        ]);
      } catch (PDOException $e) {}
    }
  }
}

$demo = lnc_is_demo_mode();
$deposit_fmt = 'Rp ' . number_format($booking['deposit_amount'], 0, ',', '.');
$balance_fmt = 'Rp ' . number_format($booking['balance_amount'], 0, ',', '.');
$total_fmt   = 'Rp ' . number_format($booking['total_amount'],   0, ',', '.');

$page_title = 'Complete Your Deposit — ' . $ref;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($page_title) ?> — <?= SITE_NAME ?></title>
<link rel="icon" type="image/png" href="/uploads/logo-1777215811265.png">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=<?= filemtime(__DIR__.'/assets/css/style.css') ?>">
<style>
.pay-wrap{min-height:100vh;background:#f7f4ee;display:grid;grid-template-columns:1fr 1fr;max-width:1100px;margin:0 auto}
.pay-left{background:#1a2118;padding:56px 48px;display:flex;flex-direction:column;gap:0}
.pay-right{background:#fff;padding:56px 48px;display:flex;flex-direction:column;justify-content:center}
.pay-topbar{background:#fff;border-bottom:1px solid #e0d8ce;padding:0 48px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10}
.pay-divider{height:1px;background:rgba(255,255,255,.1);margin:24px 0}
.pay-label{font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px}
.pay-val{font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:14px;color:#fff}
.pay-methods{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.pay-method-badge{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);padding:5px 10px;font-family:'MuseoModerno',sans-serif;font-size:10px;font-weight:600;color:rgba(255,255,255,.6)}
.pay-amount-row{display:flex;justify-content:space-between;align-items:baseline;padding:12px 0;border-bottom:1px solid rgba(255,255,255,.08)}
.demo-banner{background:#fffbe6;border:1px solid #f5c842;padding:16px 20px;margin-bottom:28px;border-left:4px solid #f5c842}
.already-paid-box{background:#f0faf7;border:1px solid rgba(44,184,150,.3);padding:28px;text-align:center}
@media(max-width:768px){
  .pay-wrap{grid-template-columns:1fr}
  .pay-left,.pay-right{padding:36px 24px}
}
</style>
</head>
<body>

<!-- Top Bar -->
<div class="pay-topbar">
  <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
    <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" style="height:36px;" alt="<?= SITE_NAME ?>">
    <div>
      <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:#1a2118;">Lombok Nature</div>
      <div style="font-family:'MuseoModerno',sans-serif;font-size:10px;color:#8a7d6e;line-height:1;">Culture</div>
    </div>
  </a>
  <a href="booking.php" class="btn btn--outline btn--sm">← Back to Booking</a>
</div>

<div class="pay-wrap">

  <!-- Left: Booking Summary -->
  <div class="pay-left">
    <div style="margin-bottom:32px;">
      <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.35);">Booking Summary</span>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#fff;margin-top:8px;line-height:1.2;"><?= htmlspecialchars($booking['package_title']) ?></p>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#2cb896;margin-top:4px;"><?= htmlspecialchars($booking['package_id']) ?></p>
    </div>

    <div class="pay-divider"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
      <div>
        <div class="pay-label">Duration</div>
        <div class="pay-val"><?= htmlspecialchars($booking['package_duration']) ?></div>
      </div>
      <div>
        <div class="pay-label">Guests</div>
        <div class="pay-val"><?= (int)$booking['guests'] ?> guest<?= (int)$booking['guests'] > 1 ? 's' : '' ?></div>
      </div>
      <?php if (!empty($booking['dates'])): ?>
      <div style="grid-column:1/-1;">
        <div class="pay-label">Dates</div>
        <div class="pay-val"><?= htmlspecialchars($booking['dates']) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <div class="pay-divider"></div>

    <div style="margin-bottom:8px;">
      <div class="pay-amount-row">
        <div>
          <div class="pay-label">Deposit Now (30%)</div>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:24px;color:#2cb896;"><?= $deposit_fmt ?></div>
        </div>
        <span style="font-family:'MuseoModerno',sans-serif;font-size:10px;color:rgba(44,184,150,.7);">DUE NOW</span>
      </div>
      <div class="pay-amount-row">
        <div>
          <div class="pay-label">Balance (70%)</div>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:16px;color:rgba(255,255,255,.5);"><?= $balance_fmt ?></div>
        </div>
        <span style="font-family:'MuseoModerno',sans-serif;font-size:10px;color:rgba(255,255,255,.3);">DUE LATER</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;">
        <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;color:rgba(255,255,255,.3);">Total package</span>
        <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:rgba(255,255,255,.4);"><?= $total_fmt ?></span>
      </div>
    </div>

    <div class="pay-divider"></div>

    <div>
      <div class="pay-label" style="margin-bottom:10px;">Accepted Payment Methods</div>
      <div class="pay-methods">
        <?php foreach (['Visa','Mastercard','QRIS','GoPay','OVO','BCA','BNI','BRI','Mandiri'] as $m): ?>
        <span class="pay-method-badge"><?= $m ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="pay-divider"></div>

    <div style="display:flex;align-items:center;gap:8px;">
      <span style="color:rgba(44,184,150,.7);font-size:16px;">🔒</span>
      <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;color:rgba(255,255,255,.3);">Secured by Midtrans · SSL · PCI-DSS Compliant</span>
    </div>
  </div>

  <!-- Right: Payment Action -->
  <div class="pay-right">
    <?php if ($demo): ?>
    <div class="demo-banner">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#856404;margin-bottom:6px;">⚠️ DEMO MODE — No real payment will be charged</p>
      <p style="font-family:'Museo',sans-serif;font-size:13px;color:#856404;margin-bottom:10px;">Midtrans credentials are placeholders. Use the simulate button below to test the full payment flow.</p>
      <a href="payment-callback.php?simulate=paid&ref=<?= urlencode($ref) ?>"
         class="btn btn--dark btn--sm">Simulate Successful Payment →</a>
    </div>
    <?php endif; ?>

    <?php if ($already_paid): ?>
    <div class="already-paid-box">
      <div style="font-size:32px;margin-bottom:12px;">✓</div>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#1a2118;margin-bottom:8px;">This Booking Has Been Paid</p>
      <p style="font-family:'Museo',sans-serif;font-size:14px;color:#8a7d6e;line-height:1.7;margin-bottom:24px;">Your deposit has already been received. Check your invoice or thank-you page for next steps.</p>
      <a href="invoice.php?ref=<?= urlencode($ref) ?>" class="btn btn--primary" style="margin-right:10px;">View Invoice →</a>
      <a href="thank-you.php?ref=<?= urlencode($ref) ?>" class="btn btn--outline btn--sm">Thank-You Page</a>
    </div>

    <?php else: ?>

    <div>
      <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;display:block;margin-bottom:10px;">Step 1 of 2</span>
      <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:32px;color:#1a2118;line-height:1.1;margin-bottom:16px;">Complete Your Deposit</h1>
      <p style="font-family:'Museo',sans-serif;font-size:15px;color:#8a7d6e;line-height:1.8;margin-bottom:32px;">
        A 30% deposit secures your booking and guide. The remaining 70% is due before your trip begins.
      </p>

      <div style="background:#f0faf7;border:1px solid rgba(44,184,150,.25);padding:16px 20px;margin-bottom:32px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#1a2118;">Deposit Amount</span>
          <span style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:22px;color:#2cb896;"><?= $deposit_fmt ?></span>
        </div>
        <div style="font-family:'Museo',sans-serif;font-size:11px;color:#8a7d6e;margin-top:4px;">Ref: <?= htmlspecialchars($ref) ?> · <?= htmlspecialchars($booking['package_title']) ?></div>
      </div>

      <?php if ($snap_token && !$demo): ?>
      <button id="pay-btn" class="btn btn--primary btn--full" style="font-size:14px;padding:18px 32px;">
        Pay Deposit — <?= $deposit_fmt ?>
      </button>
      <div id="pay-error" style="display:none;background:#fef2f2;border:1px solid rgba(229,57,53,.3);padding:12px 16px;margin-top:12px;font-family:'Museo',sans-serif;font-size:13px;color:#e53935;"></div>
      <?php elseif ($demo): ?>
      <button class="btn btn--primary btn--full" style="font-size:14px;padding:18px 32px;opacity:.4;cursor:not-allowed;" disabled>
        Pay Deposit — <?= $deposit_fmt ?> (Demo Mode)
      </button>
      <?php else: ?>
      <div style="background:#fef2f2;border:1px solid rgba(229,57,53,.3);padding:16px;font-family:'Museo',sans-serif;font-size:13px;color:#e53935;">
        Could not load payment. Please <a href="https://wa.me/<?= SITE_WA ?>" style="color:#e53935;font-weight:700;">contact us via WhatsApp</a>.
      </div>
      <?php endif; ?>

      <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:20px;line-height:1.7;text-align:center;">
        You'll receive a booking confirmation email immediately after payment.<br>
        No charges before you click Pay.
      </p>
    </div>

    <?php endif; ?>
  </div>
</div>

<?php if ($snap_token && !$demo && !$already_paid): ?>
<script src="<?= htmlspecialchars(MIDTRANS_SNAP_JS) ?>" data-client-key="<?= htmlspecialchars(MIDTRANS_CLIENT_KEY) ?>"></script>
<script>
document.getElementById('pay-btn').addEventListener('click', function() {
  var btn = this;
  btn.textContent = 'Opening payment…';
  btn.disabled = true;

  snap.pay(<?= json_encode($snap_token) ?>, {
    onSuccess: function(result) {
      window.location.href = 'payment-finish.php?ref=<?= urlencode($ref) ?>';
    },
    onPending: function(result) {
      window.location.href = 'payment-finish.php?ref=<?= urlencode($ref) ?>';
    },
    onError: function(result) {
      var err = document.getElementById('pay-error');
      err.style.display = 'block';
      err.textContent = 'Payment error: ' + (result.status_message || 'Unknown error. Please try again.');
      btn.textContent = 'Pay Deposit — <?= addslashes($deposit_fmt) ?>';
      btn.disabled = false;
    },
    onClose: function() {
      btn.textContent = 'Pay Deposit — <?= addslashes($deposit_fmt) ?>';
      btn.disabled = false;
    }
  });
});
</script>
<?php endif; ?>

</body>
</html>
