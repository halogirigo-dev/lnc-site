<?php
require_once 'config.php';
require_once 'data.php';
$page_title = 'Our Experiences & Packages';
$page_desc  = 'Browse all Lombok Nature Culture tour packages — short stay, long stay, cultural, island, adventure and honeymoon experiences in Lombok, Indonesia.';
include 'includes/head.php';
include 'includes/nav.php';

// Determine active package
$active_id  = $_GET['id']  ?? null;
$active_cat = $_GET['cat'] ?? 'all';
$all_packages = array_merge($packages_short, $packages_long, $packages_bali);
$active_pkg   = null;
if ($active_id) {
  foreach ($all_packages as $p) {
    if ($p['id'] === $active_id) { $active_pkg = $p; break; }
  }
}
?>

<!-- Page Header -->
<div style="background:#1a2118;padding:120px 72px 48px;margin-top:72px;">
  <div class="container">
    <span class="eyebrow" style="color:rgba(255,255,255,.4);">PT Lombok Nature Culture</span>
    <h1 class="section-title section-title--light" style="font-size:52px;margin-bottom:12px;">Our Experiences</h1>
    <p class="section-body" style="color:rgba(255,255,255,.5);max-width:520px;">
      Every package is private, customisable, and led by a local guide. Hotel is booked separately from our curated list.
    </p>
  </div>
</div>

<!-- Tab Bar -->
<div class="exp-tabs">
  <?php
  $tab_cats = [
    ['id'=>'all',       'label'=>'All Packages'],
    ['id'=>'culture',   'label'=>'◈ Culture & Heritage'],
    ['id'=>'island',    'label'=>'◎ Island Escape'],
    ['id'=>'adventure', 'label'=>'⛰ Adventure'],
    ['id'=>'honeymoon', 'label'=>'♡ Honeymoon'],
    ['id'=>'long',      'label'=>'◉ Long Stay'],
    ['id'=>'bali',      'label'=>'Bali Packages'],
  ];
  foreach ($tab_cats as $cat): ?>
  <button class="exp-tab-btn" data-cat="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['label']) ?></button>
  <?php endforeach; ?>
</div>

<!-- Package Panels -->
<?php foreach ($tab_cats as $tab): ?>
<div class="exp-tab-panel" data-cat="<?= $tab['id'] ?>">
  <?php
  if ($tab['id'] === 'all') $show = $all_packages;
  elseif ($tab['id'] === 'bali') $show = $packages_bali;
  elseif ($tab['id'] === 'long') $show = $packages_long;
  else $show = array_filter($packages_short, fn($p) => $p['category'] === $tab['id']);
  ?>
  <div style="background:#f7f4ee;padding:64px 48px;">
    <div class="container">
      <div class="packages-grid">
        <?php foreach ($show as $pkg): ?>
        <div class="pkg-card" style="cursor:default;">
          <div class="pkg-card__img" style="height:200px;position:relative;overflow:hidden;">
            <?php if (!empty($pkg['img'])): $pkg_webp = preg_replace('/\.jpe?g$/i', '.webp', $pkg['img']); ?>
            <picture>
              <source srcset="<?= htmlspecialchars($pkg_webp) ?>" type="image/webp">
              <img src="<?= htmlspecialchars($pkg['img']) ?>" alt="<?= htmlspecialchars($pkg['title']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy">
            </picture>
            <?php else: ?>
            <div class="ph" style="height:100%;"></div>
            <?php endif; ?>
            <div style="position:absolute;bottom:12px;left:12px;">
              <span class="tag tag--teal"><?= ucfirst($pkg['category']) ?></span>
            </div>
          </div>
          <div class="pkg-card__body">
            <p class="pkg-card__id"><?= $pkg['id'] ?> · <?= htmlspecialchars($pkg['duration']) ?></p>
            <h3 class="pkg-card__title"><?= htmlspecialchars($pkg['title']) ?></h3>
            <p class="pkg-card__sub"><?= htmlspecialchars($pkg['subtitle']) ?></p>
            <!-- Includes -->
            <div style="margin:12px 0;font-size:12px;color:#8a7d6e;">
              <?php foreach (array_slice($pkg['includes'],0,3) as $inc): ?>
              <div style="display:flex;gap:6px;margin-bottom:3px;">
                <span style="color:#2cb896;font-weight:700;">✓</span>
                <span><?= htmlspecialchars($inc) ?></span>
              </div>
              <?php endforeach; ?>
              <?php if (count($pkg['includes']) > 3): ?>
              <div style="color:#c4964a;font-size:11px;margin-top:4px;">+<?= count($pkg['includes'])-3 ?> more included</div>
              <?php endif; ?>
            </div>
            <div class="pkg-card__foot">
              <div>
                <p class="pkg-card__price"><?= fmt_idr($pkg['price']) ?></p>
                <?php if (!empty($pkg['price_label'])): ?>
                <p class="pkg-card__price" style="font-size:12px;"><?= $pkg['price_label'] ?></p>
                <?php elseif ($pkg['price']): ?>
                <small style="font-family:'Raleway',sans-serif;font-size:10px;color:#8a7d6e;display:block;">per pax · min <?= $pkg['min_pax'] ?> · excl. hotel</small>
                <?php endif; ?>
              </div>
            </div>
            <!-- Itinerary accordion -->
            <details style="margin-top:12px;border-top:1px solid #e0d8ce;padding-top:12px;">
              <summary style="font-family:'Raleway',sans-serif;font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#3d3228;cursor:pointer;list-style:none;">
                View Itinerary ↓
              </summary>
              <div style="margin-top:12px;">
                <?php foreach ($pkg['itinerary'] as $day): ?>
                <div style="margin-bottom:12px;">
                  <p style="font-family:'Raleway',sans-serif;font-weight:700;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#2cb896;margin-bottom:4px;"><?= htmlspecialchars($day['day']) ?> — <?= htmlspecialchars($day['title']) ?></p>
                  <?php foreach ($day['items'] as $item): ?>
                  <p style="font-family:'Lato',sans-serif;font-size:12px;color:#8a7d6e;line-height:1.6;padding-left:10px;border-left:2px solid #e0d8ce;margin-bottom:3px;"><?= htmlspecialchars($item) ?></p>
                  <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </details>
            <a href="booking.php?package=<?= $pkg['id'] ?>" class="btn btn--primary btn--full btn--sm" style="margin-top:16px;">Request This Package</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- JS: activate tab from URL -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const cat = '<?= htmlspecialchars($active_cat) ?>';
  const btn = document.querySelector('.exp-tab-btn[data-cat="'+cat+'"]');
  if (btn) btn.click();
  else document.querySelector('.exp-tab-btn')?.click();
});
</script>

<?php include 'includes/inquiry-cta.php'; ?>
<?php include 'includes/footer.php'; ?>
