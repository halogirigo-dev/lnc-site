<?php // includes/hero.php — Homepage hero section ?>
<section class="hero">
  <picture class="hero__bg">
    <source srcset="/uploads/hero-background.webp" type="image/webp">
    <img src="/uploads/hero-background.jpg" alt="" aria-hidden="true" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
  </picture>
  <div class="hero__overlay"></div>
  <div class="hero__content">
    <p class="hero__eyebrow">LOMBOK · INDONESIA · EST. 2016</p>
    <h1 class="hero__title">
      Untouched Nature,<br>
      <em>Singular Journeys</em>
    </h1>
    <p class="hero__sub">
      Private expeditions, cultural encounters, and honeymoon escapes — each crafted exclusively for you.
    </p>
    <div class="hero__ctas">
      <a href="experiences.php" class="btn btn--primary">Explore Experiences</a>
      <a href="https://wa.me/6281200000000?text=Hi%20LNC%2C%20I%27d%20like%20to%20chat%20about%20a%20Lombok%20journey" 
         class="btn-ghost" target="_blank" rel="noopener">
        💬 Chat on WhatsApp
      </a>
      <a href="booking.php" class="btn btn--outline-light">Plan My Journey →</a>
    </div>
  </div>
  <div class="hero__scroll">
    <span>Scroll</span>
    <div class="hero__scroll-line"></div>
  </div>

  <!-- Trust Badge -->
  <div class="trust-badge">
    <span class="trust-badge__stars">★★★★★</span>
    <div class="trust-badge__text">4.9 Rating · 500+ Travelers<br>8 Years in Lombok</div>
  </div>

  <!-- Scroll hint -->
  <div class="hero__scroll-hint" aria-hidden="true">
    <span>SCROLL</span>
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
  </div>
</section>
