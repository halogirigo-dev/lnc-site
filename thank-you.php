<?php
require_once 'config.php';
require_once 'db.php';

$ref  = preg_replace('/[^A-Z0-9\-]/', '', strtoupper(trim($_GET['ref'] ?? '')));
$type = $_GET['type'] ?? '';

// Load booking: DB first, session fallback
$booking = $ref ? lnc_get_booking($ref) : null;

if (!$booking && isset($_SESSION['lnc_booking'])) {
  $sess = $_SESSION['lnc_booking'];
  $sref = $sess['ref'] ?? '';
  if (!$ref || $ref === $sref) {
    $ref = $sref;
    $booking = [
      'ref'             => $sref,
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
    ];
    // Infer status from URL type param when coming from session
    if ($type === 'quote' || ($booking['total_amount'] == 0)) {
      $booking['status'] = 'quote';
    }
  }
}

if (!$booking) {
  header('Location: booking.php');
  exit;
}

$status        = $booking['status'];
$is_quote      = ($type === 'quote' || $status === 'quote' || (int)$booking['total_amount'] === 0);
$is_fully_paid = in_array($status, ['balance_paid', 'confirmed']);
$is_deposit    = ($status === 'deposit_paid');

// Determine variant
if ($is_quote) {
  $variant = 'quote';
} elseif ($is_fully_paid) {
  $variant = 'full';
} elseif ($is_deposit) {
  $variant = 'deposit';
} else {
  $variant = 'pending';
}

$deposit_fmt = 'Rp ' . number_format($booking['deposit_amount'] ?? 0, 0, ',', '.');
$balance_fmt = 'Rp ' . number_format($booking['balance_amount'] ?? 0, 0, ',', '.');
$total_fmt   = 'Rp ' . number_format($booking['total_amount'] ?? 0,   0, ',', '.');

$page_title  = match($variant) {
  'full'    => 'Journey Confirmed — ' . $ref,
  'deposit' => 'Deposit Confirmed — ' . $ref,
  'quote'   => 'Request Received — ' . $ref,
  default   => 'Request Received — ' . $ref,
};

include 'includes/head.php';
include 'includes/nav.php';
?>

<div style="min-height:100vh;background:#f7f4ee;padding:120px 24px 80px;display:flex;align-items:center;justify-content:center;">
<div style="max-width:660px;width:100%;">

<?php if ($variant === 'full'): ?>
<!-- ══ VARIANT C — Fully Paid ══════════════════════════════════ -->
<div style="text-align:center;margin-bottom:36px;">
  <div style="width:80px;height:80px;border-radius:50%;background:#f0fdf4;border:2px solid #2a7a52;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;">🎉</div>
  <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#2a7a52;display:block;margin-bottom:10px;">Fully Paid</span>
  <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:38px;color:#1a2118;line-height:1.1;margin-bottom:12px;">Your Journey is<br>Confirmed!</h1>
  <p style="font-family:'Museo',sans-serif;font-size:15px;color:#8a7d6e;line-height:1.8;max-width:440px;margin:0 auto;">Full payment received. Your guide will meet you at Lombok International Airport (LOP).</p>
</div>

<div style="background:#fff;border:1px solid #e0d8ce;padding:28px 32px;margin-bottom:24px;">
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;margin-bottom:6px;">Booking Reference</p>
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:28px;color:#1a2118;letter-spacing:.06em;margin-bottom:20px;"><?= htmlspecialchars($ref) ?></p>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;border-top:1px solid #f0ebe3;padding-top:20px;">
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Package</p>
    <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['package_title']) ?></p></div>
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Total Paid</p>
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:15px;color:#2a7a52;"><?= $total_fmt ?> ✓</p></div>
  </div>
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
  <a href="invoice.php?ref=<?= urlencode($ref) ?>" class="btn btn--primary">View Receipt →</a>
  <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi LNC! Full payment confirmed. Ref: {$ref}. Looking forward to the journey!") ?>"
     target="_blank" class="btn btn--outline" style="border-color:#25d366;color:#25d366;">💬 WhatsApp Us</a>
</div>

