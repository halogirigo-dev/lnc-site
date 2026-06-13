<?php // includes/nav.php — Sticky navigation ?>
<header role="banner">
<nav class="nav" id="main-nav" aria-label="Primary navigation">
  <a href="/" class="nav__logo" aria-label="<?= SITE_NAME ?> — Home">
    <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" alt="<?= SITE_NAME ?> logo" width="38" height="38">
    <div>
      <div class="nav__logo-name">Lombok Nature</div>
      <div class="nav__logo-sub">Culture</div>
    </div>
  </a>
  <div class="nav__links">
    <a href="/experiences" class="nav__link">Experiences</a>
    <a href="/hotels"      class="nav__link">Hotels</a>
    <a href="/team"        class="nav__link">Our Team</a>
    <a href="/#inquiry"    class="nav__link">Contact</a>
  </div>
  <a href="/booking" class="btn btn--primary btn--sm nav__cta">Plan Your Journey</a>

  <!-- Hamburger button (mobile only) -->
  <button class="nav__hamburger" id="nav-hamburger" aria-label="Open navigation menu" aria-expanded="false" aria-controls="nav-mobile-menu">
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
  </button>
</nav>
</header>

<!-- Mobile menu overlay -->
<div class="nav__mobile-menu" id="nav-mobile-menu" role="dialog" aria-modal="true" aria-label="Navigation menu" hidden>
  <div class="nav__mobile-header">
    <a href="/" class="nav__logo" aria-label="<?= SITE_NAME ?> — Home">
      <div>
        <div class="nav__logo-name">Lombok Nature</div>
        <div class="nav__logo-sub">Culture</div>
      </div>
    </a>
    <button class="nav__mobile-close" id="nav-mobile-close" aria-label="Close navigation menu">✕</button>
  </div>
  <nav class="nav__mobile-links" aria-label="Mobile navigation">
    <a href="/experiences" class="nav__mobile-link">Experiences</a>
    <a href="/hotels"      class="nav__mobile-link">Hotels</a>
    <a href="/team"        class="nav__mobile-link">Our Team</a>
    <a href="/#inquiry"    class="nav__mobile-link">Contact</a>
  </nav>
  <a href="/booking" class="btn btn--primary" style="margin-top:auto;">Plan Your Journey</a>
</div>
<main id="main-content" tabindex="-1">
