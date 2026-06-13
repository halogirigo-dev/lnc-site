<?php // includes/team-preview.php — Team section (homepage preview)
// Requires: $team from data.php
?>
<section class="section" id="team" style="background:var(--bg);">
  <div class="container">
    <div style="text-align:center;margin-bottom:56px;" class="reveal">
      <span class="eyebrow">The People Behind Your Journey</span>
      <h2 class="section-title section-title--center">Our Team</h2>
      <p class="section-body" style="max-width:480px;margin:0 auto;margin-top:12px;">
        Born in Lombok. Passionate about their island. Dedicated to making your journey extraordinary.
      </p>
    </div>

    <div class="team-grid">
      <?php foreach ($team as $i => $m):
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',trim($m['name'])));
        $imgPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/team/' . $slug . '.jpg';
        $imgUrl  = UPLOADS_URL . '/team/' . $slug . '.jpg';
        $hasImg  = file_exists($imgPath);
        // Pick an initial letter colour per card
        $colours = ['#2cb896','#38a8d8','#c4964a','#2cb896'];
        $accent = $colours[$i % count($colours)];
      ?>
      <div class="member-card reveal reveal--d<?= $i+1 ?>">
        <!-- Photo or portrait placeholder -->
        <?php if ($hasImg): ?>
        <div style="height:260px;overflow:hidden;">
          <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($m['name']) ?>"
               style="width:100%;height:100%;object-fit:cover;display:block;transition:transform .6s ease;"
               onmouseover="this.style.transform='scale(1.05)'"
               onmouseout="this.style.transform='scale(1)'">
        </div>
        <?php else: ?>
        <div style="height:260px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(160deg,#1a2118 0%,#243028 100%);position:relative;overflow:hidden;">
          <div style="position:absolute;inset:0;background:radial-gradient(circle at 50% 80%,rgba(44,184,150,.08) 0%,transparent 60%);"></div>
          <!-- Initials avatar -->
          <div style="width:80px;height:80px;border-radius:50%;border:2px solid <?= $accent ?>;display:flex;align-items:center;justify-content:center;margin-bottom:12px;background:rgba(255,255,255,.04);">
            <span style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:26px;color:<?= $accent ?>;">
              <?= mb_strtoupper(mb_substr($m['name'], 0, 1)) ?>
            </span>
          </div>
          <span style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:9px;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.3);">PORTRAIT PHOTO</span>
        </div>
        <?php endif; ?>
        <div class="member-card__body">
          <p class="member-card__role"><?= htmlspecialchars($m['role']) ?></p>
          <p class="member-card__name"><?= htmlspecialchars($m['name']) ?></p>
          <p class="member-card__origin"><?= htmlspecialchars($m['origin']) ?></p>
          <p class="member-card__yrs"><?= htmlspecialchars($m['years']) ?> EXPERIENCE</p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:48px;" class="reveal">
      <a href="team.php" class="btn btn--outline">Meet the Full Team</a>
    </div>
  </div>
</section>