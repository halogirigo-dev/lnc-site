<?php
require_once 'config.php';
require_once 'data.php';
$page_title = 'Hotel Partners';
$page_desc  = 'Curated accommodation partners of PT Lombok Nature Culture across all zones of Lombok and the Gili Islands.';
include 'includes/head.php';
include 'includes/nav.php';
?>

<div style="background:#1a2118;padding:120px 72px 48px;margin-top:72px;">
  <div class="container">
    <span class="eyebrow" style="color:rgba(255,255,255,.4);">Where You'll Stay</span>
    <h1 class="section-title section-title--light" style="font-size:52px;margin-bottom:12px;">Hotel Partners</h1>
    <p class="section-body" style="color:rgba(255,255,255,.5);max-width:560px;line-height:1.8;">
      We partner with <?= array_sum(array_map(fn($z)=>count($z['properties']),$hotels)) ?> handpicked properties across 4 zones of Lombok and the Gili Islands.
      Hotel pricing is <strong style="color:#fff;">separate</strong> from our tour packages — book through us for seamless coordination.
    </p>
  </div>
</div>

<!-- Policy Alert -->
<div style="background:#f0faf7;border-bottom:3px solid #2cb896;padding:16px 48px;">
  <div class="container" style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
    <div style="flex:1;min-width:260px;">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#2cb896;margin-bottom:4px;">Low Season</p>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:13px;color:#3d3228;">January–March, May, October–early December</p>
    </div>
    <div style="flex:1;min-width:260px;">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#c4964a;margin-bottom:4px;">High Season</p>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:13px;color:#3d3228;">July–September, Eid (H-7 to H+7), Christmas & New Year (20 Dec–5 Jan)</p>
    </div>
    <div style="flex:1;min-width:260px;">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#8a7d6e;margin-bottom:4px;">Driver Room</p>
      <p style="font-family:'MuseoModerno',sans-serif;font-size:13px;color:#3d3228;">Most boutique hotels don't provide driver rooms. LNC budgets Rp 150k/night in transport costs.</p>
    </div>
  </div>
</div>

<!-- Zones -->
<?php foreach ($hotels as $zi => $zone): ?>
<section class="section <?= $zi % 2 === 0 ? '' : 'section--sand' ?>">
  <div class="container">
    <div class="flex-between mb-48">
      <div>
        <span class="eyebrow" style="color:<?= htmlspecialchars($zone['color']) ?>;">Zone <?= $zi+1 ?></span>
        <h2 class="section-title"><?= htmlspecialchars($zone['zone']) ?></h2>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#8a7d6e;margin-top:4px;"><?= htmlspecialchars($zone['area']) ?></p>
      </div>
      <span class="tag" style="background:<?= htmlspecialchars($zone['color']) ?>;"><?= count($zone['properties']) ?> Properties</span>
    </div>

    <!-- Full table view -->
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-family:'MuseoModerno',sans-serif;font-size:13px;">
        <thead>
          <tr style="background:#1a2118;color:rgba(255,255,255,.5);">
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:left;">Property</th>
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:left;">Room Type</th>
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:right;">Low Season</th>
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:right;">High Season</th>
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:left;">Breakfast</th>
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:left;">Rating</th>
            <th style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;padding:12px 16px;text-align:left;">Contact</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($zone['properties'] as $hi => $h): ?>
          <tr style="background:<?= $hi%2===0?'#fff':'#faf7f3' ?>;border-bottom:1px solid #f0ebe3;">
            <td style="padding:16px;">
              <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:14px;color:#1a2118;"><?= htmlspecialchars($h['name']) ?></p>
              <p style="font-size:11px;color:#8a7d6e;margin-top:2px;"><?= htmlspecialchars($h['type']) ?></p>
              <p style="font-size:11px;color:#8a7d6e;font-style:italic;margin-top:4px;max-width:220px;line-height:1.5;"><?= htmlspecialchars($h['review']) ?></p>
            </td>
            <td style="padding:16px;color:#3d3228;max-width:160px;">
              <p style="font-weight:700;color:#1a2118;margin-bottom:2px;"><?= htmlspecialchars($h['room']) ?></p>
              <p style="font-size:11px;color:#8a7d6e;line-height:1.5;"><?= htmlspecialchars($h['features']) ?></p>
            </td>
            <td style="padding:16px;text-align:right;font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:14px;color:#1a2118;white-space:nowrap;">Rp <?= htmlspecialchars($h['low']) ?></td>
            <td style="padding:16px;text-align:right;font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:14px;color:#c4964a;white-space:nowrap;">Rp <?= htmlspecialchars($h['high']) ?></td>
            <td style="padding:16px;font-size:12px;color:#3d3228;"><?= htmlspecialchars($h['bf']) ?></td>
            <td style="padding:16px;">
              <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#2cb896;">⭐ <?= htmlspecialchars($h['rating']) ?></p>
            </td>
            <td style="padding:16px;font-size:12px;color:#8a7d6e;white-space:nowrap;"><?= htmlspecialchars($h['contact']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Card view -->
    <div class="hotels-grid" style="margin-top:32px;">
      <?php foreach ($zone['properties'] as $h): ?>
      <div class="hotel-card">
        <?php if (!empty($h['img'])): ?>
        <img src="<?= htmlspecialchars($h['img']) ?>"
             alt="<?= htmlspecialchars($h['name']) ?>"
             class="hotel-card__img"
             loading="lazy"
             style="width:100%;height:200px;object-fit:cover;display:block;">
        <?php else: ?>
        <div class="ph" style="height:160px;">
          <span class="ph__label"><?= strtoupper(htmlspecialchars($h['name'])) ?><br><?= htmlspecialchars($h['type']) ?></span>
        </div>
        <?php endif; ?>
        <div class="hotel-card__body">
          <p class="hotel-card__type"><?= htmlspecialchars($h['type']) ?></p>
          <p class="hotel-card__name"><?= htmlspecialchars($h['name']) ?></p>
          <p class="hotel-card__room"><?= htmlspecialchars($h['room']) ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-size:11px;color:#8a7d6e;margin-top:4px;line-height:1.5;"><?= htmlspecialchars($h['features']) ?></p>
          <div style="margin-top:10px;display:flex;justify-content:space-between;align-items:flex-end;">
            <div>
              <p style="font-family:'MuseoModerno',sans-serif;font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:.12em;color:#8a7d6e;">Low / High Season</p>
              <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:14px;color:#1a2118;margin-top:2px;">Rp <?= htmlspecialchars($h['low']) ?> – <?= htmlspecialchars($h['high']) ?></p>
              <p style="font-family:'MuseoModerno',sans-serif;font-size:10px;color:#8a7d6e;">incl. breakfast · per night</p>
            </div>
            <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:12px;color:#2cb896;">⭐ <?= htmlspecialchars($h['rating']) ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

<?php include 'includes/inquiry-cta.php'; ?>
<?php include 'includes/footer.php'; ?>
