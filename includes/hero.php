<?php // includes/hero.php — Homepage hero section ?>
<section class="hero">
  <video id="hero-video" class="hero__bg" autoplay muted loop playsinline preload="metadata" aria-hidden="true" poster="/uploads/hero-background.jpg">
    <source src="/uploads/HERO%20BG%20LNC.mp4" type="video/mp4">
  </video>
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
      <a href="/booking" class="btn btn--primary">Plan My Journey →</a>
      <a href="https://wa.me/<?= SITE_WA ?>?text=Hi%20LNC%2C%20I%27d%20like%20to%20chat%20about%20a%20Lombok%20journey"
         class="btn-ghost" target="_blank" rel="noopener noreferrer" aria-label="Chat with Lombok Nature Culture on WhatsApp">
        💬 Chat on WhatsApp
      </a>
      <a href="/experiences" class="btn btn--outline-light">Explore Experiences</a>
    </div>
  </div>
  <div class="hero__scroll">
    <span>Scroll</span>
    <div class="hero__scroll-line"></div>
  </div>

  <!-- Scroll hint -->
  <div class="hero__scroll-hint" aria-hidden="true">
    <span>SCROLL</span>
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
  </div>
</section>
