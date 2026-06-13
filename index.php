<?php
require_once 'config.php';
require_once 'data.php';
$page_title = 'Home';
$page_desc  = 'PT Lombok Nature Culture — ' . SITE_TAGLINE . '. Private tours, Rinjani trekking, cultural experiences and honeymoon escapes in Lombok, Indonesia.';
include 'includes/head.php';
include 'includes/nav.php';

$site_url_faq = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://lomboknatureculture.com';
echo '<script type="application/ld+json">' . json_encode([
  '@context' => 'https://schema.org',
  '@type'    => 'FAQPage',
  'mainEntity' => [
    ['@type'=>'Question','name'=>'How do I book a tour with Lombok Nature Culture?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Submit a journey request via our booking form or contact us on WhatsApp. We reply within 24 hours with a personalised itinerary. No payment is required at this stage.']],
    ['@type'=>'Question','name'=>'Is accommodation included in the tour price?','acceptedAnswer'=>['@type'=>'Answer','text'=>'No. Hotel is booked separately from our service packages. We partner with curated properties across 4 zones of Lombok and the Gili Islands, and we coordinate everything — you simply choose your comfort level.']],
    ['@type'=>'Question','name'=>'Are tours private or shared group tours?','acceptedAnswer'=>['@type'=>'Answer','text'=>'All experiences are 100% private. We never combine your group with other guests. Every itinerary is tailored exclusively for you.']],
    ['@type'=>'Question','name'=>'What languages do your guides speak?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Our guides speak Indonesian and English fluently. Arief (Founder) also speaks basic French, Dewi speaks Sasak, and Sari speaks basic Japanese.']],
    ['@type'=>'Question','name'=>'What is the cancellation policy?','acceptedAnswer'=>['@type'=>'Answer','text'=>'Cancellations more than 30 days before departure receive a full refund minus the deposit. Cancellations within 14 days are non-refundable. Full details are on our Legal & Policies page at ' . $site_url_faq . '/legal.']],
    ['@type'=>'Question','name'=>'When is the best time to visit Lombok?','acceptedAnswer'=>['@type'=>'Answer','text'=>'The dry season (May–October) is ideal for Rinjani trekking and beach activities, with July–September being peak season. April–June and October–November offer great conditions with fewer crowds.']],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . PHP_EOL;
include 'includes/hero.php';
include 'includes/experience-bar.php';
include 'includes/packages-grid.php';
include 'includes/hotels.php';
include 'includes/philosophy.php';
include 'includes/how-it-works.php';
include 'includes/trust.php';
include 'includes/team-preview.php';
include 'includes/testimonials.php';
include 'includes/gallery.php';
include 'includes/inquiry-cta.php';
include 'includes/footer.php';
