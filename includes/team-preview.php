<?php // includes/team-preview.php — Team section (homepage preview)
// Requires: $team from data.php
?>
<section class="section" id="team" style="background:var(--bg);">
  <div class="container">
    <div style="text-align:center;margin-bottom:56px;" class="reveal">
      <span class="eyebrow">THE PEOPLE BEHIND YOUR JOURNEY</span>
      <h2 class="section-title section-title--center">Meet Our Team</h2>
      <p class="section-body" style="max-width:560px;margin:0 auto;margin-top:12px;">
        Born in Lombok and passionate about sharing our island with the world. We combine local knowledge, authentic hospitality, and professional service to create unforgettable journeys across Lombok.
      </p>
    </div>

    <div class="team-grid">
      <?php foreach ($team as $i => $m):
        $slug    = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($m['name'])));
        $imgPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/team/' . $slug . '.jpg';
        $imgUrl  = UPLOADS_URL . '/team/' . $slug . '.jpg';
        $hasImg  = file_exists($imgPath);
        $colours = ['#2cb896', '#38a8d8', '#c4964a', '#2cb896'];
        $accent  = $colours[$i % count($colours)];
        $initial = $m['initial'] ?? mb_strtoupper(mb_substr($m['name'], 0, 1));
        $initFz  = mb_strlen($initial) > 1 ? '18px' : '26px';
      ?>
      <article class="member-card reveal reveal--d<?= $i + 1 ?>" tabindex="0" aria-label="<?= htmlspecialchars($m['name']) ?>, <?= htmlspecialchars($m['role']) ?>">
        <?php if ($hasImg): ?>
        <div class="member-card__photo">
          <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($m['name']) ?>, <?= htmlspecialchars($m['role']) ?> at Lombok Nature Culture" loading="lazy">
        </div>
        <?php else: ?>
        <div class="member-card__avatar" aria-hidden="true" style="background:linear-gradient(160deg,#1a2118 0%,#243028 100%);">
          <div style="position:absolute;inset:0;background:radial-gradient(circle at 50% 80%,rgba(44,184,150,.08) 0%,transparent 60%);"></div>
          <div style="width:80px;height:80px;border-radius:50%;border:2px solid <?= $accent ?>;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.04);position:relative;">
            <span style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:<?= $initFz ?>;color:<?= $accent ?>;letter-spacing:-.02em;"><?= htmlspecialchars($initial) ?></span>
          </div>
          <span style="font-family:'MuseoModerno',sans-serif;font-weight:600;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-top:10px;">LOMBOK, INDONESIA</span>
        </div>
        <?php endif; ?>
        <div class="member-card__body">
          <p class="member-card__role"><?= htmlspecialchars($m['role']) ?></p>
          <p class="member-card__name"><?= htmlspecialchars($m['name']) ?></p>
          <p class="member-card__origin"><?= htmlspecialchars($m['origin']) ?></p>
          <p class="member-card__bio"><?= htmlspecialchars($m['bio']) ?></p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <p class="reveal" style="text-align:center;font-family:'MuseoModerno',sans-serif;font-size:13px;color:var(--muted);max-width:620px;margin:40px auto 0;line-height:1.85;border-top:1px solid rgba(0,0,0,.06);padding-top:32px;">
      Supported by a growing network of local guides, hospitality partners, transportation providers, and cultural experts across Lombok.
    </p>

    <div style="text-align:center;margin-top:32px;" class="reveal">
      <a href="/team" class="btn btn--outline">Meet the Full Team</a>
    </div>
  </div>
</section>
