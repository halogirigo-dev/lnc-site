<?php
session_start();
require_once 'config.php';
require_once 'data.php';

// ── Read from session or use demo data ─────────────────────────
$b = $_SESSION['lnc_booking'] ?? null;

if ($b) {
  $invoice = [
    'ref'          => $b['ref'],
    'guest'        => ['name' => $b['name'], 'email' => $b['email'], 'country' => $b['country']],
    'experience'   => $b['package_id'] . ' — ' . $b['package_title'],
    'dates'        => $b['dates'] ?: 'To be confirmed',
    'duration'     => $b['package_duration'],
    'guests'       => (int)($b['guests'] ?: 1),
    'agent'        => 'Arief Hidayat',
    'issued'       => $b['issued'],
    'expiry'       => $b['expiry'],
    'due_deposit'  => $b['due_deposit'],
    'due_balance'  => $b['due_balance'],
    'deposit_pct'  => 30,
    'accommodation'=> $b['accommodation'] ?: 'To be selected',
    'phone'        => $b['phone'] ?: '',
  ];

  // Build line items
  if ($b['package_price'] > 0) {
    $invoice['items'] = [[
      'desc'   => $b['package_title'] . ' — ' . $b['package_duration'],
      'detail' => implode(', ', array_slice($b['package_includes'], 0, 4)),
      'qty'    => $invoice['guests'],
      'unit'   => $b['package_price'],
      'total'  => $b['package_price'] * $invoice['guests'],
    ]];
    $invoice['total']   = $b['subtotal'];
    $invoice['deposit'] = $b['deposit'];
    $invoice['balance'] = $b['balance'];
    $invoice['has_price'] = true;
  } else {
    // Request Quote packages
    $invoice['items'] = [[
      'desc'   => $b['package_title'] . ' — ' . $b['package_duration'],
      'detail' => implode(', ', array_slice($b['package_includes'], 0, 4)),
      'qty'    => $invoice['guests'],
      'unit'   => 0,
      'total'  => 0,
    ]];
    $invoice['total']     = 0;
    $invoice['deposit']   = 0;
    $invoice['balance']   = 0;
    $invoice['has_price'] = false;
  }
} else {
  // Demo data (no active session)
  $invoice = [
    'ref'          => 'LNC-' . date('Y') . '-DEMO1',
    'guest'        => ['name' => 'Sarah & Tom Müller', 'email' => 'sarah@example.com', 'country' => 'Berlin, Germany'],
    'experience'   => 'LNC-01 — Lombok Signature (3D2N)',
    'dates'        => 'August 12–14, 2026',
    'duration'     => '3 days / 2 nights',
    'guests'       => 2,
    'agent'        => 'Arief Hidayat',
    'issued'       => date('d M Y'),
    'expiry'       => date('d M Y', strtotime('+14 days')),
    'due_deposit'  => date('d M Y', strtotime('+7 days')),
    'due_balance'  => 'Before departure',
    'deposit_pct'  => 30,
    'accommodation'=> 'Comfort (3–4★)',
    'phone'        => '',
    'has_price'    => true,
    'items' => [
      ['desc' => 'Lombok Signature Tour — 3 days, 2 guests', 'detail' => 'Private guide, transport, boat, snorkeling gear', 'qty' => 2, 'unit' => 1873300, 'total' => 3746600],
      ['desc' => 'Reflexology Massage (2 sessions)', 'detail' => '60 min traditional foot massage', 'qty' => 2, 'unit' => 150000, 'total' => 300000],
      ['desc' => 'Private Boat — Gili Nanggu', 'detail' => 'Full day private boat charter', 'qty' => 1, 'unit' => 400000, 'total' => 400000],
      ['desc' => 'Entrance Tickets & Permits', 'detail' => 'Bukit Merese, Tanjung Aan, Desa Sukarara', 'qty' => 2, 'unit' => 75000, 'total' => 150000],
    ],
    'total'   => 4596600,
    'deposit' => 1378980,
    'balance' => 3217620,
  ];
}

$page_title = 'Proposal — ' . $invoice['ref'];
$page_desc  = 'PT Lombok Nature Culture journey proposal for ' . $invoice['guest']['name'];
include 'includes/head.php';
include 'includes/nav.php';

$stages = [
  ['proposal', 'Journey Proposal',  'Stage 1 — Pre-booking'],
  ['deposit',  'Deposit Invoice',   'Stage 2 — Booking Confirmed'],
  ['receipt',  'Final Receipt',     'Stage 3 — Journey Complete'],
];
?>

