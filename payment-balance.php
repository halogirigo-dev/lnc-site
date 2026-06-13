<?php
require_once 'config.php';
require_once 'data.php';
require_once 'db.php';
require_once 'lib/midtrans.php';

$ref = preg_replace('/[^A-Z0-9\-]/', '', strtoupper(trim($_GET['ref'] ?? '')));

$booking = lnc_get_booking($ref);

if (!$booking) {
  header('Location: booking.php');
  exit;
}

// Only accessible if deposit already paid
if ($booking['status'] !== 'deposit_paid') {
  if (in_array($booking['status'], ['balance_paid', 'confirmed'])) {
    header('Location: thank-you.php?ref=' . urlencode($ref));
  } else {
    header('Location: payment.php?ref=' . urlencode($ref));
  }
  exit;
}

// Check if balance payment record already has a snap token
$db = lnc_db();
$snap_token = $db ? lnc_get_snap_token_from_db($ref, 'balance') : null;

// Generate new snap token for balance if needed
if (!$snap_token) {
  $snap_params = lnc_format_snap_params($booking, 'balance');
  $snap_result = lnc_get_snap_token($snap_params);

  if (!isset($snap_result['error'])) {
    $snap_token = $snap_result['token'];

    if ($db) {
      try {
        $db->prepare("
          INSERT INTO payments (booking_ref, payment_type, amount, midtrans_order_id, snap_token)
          VALUES (:ref, 'balance', :amount, :oid, :token)
        ")->execute([
          ':ref'    => $ref,
          ':amount' => $booking['balance_amount'],
          ':oid'    => $ref . '-BAL',
          ':token'  => $snap_token,
        ]);
      } catch (PDOException $e) {}
    }
  }
}

$demo        = lnc_is_demo_mode();
$balance_fmt = 'Rp ' . number_format($booking['balance_amount'], 0, ',', '.');
$deposit_fmt = 'Rp ' . number_format($booking['deposit_amount'], 0, ',', '.');
$total_fmt   = 'Rp ' . number_format($booking['total_amount'],   0, ',', '.');

