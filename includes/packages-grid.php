<?php // includes/packages-grid.php — Homepage packages section
// Requires: $packages_short, $packages_long from data.php
?>
<section class="section packages-section" id="experiences">
  <div class="container">
    <div class="packages-header">
      <div>
        <span class="eyebrow">Our Experiences</span>
        <h2 class="section-title">Journeys Designed<br>Around You</h2>
      </div>
      <p class="section-body" style="max-width:380px;">
        Each experience is private, personalised, and led by guides born in these mountains and villages.
        Hotel is not included — choose from our curated accommodation list.
      </p>
    </div>

    <!-- Featured: LNC-01 -->
    <?php $feat = $packages_short[0]; ?>
    <div class="pkg-card pkg-card--featured" style="display:grid;grid-template-columns:3fr 2fr;background:#fff;margin-bottom:3px;">
      <div style="position:relative;min-height:320px;overflow:hidden;">
        <?php if (!empty($feat['img'])): $feat_webp = preg_replace('/\.jpe?g$/i', '.webp', $feat['img']); ?>
        <picture>
          <source srcset="<?= htmlspecialchars($feat_webp) ?>" type="image/webp">
          <img src="<?= htmlspecialchars($feat['img']) ?>" alt="<?= htmlspecialchars($feat['title']) ?>" loading="eager" style="width:100%;height:100%;object-fit:cover;display:block;">
        </picture>
        <?php else: ?>
        <div class="ph" style="height:100%;min-height:320px;"></div>
        <?php endif; ?>
        <div style="position:absolute;bottom:20px;left:20px;">
          <span class="tag tag--teal"><?= htmlspecialchars($feat['category']) ?></span>
        </div>
      </div>
      <div style="padding:40px 36px;display:flex;flex-direction:column;justify-content:center;gap:14px;">
        <p style="font-family:'Raleway',sans-serif;font-weight:700;font-size:10px;letter-spacing:.2em;text-transform:uppercase;color:#8a7d6e;"><?= $feat['id'] ?> · <?= htmlspecialchars($feat['duration']) ?></p>
        <h3 style="font-family:'Raleway',sans-serif;font-weight:900;font-size:30px;line-height:1.1;color:#1a2118;"><?= htmlspecialchars($feat['title']) ?></h3>
        <p style="font-family:'Lato',sans-serif;font-size:14px;color:#8a7d6e;line-height:1.7;"><?= htmlspecialchars($feat['subtitle']) ?></p>
        <div style="width:40px;height:2px;background:#2cb896;"></div>
        <div style="display:flex;gap:24px;">
          <div>
            <p style="font-family:'Raleway',sans-serif;font-weight:700;font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:#8a7d6e;">Starting from</p>
            <p style="font-family:'Raleway',sans-serif;font-weight:900;font-size:18px;color:#2cb896;margin-top:2px;"><?= fmt_idr($feat['price']) ?></p>
            <p style="font-family:'Raleway',sans-serif;font-size:10px;color:#8a7d6e;">/ pax (min <?= $feat['min_pax'] ?> pax) · excl. hotel</p>
          </div>
        </div>
        <a href="experiences.php?id=<?= $feat['id'] ?>" class="btn btn--primary" style="width:fit-content;">View Experience →</a>
      </div>
    </div>

    <!-- Short Stay Grid -->
    <div class="packages-grid" style="margin-bottom:3px;">
      <?php foreach (array_slice($packages_short, 1, 6) as $pkg): ?>
      <div class="pkg-card" data-cat="<?= $pkg['category'] ?>">
        <div class="pkg-card__img" style="height:180px;position:relative;overflow:hidden;">
          <?php if (!empty($pkg['img'])): $pkg_webp = preg_replace('/\.jpe?g$/i', '.webp', $pkg['img']); ?>
          <picture>
            <source srcset="<?= htmlspecialchars($pkg_webp) ?>" type="image/webp">
            <img src="<?= htmlspecialchars($pkg['img']) ?>" alt="<?= htmlspecialchars($pkg['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
          </picture>
          <?php else: ?>
          <div class="ph" style="height:100%;"></div>
          <?php endif; ?>
          <div style="position:absolute;bottom:12px;left:12px;">
            <span class="tag tag--teal"><?= ucfirst($pkg['category']) ?></span>
          </div>
        </div>
        <div class="pkg-card__body">
          <p class="pkg-card__id"><?= $pkg['id'] ?></p>
          <h3 class="pkg-card__title"><?= htmlspecialchars($pkg['title']) ?></h3>
          <p class="pkg-card__sub"><?= htmlspecialchars($pkg['subtitle']) ?></p>
          <div class="pkg-card__foot">
            <div>
              <p class="pkg-card__price"><?= fmt_idr($pkg['price']) ?></p>
              <?php if ($pkg['price']): ?>
              <small class="pkg-card__price" style="font-size:11px;color:#8a7d6e;font-weight:500;"><?= fmt_usd($pkg['price']) ?> · excl. hotel</small>
              <?php endif; ?>
            </div>
            <span style="font-family:'Raleway',sans-serif;font-weight:600;font-size:11px;color:#8a7d6e;text-transform:uppercase;letter-spacing:.1em;"><?= htmlspecialchars($pkg['duration']) ?></span>
          </div>
          <a href="experiences.php?id=<?= $pkg['id'] ?>" class="btn btn--dark btn--full btn--sm" style="margin-top:12px;">View →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Long Stay -->
    <div style="margin-top:48px;margin-bottom:24px;">
      <span class="eyebrow">Long Stay Collection (7–14 Days)</span>
    </div>
    <div class="packages-grid packages-grid--2">
      <?php foreach ($packages_long as $pkg): ?>
      <div class="pkg-card" data-cat="long">
        <div class="pkg-card__img" style="height:200px;position:relative;overflow:hidden;">
          <?php if (!empty($pkg['img'])): $pkg_webp = preg_replace('/\.jpe?g$/i', '.webp', $pkg['img']); ?>
          <picture>
            <source srcset="<?= htmlspecialchars($pkg_webp) ?>" type="image/webp">
            <img src="<?= htmlspecialchars($pkg['img']) ?>" alt="<?= htmlspecialchars($pkg['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">
          </picture>
          <?php else: ?>
          <div class="ph" style="height:100%;"></div>
          <?php endif; ?>
          <div style="position:absolute;bottom:12px;left:12px;">
            <span class="tag tag--gold">Long Stay</span>
          </div>
        </div>
        <div class="pkg-card__body">
          <p class="pkg-card__id"><?= $pkg['id'] ?> · <?= htmlspecialchars($pkg['duration']) ?></p>
          <h3 class="pkg-card__title"><?= htmlspecialchars($pkg['title']) ?></h3>
          <p class="pkg-card__sub"><?= htmlspecialchars($pkg['subtitle']) ?></p>
          <a href="experiences.php?id=<?= $pkg['id'] ?>" class="btn btn--primary btn--full btn--sm" style="margin-top:16px;">Request Quote →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:40px;">
      <a href="experiences.php" class="btn btn--outline">View All Packages</a>
    </div>
  </div>
</section>
