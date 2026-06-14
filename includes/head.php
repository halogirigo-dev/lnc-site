<?php
// includes/head.php — Shared <head> for all pages
$page_title   = $page_title  ?? SITE_NAME;
$page_desc    = $page_desc   ?? SITE_TAGLINE . '. Private tours, Rinjani trekking, cultural experiences and honeymoon escapes in Lombok, Indonesia.';
$page_noindex = $page_noindex ?? false;
$site_url     = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://lomboknatureculture.com';
$og_image     = $site_url . '/uploads/hero-background.jpg';
$canonical    = $site_url . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$full_title   = htmlspecialchars($page_title) . ' — ' . SITE_NAME;
$is_homepage  = (strtok($_SERVER['REQUEST_URI'] ?? '/', '?') === '/' || basename(strtok($_SERVER['REQUEST_URI'] ?? '/', '?')) === 'index.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= htmlspecialchars($page_desc) ?>">
<?php if ($page_noindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<?php endif; ?>
<title><?= $full_title ?></title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="/uploads/logo-1777215811265.png">
<link rel="apple-touch-icon" href="/uploads/logo-1777215811265.png">

<!-- Canonical -->
<?php if (!$page_noindex): ?>
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="<?= SITE_NAME ?>">
<meta property="og:title"       content="<?= $full_title ?>">
<meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
<meta property="og:image"       content="<?= $og_image ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:url"         content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:locale"      content="en_US">

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= $full_title ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
<meta name="twitter:image"       content="<?= $og_image ?>">

<!-- Structured Data: TravelAgency -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "TravelAgency",
  "name": "<?= SITE_COMPANY ?>",
  "url": "<?= $site_url ?>",
  "logo": "<?= $site_url ?>/uploads/logo-1777215811265.png",
  "image": "<?= $og_image ?>",
  "description": "<?= SITE_TAGLINE ?>. Private tours, Rinjani trekking, cultural experiences and honeymoon escapes in Lombok, Indonesia.",
  "telephone": "<?= SITE_PHONE ?>",
  "email": "<?= SITE_EMAIL ?>",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Lombok",
    "addressRegion": "West Nusa Tenggara",
    "addressCountry": "ID"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "-8.6529",
    "longitude": "116.3238"
  },
  "areaServed": "Lombok, Indonesia",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "500",
    "bestRating": "5"
  },
  "sameAs": [
    "https://wa.me/<?= SITE_WA ?>"
  ]
}
</script>
<?php if ($is_homepage): ?>
<!-- Structured Data: BreadcrumbList (Homepage) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "<?= $site_url ?>/" }
  ]
}
</script>
<?php endif; ?>

<!-- LCP: Preload hero image for homepage -->
<?php if ($is_homepage): ?>
<link rel="preload" as="image" href="/uploads/hero-background.webp" type="image/webp" fetchpriority="high">
<?php endif; ?>

<!-- Fonts: local TTF critical weights preloaded first -->
<link rel="preload" as="font" href="/fonts/MuseoModerno-Regular.ttf" type="font/truetype" crossorigin>
<link rel="preload" as="font" href="/fonts/MuseoModerno-Bold.ttf" type="font/truetype" crossorigin>
<!-- Google Fonts: DM Sans (used by .btn-ghost) — loaded async to avoid render blocking -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600&display=swap"></noscript>

<!-- Styles -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css?v=<?= filemtime(__DIR__.'/../assets/css/style.css') ?>">
</head>
<body>
<!-- Skip Navigation (Accessibility) -->
<a href="#main-content" class="skip-link">Skip to main content</a>
