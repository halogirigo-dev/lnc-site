<?php // includes/nav.php — Sticky navigation ?>
<nav class="nav" id="main-nav">
  <a href="index.php" class="nav__logo">
    <img src="<?= UPLOADS_URL ?>/logo-1777215811265.png" alt="<?= SITE_NAME ?>">
    <div>
      <div class="nav__logo-name">Lombok Nature</div>
      <div class="nav__logo-sub">Culture</div>
    </div>
  </a>
  <div class="nav__links">
    <a href="experiences.php" class="nav__link">Experiences</a>
    <a href="hotels.php"      class="nav__link">Hotels</a>
    <a href="team.php"        class="nav__link">Our Team</a>
    <a href="index.php#inquiry" class="nav__link">Contact</a>
  </div>
  <a href="booking.php" class="btn btn--primary btn--sm nav__cta">Plan Your Journey</a>

  <!-- Hamburger button (mobile only) -->
  <button class="nav__hamburger" id="nav-hamburger" aria-label="Open menu" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>

<!-- Mobile menu overlay -->
<div class="nav__mobile-menu" id="nav-mobile-menu" role="dialog" aria-label="Navigation menu">
  <div class="nav__mobile-header">
    <a href="index.php" class="nav__logo">
      <div>
        <div class="nav__logo-name">Lombok Nature</div>
        <div class="nav__logo-sub">Culture</div>
      </div>
    </a>
    <button class="nav__mobile-close" id="nav-mobile-close" aria-label="Close menu">✕</button>
  </div>
  <nav class="nav__mobile-links">
    <a href="experiences.php" class="nav__mobile-link">Experiences</a>
    <a href="hotels.php"      class="nav__mobile-link">Hotels</a>
    <a href="team.php"        class="nav__mobile-link">Our Team</a>
    <a href="index.php#inquiry" class="nav__mobile-link">Contact</a>
  </nav>
  <a href="booking.php" class="btn btn--primary" style="margin-top:auto;">Plan Your Journey</a>
</div>