<?php elseif ($variant === 'deposit'): ?>
<!-- ══ VARIANT A — Deposit Paid ════════════════════════════════ -->
<div style="text-align:center;margin-bottom:36px;">
  <div style="width:80px;height:80px;border-radius:50%;background:#f0faf7;border:2px solid #2cb896;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;">✓</div>
  <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#2cb896;display:block;margin-bottom:10px;">Deposit Confirmed</span>
  <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:38px;color:#1a2118;line-height:1.1;margin-bottom:12px;">Your Booking is<br>Secured!</h1>
  <p style="font-family:'Museo',sans-serif;font-size:15px;color:#8a7d6e;line-height:1.8;max-width:440px;margin:0 auto;">Your guide is reserved. Pay the balance before your departure to complete the booking.</p>
</div>

<!-- Reference card -->
<div style="background:#fff;border:1px solid #e0d8ce;padding:28px 32px;margin-bottom:16px;">
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;margin-bottom:6px;">Booking Reference</p>
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:28px;color:#1a2118;letter-spacing:.06em;margin-bottom:16px;"><?= htmlspecialchars($ref) ?></p>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;border-top:1px solid #f0ebe3;padding-top:16px;">
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Package</p>
    <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['package_id'] . ' — ' . $booking['package_title']) ?></p></div>
    <?php if (!empty($booking['dates'])): ?>
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Dates</p>
    <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['dates']) ?></p></div>
    <?php endif; ?>
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Deposit Paid</p>
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:15px;color:#2cb896;"><?= $deposit_fmt ?> ✓</p></div>
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Balance Due</p>
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:15px;color:#c4964a;"><?= $balance_fmt ?></p></div>
  </div>
</div>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
  <a href="payment-balance.php?ref=<?= urlencode($ref) ?>" class="btn btn--gold">Pay Balance Now →</a>
  <a href="invoice.php?ref=<?= urlencode($ref) ?>" class="btn btn--dark">View Invoice</a>
  <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi LNC! Deposit confirmed. Ref: {$ref}.") ?>"
     target="_blank" class="btn btn--outline" style="border-color:#25d366;color:#25d366;">💬 WhatsApp</a>
</div>

<!-- What Happens Next -->
<div style="background:#1a2118;padding:28px 32px;">
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#2cb896;margin-bottom:16px;">What Happens Next</p>
  <?php foreach ([
    ['Guide assigned within 24h', 'Your personal guide will contact you to introduce themselves and confirm logistics.'],
    ['Detailed itinerary emailed', 'A full day-by-day itinerary will arrive in your inbox within 48 hours.'],
    ['Pay balance before departure', 'Complete the remaining 70% anytime before your journey begins.'],
    ['We pick you up at LOP ✈', 'Your guide will be waiting at Lombok International Airport on arrival day.'],
  ] as $i => [$step, $desc]): ?>
  <div style="display:flex;gap:16px;margin-bottom:<?= $i < 3 ? '16px' : '0' ?>;">
    <div style="width:24px;height:24px;border-radius:50%;background:#2cb896;display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;color:#fff;flex-shrink:0;margin-top:2px;"><?= $i+1 ?></div>
    <div>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#fff;margin-bottom:2px;"><?= $step ?></p>
      <p style="font-family:'Museo',sans-serif;font-size:12px;color:rgba(255,255,255,.4);line-height:1.6;"><?= $desc ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php elseif ($variant === 'quote'): ?>
<!-- ══ VARIANT B — Request Quote ══════════════════════════════ -->
<div style="text-align:center;margin-bottom:36px;">
  <div style="width:80px;height:80px;border-radius:50%;background:#f0faf7;border:2px solid #2cb896;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;">✓</div>
  <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#2cb896;display:block;margin-bottom:10px;">Request Received</span>
  <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:38px;color:#1a2118;line-height:1.1;margin-bottom:12px;">Your Journey is<br>In Our Hands.</h1>
  <p style="font-family:'Museo',sans-serif;font-size:15px;color:#8a7d6e;line-height:1.8;max-width:440px;margin:0 auto;">Our team personally reviews every request and sends a bespoke proposal within <strong style="color:#1a2118;">24–48 hours</strong>.</p>