$page_title = 'Pay Remaining Balance — ' . $ref;
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
.pay-left{background:#1a2118;padding:56px 48px;display:flex;flex-direction:column}
.pay-right{background:#fff;padding:56px 48px;display:flex;flex-direction:column;justify-content:center}
.pay-topbar{background:#fff;border-bottom:1px solid #e0d8ce;padding:0 48px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10}
.pay-divider{height:1px;background:rgba(255,255,255,.1);margin:24px 0}
.pay-label{font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px}
.demo-banner{background:#fffbe6;border:1px solid #f5c842;padding:16px 20px;margin-bottom:28px;border-left:4px solid #f5c842}
.paid-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.08)}
@media(max-width:768px){
  .pay-wrap{grid-template-columns:1fr}
  .pay-left,.pay-right{padding:36px 24px}
}
</style>
</head>
<body>

<div class="pay-topbar">
  <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
    <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" style="height:36px;" alt="<?= SITE_NAME ?>">
    <div>
      <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:#1a2118;">Lombok Nature</div>
      <div style="font-family:'MuseoModerno',sans-serif;font-size:10px;color:#8a7d6e;line-height:1;">Culture</div>
    </div>
  </a>
  <a href="thank-you.php?ref=<?= urlencode($ref) ?>" class="btn btn--outline btn--sm">← Back</a>
</div>

<div class="pay-wrap">
  <!-- Left: Booking Summary -->
  <div class="pay-left">
    <div style="margin-bottom:32px;">
      <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:rgba(255,255,255,.35);">Final Payment</span>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#fff;margin-top:8px;line-height:1.2;"><?= htmlspecialchars($booking['package_title']) ?></p>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:#2cb896;margin-top:4px;"><?= htmlspecialchars($booking['package_id']) ?></p>
    </div>

    <div class="pay-divider"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
      <div>
        <div class="pay-label">Duration</div>
        <div style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:14px;color:#fff;"><?= htmlspecialchars($booking['package_duration']) ?></div>
      </div>
      <div>
        <div class="pay-label">Guests</div>
        <div style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:14px;color:#fff;"><?= (int)$booking['guests'] ?> guest<?= (int)$booking['guests'] > 1 ? 's' : '' ?></div>
      </div>
    </div>

    <div class="pay-divider"></div>

    <div>
      <div class="paid-row">
        <div>
          <div class="pay-label">Deposit Paid (30%)</div>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:16px;color:rgba(255,255,255,.5);"><?= $deposit_fmt ?></div>
        </div>
        <span style="color:#2cb896;font-size:18px;align-self:center;">✓</span>
      </div>
      <div class="paid-row">
        <div>
          <div class="pay-label">Balance Due (70%)</div>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:24px;color:#c4964a;"><?= $balance_fmt ?></div>
        </div>
        <span style="font-family:'MuseoModerno',sans-serif;font-size:10px;color:#c4964a;align-self:center;">DUE NOW</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;">
        <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;color:rgba(255,255,255,.3);">Total package</span>
        <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:rgba(255,255,255,.4);"><?= $total_fmt ?></span>
      </div>
    </div>

    <div class="pay-divider"></div>

    <div style="display:flex;align-items:center;gap:8px;margin-top:auto;">
      <span style="color:rgba(44,184,150,.7);font-size:16px;">🔒</span>
      <span style="font-family:'MuseoModerno',sans-serif;font-size:11px;color:rgba(255,255,255,.3);">Secured by Midtrans · SSL · PCI-DSS</span>
    </div>
  </div>

  <!-- Right: Payment Action -->
  <div class="pay-right">
    <?php if ($demo): ?>
    <div class="demo-banner">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#856404;margin-bottom:6px;">⚠️ DEMO MODE</p>
      <p style="font-family:'Museo',sans-serif;font-size:13px;color:#856404;margin-bottom:10px;">No real payment will be charged.</p>
      <a href="payment-callback.php?simulate=paid&ref=<?= urlencode($ref) ?>" class="btn btn--dark btn--sm">Simulate Balance Payment →</a>
    </div>
    <?php endif; ?>

    <div>
      <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;display:block;margin-bottom:10px;">Step 2 of 2 — Final Payment</span>
      <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:32px;color:#1a2118;line-height:1.1;margin-bottom:16px;">Pay Remaining Balance</h1>
      <p style="font-family:'Museo',sans-serif;font-size:15px;color:#8a7d6e;line-height:1.8;margin-bottom:32px;">
        This is the final 70% payment. Once complete, your journey is fully confirmed and your guide team will reach out with final logistics.
      </p>

      <div style="background:#fdf6ec;border:1px solid rgba(196,150,74,.3);padding:16px 20px;margin-bottom:32px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#1a2118;">Balance Due</span>
          <span style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:22px;color:#c4964a;"><?= $balance_fmt ?></span>
        </div>
        <div style="font-family:'Museo',sans-serif;font-size:11px;color:#8a7d6e;margin-top:4px;">Ref: <?= htmlspecialchars($ref) ?> · Final payment</div>
      </div>

      <?php if ($snap_token && !$demo): ?>
      <button id="pay-btn" class="btn btn--gold btn--full" style="font-size:14px;padding:18px 32px;">
        Pay Balance — <?= $balance_fmt ?>
      </button>
      <div id="pay-error" style="display:none;background:#fef2f2;border:1px solid rgba(229,57,53,.3);padding:12px 16px;margin-top:12px;font-family:'Museo',sans-serif;font-size:13px;color:#e53935;"></div>
      <?php elseif ($demo): ?>
      <button class="btn btn--gold btn--full" style="font-size:14px;padding:18px 32px;opacity:.4;cursor:not-allowed;" disabled>
        Pay Balance — <?= $balance_fmt ?> (Demo Mode)
      </button>
      <?php else: ?>
      <div style="background:#fef2f2;border:1px solid rgba(229,57,53,.3);padding:16px;font-family:'Museo',sans-serif;font-size:13px;color:#e53935;">
        Could not load payment. <a href="https://wa.me/<?= SITE_WA ?>" style="color:#e53935;font-weight:700;">Contact us via WhatsApp</a>.
      </div>
      <?php endif; ?>

      <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:20px;line-height:1.7;text-align:center;">
        You'll receive a journey confirmation email immediately after this payment.
      </p>
    </div>
  </div>
</div>

<?php if ($snap_token && !$demo): ?>
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
      btn.textContent = 'Pay Balance — <?= addslashes($balance_fmt) ?>';
      btn.disabled = false;
    },
    onClose: function() {
      btn.textContent = 'Pay Balance — <?= addslashes($balance_fmt) ?>';
      btn.disabled = false;
    }
  });
});
</script>
<?php endif; ?>

</body>
</html>
