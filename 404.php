<?php
require_once 'config.php';
http_response_code(404);
$page_title   = 'Page Not Found';
$page_desc    = 'The page you\'re looking for doesn\'t exist. Explore Lombok Nature Culture\'s private tours, trekking, and cultural experiences.';
$page_noindex = true;
include 'includes/head.php';
include 'includes/nav.php';
?>
<section style="min-height:70vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:120px 24px 80px;">
  <div style="max-width:520px;">
    <p style="font-family:'MuseoModerno',sans-serif;font-weight:700;font-size:10px;letter-spacing:.3em;text-transform:uppercase;color:#2cb896;margin-bottom:16px;">404 — Not Found</p>
    <h1 style="font-family:'MuseoModerno',sans-serif;font-weight:900;font-size:clamp(32px,5vw,52px);line-height:1.1;color:#1a2118;margin-bottom:20px;">
      This trail leads nowhere.
    </h1>
    <p style="font-family:'MuseoModerno',sans-serif;font-size:16px;color:#8a7d6e;line-height:1.75;margin-bottom:40px;">
      The page you're looking for doesn't exist or has been moved.<br>
      Let's get you back on the path.
    </p>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
      <a href="/" class="btn btn--primary">Back to Home</a>
      <a href="/experiences" class="btn btn--outline">View Experiences</a>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
