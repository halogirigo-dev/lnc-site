<?php // includes/testimonials.php
// Requires: $testimonials from data.php
?>
<section class="section section--sand">
  <div class="container">
    <div style="text-align:center;margin-bottom:64px;">
      <span class="eyebrow">Guest Stories</span>
      <h2 class="section-title section-title--center">Words From<br>Our Travellers</h2>
    </div>
    <div class="testi-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testi-card">
        <p class="testi-card__quote">"<?= htmlspecialchars($t['quote']) ?>"</p>
        <div class="testi-card__info">
          <div class="ph testi-card__avatar"><span></span></div>
          <div>
            <p class="testi-card__name"><?= htmlspecialchars($t['name']) ?></p>
            <p class="testi-card__origin"><?= htmlspecialchars($t['origin']) ?></p>
            <p class="testi-card__exp"><?= htmlspecialchars($t['experience']) ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