</div>

<div style="background:#fff;border:1px solid #e0d8ce;padding:28px 32px;margin-bottom:24px;">
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;margin-bottom:6px;">Your Reference Number</p>
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:28px;color:#1a2118;letter-spacing:.06em;"><?= htmlspecialchars($ref) ?></p>
  <?php if (!empty($booking['package_title'])): ?>
  <div style="border-top:1px solid #f0ebe3;margin-top:16px;padding-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Package</p>
    <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['package_id'] . ' — ' . $booking['package_title']) ?></p></div>
    <?php if (!empty($booking['name'])): ?>
    <div><p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Name</p>
    <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['name']) ?></p></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- What Happens Next -->
<div style="background:#1a2118;padding:28px 32px;margin-bottom:24px;">
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#2cb896;margin-bottom:16px;">What Happens Next</p>
  <?php foreach ([
    ['Within 24–48 hours', 'Our journey designer reviews your request and crafts a personalised itinerary.'],
    ['Proposal sent', 'You\'ll receive detailed itinerary and pricing via email and WhatsApp.'],
    ['Confirm & deposit', 'Confirm your journey with a 30% deposit — balance due before departure.'],
    ['Your journey begins', 'We handle everything. You just show up and experience Lombok.'],
  ] as $i => [$step, $desc]): ?>
  <div style="display:flex;gap:16px;margin-bottom:<?= $i < 3 ? '16px' : '0' ?>;">
    <div style="width:24px;height:24px;border-radius:50%;background:#2cb896;display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;color:#fff;flex-shrink:0;margin-top:2px;"><?= $i+1 ?></div>
    <div>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#fff;margin-bottom:2px;"><?= $step ?></p>
      <p style="font-family:'Museo',sans-serif;font-size:12px;color:rgba(255,255,255,.4);line-height:1.6;"><?= $desc ?></p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:12px;">
  <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi LNC! I just submitted a journey request. Ref: {$ref}. Looking forward to your proposal!") ?>"
     target="_blank" class="btn btn--outline" style="border-color:#25d366;color:#25d366;">💬 WhatsApp Us</a>
  <a href="invoice.php?ref=<?= urlencode($ref) ?>" class="btn btn--outline btn--sm">View My Proposal</a>
</div>

<?php else: ?>
<!-- ══ PENDING / FALLBACK ══════════════════════════════════════ -->
<div style="text-align:center;margin-bottom:36px;">
  <div style="width:80px;height:80px;border-radius:50%;background:#f0faf7;border:2px solid #2cb896;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 20px;">✓</div>
  <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#2cb896;display:block;margin-bottom:10px;">Request Received</span>
  <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:38px;color:#1a2118;line-height:1.1;margin-bottom:12px;">Your Journey is<br>In Our Hands.</h1>
  <p style="font-family:'Museo',sans-serif;font-size:15px;color:#8a7d6e;line-height:1.8;max-width:440px;margin:0 auto;">Our team will review your request and send a bespoke itinerary within <strong style="color:#1a2118;">24–48 hours</strong>.</p>
</div>
<div style="background:#fff;border:1px solid #e0d8ce;padding:28px 32px;margin-bottom:24px;text-align:center;">
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;margin-bottom:8px;">Your Reference</p>
  <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:28px;color:#1a2118;letter-spacing:.06em;"><?= htmlspecialchars($ref) ?></p>
</div>
<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
  <a href="invoice.php?ref=<?= urlencode($ref) ?>" class="btn btn--primary">View My Proposal →</a>
  <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi LNC, I submitted a request. Ref: {$ref}.") ?>"
     target="_blank" class="btn btn--outline" style="border-color:#25d366;color:#25d366;">💬 WhatsApp Us</a>
</div>
<?php endif; ?>

<p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:32px;text-align:center;">
  Reference: <strong style="color:#1a2118;"><?= htmlspecialchars($ref) ?></strong>
  <?php if (!empty($booking['email'])): ?>
  · Confirmation sent to <?= htmlspecialchars($booking['email']) ?>
  <?php endif; ?>
</p>

</div>
</div>

<?php include 'includes/footer.php'; ?>
