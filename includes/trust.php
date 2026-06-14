<?php // includes/trust.php — Certifications, SOP, partner logos ?>
<section class="section section--white" id="trust">
  <div class="container">
    <div style="text-align:center;margin-bottom:64px;">
      <span class="eyebrow">Why Trust Us</span>
      <h2 class="section-title section-title--center">Certified.<br>Experienced. Trusted.</h2>
    </div>
    <div class="trust-grid">
      <div class="trust-card">
        <div class="trust-card__icon">◉</div>
        <h3 class="trust-card__title">Eco Certified</h3>
        <p class="trust-card__sub">Indonesian Eco-Tourism Board · 2024</p>
        <p class="trust-card__body">We operate under certified sustainable travel guidelines — zero single-use plastic, banana-leaf dining, and carbon-conscious operations to protect Lombok's nature.</p>
      </div>
      <div class="trust-card">
        <div class="trust-card__icon">◎</div>
        <h3 class="trust-card__title">Licensed Operator</h3>
        <p class="trust-card__sub">PT Lombok Nature Culture · NTB Reg.</p>
        <p class="trust-card__body">Fully registered as PT Lombok Nature Culture under Indonesian business law. Officially licensed travel operator in West Nusa Tenggara province.</p>
      </div>
      <div class="trust-card">
        <div class="trust-card__icon">◈</div>
        <h3 class="trust-card__title">Community Partner</h3>
        <p class="trust-card__sub">Sasak Community · Active Partner</p>
        <p class="trust-card__body">We invest directly in Sasak village communities — supporting local artisans, guides, and families. Every booking contributes to the people whose land you visit.</p>
      </div>
    </div>

    <!-- SOP Standards -->
    <div style="margin-bottom:56px;">
      <span class="eyebrow" style="margin-bottom:20px;">Our Service Standards</span>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:3px;">
        <?php foreach ($sop as $label => $desc): ?>
        <div style="background:#f7f4ee;padding:20px 22px;border-left:3px solid #2cb896;">
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#2cb896;margin-bottom:6px;"><?= htmlspecialchars($label) ?></p>
          <p style="font-family:'MuseoModerno',sans-serif;font-weight:400;font-size:13px;color:#8a7d6e;line-height:1.75;"><?= htmlspecialchars($desc) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="partners">
      <p class="partners__label">Trusted Partners &amp; Accommodations</p>
      <div class="partners__logos">
        <?php
        $partners = [
          'Jeeva Klui'        => 'Jeeva Klui.png',
          'Kaleana Villas'    => 'Kaleana Villas.png',
          'MAHAMAYA Gili Meno'=> 'MAHAMAYA Gili Meno.png',
          'Sudamala Resort'   => 'sundamala resort',
        ];
        foreach ($partners as $label => $file):
          $filename = htmlspecialchars($file);
          $labelSafe = htmlspecialchars($label);
        ?>
        <div class="partners__logo-item">
          <img
            src="<?= UPLOADS_URL ?>/logos partner/<?= $filename ?>"
            alt="<?= $labelSafe ?>"
            class="partners__logo-img"
            onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
          >
          <span class="partners__logo-fallback"><?= $labelSafe ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>