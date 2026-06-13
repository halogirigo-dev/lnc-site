<?php
require_once 'config.php';

$booking = $_SESSION['lnc_booking'] ?? null;
$ref     = $_GET['ref'] ?? ($booking['ref'] ?? 'LNC-XXXX');

$page_title = 'Request Received — ' . $ref;
include 'includes/head.php';
include 'includes/nav.php';
?>

<div style="min-height:100vh;background:#f7f4ee;padding:120px 48px 80px;display:flex;align-items:center;justify-content:center;">
  <div style="max-width:640px;width:100%;text-align:center;">

    <!-- Success icon -->
    <div style="width:80px;height:80px;border-radius:50%;background:#f0faf7;border:2px solid #2cb896;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 32px;">✓</div>

    <!-- Heading -->
    <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;letter-spacing:.28em;text-transform:uppercase;color:#2cb896;display:block;margin-bottom:12px;">Request Received</span>
    <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:42px;color:#1a2118;line-height:1.1;margin-bottom:16px;">Your Journey is<br>in Our Hands.</h1>
    <p style="font-family:'Museo',sans-serif;font-size:16px;color:#8a7d6e;line-height:1.8;margin-bottom:40px;max-width:480px;margin-left:auto;margin-right:auto;">
      Our team personally reviews every request and sends a bespoke itinerary within <strong style="color:#1a2118;">24–48 hours</strong> via email and WhatsApp.
    </p>

    <!-- Booking reference card -->
    <div style="background:#fff;border:1px solid #e0d8ce;padding:28px 32px;margin-bottom:32px;text-align:left;">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#8a7d6e;margin-bottom:6px;">Your Reference Number</p>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:28px;color:#1a2118;letter-spacing:.06em;"><?= htmlspecialchars($ref) ?></p>
      <?php if ($booking): ?>
      <div style="border-top:1px solid #f0ebe3;margin-top:20px;padding-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <?php if (!empty($booking['package_title'])): ?>
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Package</p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['package_id'] . ' — ' . $booking['package_title']) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($booking['dates'])): ?>
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Requested Dates</p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['dates']) ?></p>
        </div>
        <?php endif; ?>
        <?php if (!empty($booking['guests'])): ?>
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Guests</p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['guests']) ?> guest(s)</p>
        </div>
        <?php endif; ?>
        <?php if (!empty($booking['name'])): ?>
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Name</p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#1a2118;"><?= htmlspecialchars($booking['name']) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Next steps -->
    <div style="background:#1a2118;padding:28px 32px;margin-bottom:32px;text-align:left;">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.22em;text-transform:uppercase;color:#2cb896;margin-bottom:16px;">What Happens Next</p>
      <?php foreach ([
        ['Within 24–48 hours',  'Our journey designer reviews your request and crafts a personalised itinerary.'],
        ['Proposal sent',       'You\'ll receive a detailed proposal and pricing via email and WhatsApp.'],
        ['Confirm & deposit',   'Confirm your journey with a 30% deposit — balance due before departure.'],
        ['Your journey begins', 'We handle everything. You just show up and experience Lombok.'],
      ] as $i => [$step, $desc]): ?>
      <div style="display:flex;gap:16px;margin-bottom:<?= $i < 3 ? '16px' : '0' ?>;">
        <div style="width:24px;height:24px;border-radius:50%;background:#2cb896;display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;color:#fff;flex-shrink:0;margin-top:2px;"><?= $i+1 ?></div>
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#fff;margin-bottom:2px;"><?= $step ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:rgba(255,255,255,.45);line-height:1.6;"><?= $desc ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CTAs -->
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:24px;">
      <a href="invoice.php" class="btn btn--primary">View My Proposal →</a>
      <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi, I just submitted a journey request. My reference number is {$ref}. Looking forward to hearing from you!") ?>"
         target="_blank" class="btn btn--outline" style="border-color:#25d366;color:#25d366;">
        💬 WhatsApp Us
      </a>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="index.php" class="btn btn--outline btn--sm">Back to Homepage</a>
      <a href="experiences.php" class="btn btn--outline btn--sm">Browse More Packages</a>
    </div>

    <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:32px;">
      Save your reference number: <strong style="color:#1a2118;"><?= htmlspecialchars($ref) ?></strong><br>
      A confirmation has been sent to <?= $booking ? htmlspecialchars($booking['email']) : 'your email' ?>.
    </p>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