<!-- Top Bar -->
<div class="no-print" style="background:#fff;border-bottom:1px solid rgba(0,0,0,.08);padding:0 48px;height:72px;display:flex;align-items:center;justify-content:space-between;margin-top:72px;">
  <div>
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;">Booking Ref</p>
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:18px;color:#1a2118;"><?= htmlspecialchars($invoice['ref']) ?></p>
  </div>
  <div style="display:flex;gap:8px;">
    <button id="btn-print" class="btn btn--dark btn--sm">↓ Download PDF</button>
    <a href="index.php" class="btn btn--outline btn--sm">← Back</a>
  </div>
</div>

<!-- Stage Tabs -->
<div class="no-print" style="background:#fff;border-bottom:1px solid rgba(0,0,0,.08);padding:0 48px;">
  <div style="display:flex;max-width:900px;margin:0 auto;">
    <?php foreach ($stages as $i => [$sid, $slabel, $ssub]): ?>
    <button class="inv-tab-btn" style="padding:16px 24px;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;cursor:pointer;border:none;background:transparent;color:#8a7d6e;border-bottom:2px solid transparent;text-align:left;transition:all .2s;">
      <span style="display:block;font-size:9px;letter-spacing:.18em;color:#8a7d6e;margin-bottom:2px;"><?= $ssub ?></span>
      <?= $slabel ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>

<!-- Flow Steps -->
<div class="no-print" style="background:#ede9e1;padding:10px 48px;">
  <div style="display:flex;align-items:center;max-width:900px;margin:0 auto;">
    <?php foreach ($stages as $i => [$sid, $slabel, $ssub]): ?>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
      <div style="width:22px;height:22px;border-radius:50%;background:<?= $i===0?'#1a2118':'#c5b9ad' ?>;display:flex;align-items:center;justify-content:center;font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;color:#fff;"><?= $i+1 ?></div>
      <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:<?= $i===0?'#1a2118':'#8a7d6e' ?>;"><?= $ssub ?></span>
    </div>
    <?php if ($i < count($stages)-1): ?><div style="flex:1;height:1px;background:#c5b9ad;margin:0 12px;"></div><?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>

