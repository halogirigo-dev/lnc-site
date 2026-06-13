<?php // includes/hotels.php — Hotel section (homepage preview)
// Requires: $hotels from data.php
?>
<section class="section section--sand" id="hotels">
  <div class="container">
    <div class="flex-between mb-48">
      <div>
        <span class="eyebrow">Where You'll Stay</span>
        <h2 class="section-title">Handpicked<br>Accommodations</h2>
      </div>
      <p class="section-body" style="max-width:360px;">
        We partner only with properties that share our values — authentic, sustainable, and genuinely exceptional.
        Hotel pricing is separate from our service packages.
      </p>
    </div>

    <!-- Zone tabs -->
    <div class="hotels-tabs-wrapper">
      <div class="zone-tabs">
        <?php foreach ($hotels as $i => $zone): ?>
        <button class="zone-tab<?= $i === 0 ? ' active' : '' ?>"
                onclick="switchZone(<?= $i ?>)">
          <?= htmlspecialchars($zone['zone']) ?>
          <span style="opacity:.5;font-weight:400;font-size:10px;">· <?= htmlspecialchars($zone['area']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($hotels as $i => $zone): ?>
      <div class="zone-panel" id="zone-<?= $i ?>" <?= $i !== 0 ? 'style="display:none"' : '' ?>>
        <div class="hotels-grid">
          <?php foreach ($zone['properties'] as $h):
            // Check for hotel photo
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',trim($h['name'])));
            $imgPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/hotels/' . $slug . '.jpg';
            $imgUrl  = UPLOADS_URL . '/hotels/' . $slug . '.jpg';
            $hasImg  = file_exists($imgPath);
          ?>
          <div class="hotel-card reveal">
            <!-- Hotel image or elegant placeholder -->
            <?php if ($hasImg): ?>
            <div class="hotel-card__img">
              <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($h['name']) ?>" loading="lazy">
            </div>
            <?php else: ?>
            <?php if (!empty($h['img'])): ?>
            <img src="<?= htmlspecialchars($h['img']) ?>"
                 alt="<?= htmlspecialchars($h['name']) ?>"
                 class="hotel-card__img"
                 loading="lazy">
          <?php else: ?>
            <div class="hotel-card__img hotel-card__img--ph"></div>
          <?php endif; ?>
            <?php endif; ?>
            <div class="hotel-card__body">
              <p class="hotel-card__type"><?= htmlspecialchars($h['type']) ?></p>
              <p class="hotel-card__name"><?= htmlspecialchars($h['name']) ?></p>
              <p class="hotel-card__room"><?= htmlspecialchars($h['room']) ?></p>
              <p class="hotel-card__price">
                Rp <?= htmlspecialchars($h['low']) ?> – <?= htmlspecialchars($h['high']) ?>
                <span style="font-size:10px;color:#8a7d6e;"> / night · incl. BF</span>
              </p>
              <p class="hotel-card__rating">⭐ <?= htmlspecialchars($h['rating']) ?></p>
              <p style="font-family:'MuseoModerno',sans-serif;font-weight:300;font-size:12px;color:#8a7d6e;line-height:1.7;margin-top:6px;font-style:italic;"><?= htmlspecialchars($h['review']) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:40px;">
      <a href="hotels.php" class="btn btn--outline">View Full Hotel List</a>
    </div>

    <!-- Policy note -->
    <div style="margin-top:32px;padding:20px 24px;background:#fff;border-left:3px solid var(--teal);">
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--teal);margin-bottom:8px;">Accommodation Policy</p>
      <p style="font-family:'MuseoModerno',sans-serif;font-weight:400;font-size:13px;color:#8a7d6e;line-height:1.8;">
        Hotel prices are <strong style="color:#3d3228;">not included</strong> in our tour packages.
        Low season: Jan–Mar, May, Oct–early Dec.
        High season: Jul–Sep, Eid (H-7 to H+7), Christmas &amp; New Year (20 Dec–5 Jan).
        We coordinate all hotel bookings on your behalf for seamless logistics.
      </p>
    </div>
  </div>
</section>

<script>
function switchZone(idx) {
  document.querySelectorAll('.zone-panel').forEach((p,i) => p.style.display = i === idx ? '' : 'none');
  document.querySelectorAll('.zone-tab').forEach((t,i) => t.classList.toggle('active', i === idx));
}
</script>