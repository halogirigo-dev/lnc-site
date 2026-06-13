<?php
require_once 'config.php';
require_once 'data.php';
$page_title = 'Our Team';
$page_desc  = 'Meet the guides and team behind PT Lombok Nature Culture — born in Lombok, passionate about their island.';
include 'includes/head.php';

// Structured data: Person schema for each team member
$site_url_sd = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://lomboknatureculture.com';
$sd_persons = array_map(fn($m) => [
  '@type'       => 'Person',
  'name'        => $m['name'],
  'jobTitle'    => $m['role'],
  'description' => $m['bio'],
  'knowsLanguage' => array_map('trim', explode(',', $m['lang'])),
  'worksFor'    => ['@type' => 'TravelAgency', 'name' => SITE_COMPANY, 'url' => $site_url_sd],
  'url'         => $site_url_sd . '/team',
], $team);
echo '<script type="application/ld+json">' . json_encode([
  '@context' => 'https://schema.org',
  '@graph'   => $sd_persons,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . PHP_EOL;

include 'includes/nav.php';

$values = [
  ['◉','Born Here, Not Imported',   'Every guide is Lombok-born. We do not hire "professional tour guides" from elsewhere. Local knowledge, local heart.'],
  ['◎','Small Teams, Deep Care',     'We limit our operation deliberately. Fewer journeys means more attention on each guest. Quality is not scalable — we chose not to scale.'],
  ['◈','Honest Pricing',             'We publish real prices. No hidden fees, no commission markups, no upsells. What we quote is what you pay.'],
  ['⌘','Available, Always',          'Our team is reachable by WhatsApp 7 days a week. If something goes wrong — any time, any hour — we respond.'],
  ['◆','Sustainably Operated',       'We use eco-certified lodges, minimise plastic, offset carbon, and return proceeds to Sasak community projects.'],
  ['◇','No Overcrowding',            'Every experience is private. We do not combine your group with other guests, ever.'],
];
?>

<!-- Hero -->
<div style="position:relative;height:55vh;min-height:360px;margin-top:72px;">
  <div class="ph" style="position:absolute;inset:0;">
    <span class="ph__label">TEAM GROUP PHOTO · Lombok · Rinjani foothills · golden hour</span>
  </div>
  <div style="position:absolute;inset:0;background:linear-gradient(160deg,rgba(26,33,24,.35),rgba(26,33,24,.68));"></div>
  <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:48px 72px;">
    <span class="eyebrow" style="color:rgba(255,255,255,.55);">The People Behind Your Journey</span>
    <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:56px;color:#fff;line-height:1.05;margin-bottom:10px;">Our Team</h1>
    <p style="font-family:'MuseoModerno',sans-serif;font-style:italic;font-size:20px;color:rgba(255,255,255,.65);">Born in Lombok. Passionate about their island.</p>
  </div>
</div>

<!-- Intro Strip -->
<div style="background:#1a2118;padding:56px 72px;">
  <div class="container" style="display:grid;grid-template-columns:3fr 2fr;gap:64px;align-items:center;">
    <div>
      <span class="eyebrow" style="color:rgba(255,255,255,.4);">Who We Are</span>
      <p style="font-family:'MuseoModerno',sans-serif;font-style:italic;font-size:22px;color:rgba(255,255,255,.85);line-height:1.6;margin-bottom:20px;">"We are not a travel agency with guides. We are guides who built a travel agency."</p>
      <p class="section-body" style="color:rgba(255,255,255,.5);">Every person on our team was born or has lived in Lombok for most of their life. We hire for passion, local knowledge, and character — not certificates alone.</p>
    </div>
    <div>
      <?php foreach ([['8','Team Members'],['12+','Years of Experience (Founder)'],['5','Languages Spoken'],['400+','Rinjani Summits Completed']] as [$n,$l]): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:18px 0;border-bottom:1px solid rgba(255,255,255,.07);">
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:32px;color:#2cb896;"><?= $n ?></p>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:500;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.4);text-align:right;max-width:140px;"><?= $l ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Team Grid -->
<section class="section" style="background:#f7f4ee;">
  <div class="container">
    <span class="eyebrow" style="margin-bottom:40px;display:block;">Meet the Team</span>
    <div class="team-grid" id="team-grid">
      <?php foreach ($team as $i => $m): ?>
      <div class="member-card" data-member="<?= $i ?>">
        <div class="ph" style="height:280px;">
          <span class="ph__label"><?= strtoupper(htmlspecialchars($m['name'])) ?><br>Portrait photo</span>
        </div>
        <div class="member-card__body">
          <p class="member-card__role"><?= htmlspecialchars($m['role']) ?></p>
          <p class="member-card__name"><?= htmlspecialchars($m['name']) ?></p>
          <p class="member-card__origin"><?= htmlspecialchars($m['origin']) ?></p>
          <p class="member-card__yrs"><?= $m['years'] ?> years experience</p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Expanded panel (filled by JS) -->
    <div id="member-expanded" class="member-expanded" style="display:none;"></div>
  </div>
</section>

<!-- Values -->
<section class="section section--sand">
  <div class="container">
    <span class="eyebrow">How We Operate</span>
    <h2 class="section-title" style="margin-bottom:48px;">Our Principles</h2>
    <div class="grid-3" style="gap:3px;">
      <?php foreach ($values as [$icon,$title,$body]): ?>
      <div class="value-card" style="background:#f7f4ee;padding:32px;transition:background .2s;">
        <div style="font-size:22px;color:#2cb896;margin-bottom:16px;"><?= $icon ?></div>
        <p style="font-family:'MuseoModerno',sans-serif;font-weight:800;font-size:17px;color:#1a2118;margin-bottom:10px;"><?= htmlspecialchars($title) ?></p>
        <p style="font-family:'MuseoModerno',sans-serif;font-size:13.5px;color:#8a7d6e;line-height:1.8;"><?= htmlspecialchars($body) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Careers -->
<div style="background:#1a2118;padding:72px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:32px;" id="careers">
  <div>
    <span class="eyebrow" style="color:rgba(255,255,255,.35);">Careers</span>
    <h2 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:34px;color:#fff;margin-bottom:10px;">Join Our Team</h2>
    <p class="section-body" style="color:rgba(255,255,255,.5);max-width:480px;">We're always looking for exceptional local guides, hospitality professionals, and operations staff who love Lombok.</p>
  </div>
  <div style="flex-shrink:0;display:flex;flex-direction:column;gap:10px;">
    <a href="mailto:careers@lnc-travel.com" class="btn btn--primary">Send Your Application</a>
    <p style="font-family:'MuseoModerno',sans-serif;font-size:12px;color:rgba(255,255,255,.3);text-align:center;">careers@lnc-travel.com</p>
  </div>
</div>

<!-- Pass team data to JS -->
<script>
window.LNC_TEAM = <?= json_encode($team) ?>;
</script>

<?php include 'includes/footer.php'; ?>