<!-- Documents -->
<div style="padding:48px 24px;background:#ede9e1;">

  <!-- ══ STAGE 1: PROPOSAL ══════════════════════════════════════ -->
  <div class="inv-tab-panel">
    <div class="doc-wrap">
      <!-- Header -->
      <div class="doc-header">
        <div style="display:flex;align-items:center;gap:14px;">
          <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" style="height:48px;" alt="LNC">
          <div>
            <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:14px;letter-spacing:.18em;text-transform:uppercase;color:#1a2118;"><?= SITE_COMPANY ?></div>
            <div style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:2px;"><?= SITE_EMAIL ?> · <?= SITE_PHONE ?></div>
            <div style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;"><?= SITE_ADDRESS ?></div>
          </div>
        </div>
        <div style="text-align:right;">
          <span class="tag tag--teal" style="margin-bottom:10px;display:inline-block;">PROPOSAL</span>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:13px;color:#1a2118;">PRO-<?= htmlspecialchars($invoice['ref']) ?></div>
          <div style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:4px;">Issued: <?= $invoice['issued'] ?></div>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#2cb896;margin-top:2px;">Valid until <?= $invoice['expiry'] ?></div>
        </div>
      </div>
      <div style="border-top:3px solid;border-image:linear-gradient(to right,#2cb896,#38a8d8) 1;margin-bottom:36px;"></div>

      <!-- Guest & Journey Info -->
      <div class="doc-guest-block">
        <div class="doc-guest-col">
          <span class="doc-meta-label">Prepared For</span>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:16px;color:#1a2118;"><?= htmlspecialchars($invoice['guest']['name']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;margin-top:2px;"><?= htmlspecialchars($invoice['guest']['email']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;"><?= htmlspecialchars($invoice['guest']['country']) ?></p>
        </div>
        <div class="doc-guest-col">
          <span class="doc-meta-label">Journey</span>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:16px;color:#1a2118;"><?= htmlspecialchars($invoice['experience']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;margin-top:2px;"><?= htmlspecialchars($invoice['dates']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;"><?= $invoice['duration'] ?> · <?= $invoice['guests'] ?> guest(s)</p>
          <?php if (!empty($invoice['accommodation'])): ?>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:4px;">Hotel: <?= htmlspecialchars($invoice['accommodation']) ?></p>
          <?php endif; ?>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#2cb896;margin-top:6px;">Valid until <?= $invoice['expiry'] ?></p>
        </div>
      </div>

      <!-- Line Items -->
      <div class="doc-table-head" style="display:grid;grid-template-columns:1fr auto auto;">
        <span>Description</span><span style="text-align:right;">Qty × Unit</span><span style="text-align:right;min-width:120px;">Amount</span>
      </div>
      <?php foreach ($invoice['items'] as $i => $item): ?>
      <div style="display:grid;grid-template-columns:1fr auto auto;padding:16px 20px;border-bottom:1px solid #f0ebe3;gap:24px;background:<?= $i%2===0?'#fff':'#faf7f3' ?>;">
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;"><?= htmlspecialchars($item['desc']) ?></p>
          <?php if (!empty($item['detail'])): ?>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:2px;"><?= htmlspecialchars($item['detail']) ?></p>
          <?php endif; ?>
        </div>
        <?php if ($invoice['has_price']): ?>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;text-align:right;white-space:nowrap;"><?= $item['qty'] ?> × Rp <?= number_format($item['unit'],0,',','.') ?></p>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;text-align:right;min-width:120px;white-space:nowrap;">Rp <?= number_format($item['total'],0,',','.') ?></p>
        <?php else: ?>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;text-align:right;"><?= $item['qty'] ?> guest(s)</p>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#c4964a;text-align:right;min-width:120px;">Quote TBC</p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <!-- Totals -->
      <div style="background:#faf7f3;padding:0 20px;">
        <?php if ($invoice['has_price']): ?>
        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e8e2d8;">
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;">Subtotal</p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;">Rp <?= number_format($invoice['total'],0,',','.') ?></p>
        </div>
        <div style="display:flex;justify-content:space-between;padding:16px 0;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:15px;color:#1a2118;">Total</p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:22px;color:#1a2118;">Rp <?= number_format($invoice['total'],0,',','.') ?> <span style="font-size:13px;font-weight:400;color:#8a7d6e;">IDR</span></p>
        </div>
        <?php else: ?>
        <div style="padding:20px 0;text-align:center;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:14px;color:#c4964a;">Pricing will be confirmed in your personalised proposal within 24–48 hours.</p>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:4px;">Our team will review your requirements and send exact pricing.</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Payment Schedule -->
      <?php if ($invoice['has_price']): ?>
      <div style="background:#f0faf7;padding:20px 24px;border-left:3px solid #2cb896;margin:24px 0;">
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#2cb896;margin-bottom:12px;">Payment Schedule</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div>
            <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;">Deposit (<?= $invoice['deposit_pct'] ?>%) to confirm booking</p>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#1a2118;margin-top:4px;">Rp <?= number_format($invoice['deposit'],0,',','.') ?></p>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#8a7d6e;">Due by <?= $invoice['due_deposit'] ?></p>
          </div>
          <div>
            <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;">Balance (<?= 100-$invoice['deposit_pct'] ?>%) before departure</p>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#1a2118;margin-top:4px;">Rp <?= number_format($invoice['balance'],0,',','.') ?></p>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#8a7d6e;">Due by <?= $invoice['due_balance'] ?></p>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Footer -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:24px;border-top:1px solid #e0d8ce;">
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#8a7d6e;">Prepared by</p>
          <p style="font-family:'Museo',sans-serif;font-style:italic;font-size:20px;color:#1a2118;margin-top:4px;"><?= $invoice['agent'] ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;">Lead Guide & Founder, <?= SITE_COMPANY ?></p>
        </div>
        <div style="text-align:right;">
          <?php if ($invoice['has_price']): ?>
          <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi, I'd like to confirm my booking. Ref: {$invoice['ref']} — {$invoice['experience']}") ?>"
             target="_blank" class="btn btn--primary">Confirm via WhatsApp →</a>
          <?php else: ?>
          <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi, I'd like to discuss pricing for my request. Ref: {$invoice['ref']} — {$invoice['experience']}") ?>"
             target="_blank" class="btn btn--primary">Discuss Pricing →</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ STAGE 2: DEPOSIT INVOICE ══════════════════════════════ -->
  <div class="inv-tab-panel">
    <div class="doc-wrap">
      <div class="doc-header">
        <div style="display:flex;align-items:center;gap:14px;">
          <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" style="height:48px;" alt="LNC">
          <div>
            <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:14px;letter-spacing:.18em;text-transform:uppercase;color:#1a2118;"><?= SITE_COMPANY ?></div>
            <div style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:2px;"><?= SITE_EMAIL ?></div>
          </div>
        </div>
        <div style="text-align:right;">
          <span class="tag tag--gold" style="margin-bottom:10px;display:inline-block;">DEPOSIT INVOICE</span>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:13px;color:#1a2118;">INV-<?= htmlspecialchars($invoice['ref']) ?>-D</div>
          <div style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:4px;">Issued: <?= $invoice['issued'] ?></div>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#c4964a;margin-top:2px;">Due by <?= $invoice['due_deposit'] ?></div>
        </div>
      </div>
      <div style="border-top:3px solid #c4964a;margin-bottom:36px;"></div>

      <div class="doc-guest-block">
        <div class="doc-guest-col">
          <span class="doc-meta-label">Billed To</span>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:16px;color:#1a2118;"><?= htmlspecialchars($invoice['guest']['name']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;margin-top:2px;"><?= htmlspecialchars($invoice['guest']['email']) ?></p>
        </div>
        <div class="doc-guest-col">
          <span class="doc-meta-label">Journey</span>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:16px;color:#1a2118;"><?= htmlspecialchars($invoice['experience']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;margin-top:2px;"><?= $invoice['dates'] ?></p>
        </div>
      </div>

      <?php if ($invoice['has_price']): ?>
      <div class="doc-table-head" style="display:grid;grid-template-columns:1fr auto auto;">
        <span>Description</span><span style="text-align:right;">Percentage</span><span style="text-align:right;min-width:120px;">Amount Due</span>
      </div>
      <div style="background:#fff;padding:20px;border-bottom:1px solid #f0ebe3;display:grid;grid-template-columns:1fr auto auto;gap:24px;">
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;">Booking Deposit — <?= htmlspecialchars($invoice['experience']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;margin-top:2px;"><?= $invoice['dates'] ?> · Ref: PRO-<?= htmlspecialchars($invoice['ref']) ?></p>
        </div>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;text-align:right;"><?= $invoice['deposit_pct'] ?>%</p>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:20px;color:#1a2118;text-align:right;min-width:120px;">Rp <?= number_format($invoice['deposit'],0,',','.') ?></p>
      </div>
      <div style="background:#faf7f3;padding:16px 20px;display:flex;justify-content:space-between;align-items:flex-end;">
        <div>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;">Remaining balance due <?= $invoice['due_balance'] ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#8a7d6e;margin-top:2px;">Balance: Rp <?= number_format($invoice['balance'],0,',','.') ?></p>
        </div>
        <div style="text-align:right;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;">Total Due Now</p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:28px;color:#1a2118;">Rp <?= number_format($invoice['deposit'],0,',','.') ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;color:#c4964a;">Due by <?= $invoice['due_deposit'] ?></p>
        </div>
      </div>
      <?php else: ?>
      <div style="background:#fff;padding:28px;text-align:center;border:1px solid #e0d8ce;">
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:14px;color:#c4964a;margin-bottom:8px;">Pricing to be confirmed</p>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;">Our team will send deposit details once we confirm the pricing for your package.</p>
      </div>
      <?php endif; ?>

      <!-- Payment Methods -->
      <div style="background:#ede9e1;padding:20px 24px;margin-top:24px;">
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:14px;">Payment Methods</p>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px;">
          <?php foreach ([['Bank Transfer','BCA / Mandiri'],['Credit Card','Visa / Mastercard'],['PayPal','paypal.me/lnc'],['Wise','@lnc-travel']] as [$m,$d]): ?>
          <div style="background:#fff;padding:14px;border:1px solid #e0d8ce;">
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:12px;color:#1a2118;margin-bottom:2px;"><?= $m ?></p>
            <p style="font-family:'Museo',sans-serif;font-size:11px;color:#8a7d6e;"><?= $d ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="background:#fff;padding:12px 16px;border:1px solid #e0d8ce;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:#8a7d6e;margin-bottom:6px;">Bank Transfer Details</p>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <?php foreach ([['Bank','Bank Central Asia (BCA)'],['Account Name',SITE_COMPANY],['Account No.','1234567890']] as [$k,$v]): ?>
            <div>
              <p style="font-family:'Museo',sans-serif;font-size:11px;color:#8a7d6e;"><?= $k ?></p>
              <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;"><?= $v ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Send payment proof via WhatsApp -->
      <div style="margin-top:24px;padding:20px 24px;background:#f0faf7;border:1px solid rgba(44,184,150,.2);display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;margin-bottom:4px;">After transferring, send proof of payment</p>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;">Include reference <strong style="color:#1a2118;">INV-<?= htmlspecialchars($invoice['ref']) ?>-D</strong> in your transfer description.</p>
        </div>
        <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi, I've transferred the deposit for booking ref INV-{$invoice['ref']}-D. Please find my payment proof attached.") ?>"
           target="_blank" class="btn btn--primary btn--sm" style="white-space:nowrap;">
          💬 Send Payment Proof
        </a>
      </div>
    </div>
  </div>

  <!-- ══ STAGE 3: FINAL RECEIPT ═════════════════════════════════ -->
  <div class="inv-tab-panel">
    <div class="doc-wrap">
      <div class="doc-header">
        <div style="display:flex;align-items:center;gap:14px;">
          <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" style="height:48px;" alt="LNC">
          <div>
            <div style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:14px;letter-spacing:.18em;text-transform:uppercase;color:#1a2118;"><?= SITE_COMPANY ?></div>
          </div>
        </div>
        <div style="text-align:right;">
          <span class="tag" style="background:#2a7a52;color:#fff;margin-bottom:10px;display:inline-block;">PAID IN FULL</span>
          <div style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:13px;color:#1a2118;">RCP-<?= htmlspecialchars($invoice['ref']) ?></div>
        </div>
      </div>
      <div style="border-top:3px solid #2a7a52;margin-bottom:36px;"></div>
      <div class="doc-guest-block">
        <div class="doc-guest-col">
          <span class="doc-meta-label">Billed To</span>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:16px;color:#1a2118;"><?= htmlspecialchars($invoice['guest']['name']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;margin-top:2px;"><?= htmlspecialchars($invoice['guest']['email']) ?></p>
        </div>
        <div class="doc-guest-col">
          <span class="doc-meta-label">Journey</span>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:16px;color:#1a2118;"><?= htmlspecialchars($invoice['experience']) ?></p>
          <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;margin-top:2px;"><?= $invoice['dates'] ?></p>
        </div>
      </div>

      <div class="doc-table-head" style="display:grid;grid-template-columns:1fr auto auto;">
        <span>Description</span><span style="text-align:right;">Qty × Unit</span><span style="text-align:right;min-width:120px;">Amount</span>
      </div>
      <?php foreach ($invoice['items'] as $i => $item): ?>
      <div style="display:grid;grid-template-columns:1fr auto auto;padding:16px 20px;border-bottom:1px solid #f0ebe3;gap:24px;background:<?= $i%2===0?'#fff':'#faf7f3' ?>;">
        <div>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;"><?= htmlspecialchars($item['desc']) ?></p>
          <?php if (!empty($item['detail'])): ?>
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;"><?= htmlspecialchars($item['detail']) ?></p>
          <?php endif; ?>
        </div>
        <?php if ($invoice['has_price']): ?>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;text-align:right;white-space:nowrap;"><?= $item['qty'] ?> × Rp <?= number_format($item['unit'],0,',','.') ?></p>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#1a2118;text-align:right;min-width:120px;">Rp <?= number_format($item['total'],0,',','.') ?></p>
        <?php else: ?>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:#8a7d6e;text-align:right;"><?= $item['qty'] ?> guest(s)</p>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#2a7a52;text-align:right;min-width:120px;">Paid ✓</p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>

      <?php if ($invoice['has_price']): ?>
      <div style="background:#faf7f3;padding:0 20px;">
        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e8e2d8;">
          <p style="font-family:'Museo',sans-serif;font-size:12px;color:#8a7d6e;">Deposit Paid (<?= $invoice['deposit_pct'] ?>%)</p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:13px;color:#2cb896;">−Rp <?= number_format($invoice['deposit'],0,',','.') ?></p>
        </div>
        <div style="display:flex;justify-content:space-between;padding:16px 0;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:15px;color:#1a2118;">Total Paid</p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:22px;color:#2a7a52;">Rp <?= number_format($invoice['total'],0,',','.') ?> ✓</p>
        </div>
      </div>
      <?php endif; ?>

      <div style="text-align:center;padding:32px;background:#1a2118;margin-top:24px;">
        <p style="font-family:'Museo',sans-serif;font-style:italic;font-size:22px;color:rgba(255,255,255,.8);margin-bottom:8px;">"Thank you for journeying with us."</p>
        <p style="font-family:'Museo',sans-serif;font-size:13px;color:rgba(255,255,255,.45);margin-bottom:20px;">We hope your experience with Lombok Nature Culture was everything you imagined.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
          <a href="booking.php" class="btn btn--primary btn--sm">Plan Your Next Journey</a>
          <a href="https://wa.me/<?= SITE_WA ?>?text=<?= urlencode("Hi LNC! I just completed my journey (Ref: {$invoice['ref']}) and wanted to leave a review.") ?>"
             target="_blank" class="btn btn--outline-light btn--sm">Leave a Review</a>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
