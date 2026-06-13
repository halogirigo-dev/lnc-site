<?php
// process-booking.php — Handles booking form POST submission
require_once 'config.php';
require_once 'data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: booking.php');
  exit;
}

// ── CSRF protection ────────────────────────────────────────────
if (empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
  http_response_code(403);
  header('Location: booking.php?error=invalid_request');
  exit;
}
// Rotate token after use
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ── Sanitise inputs ────────────────────────────────────────────
function clean($val) {
  return htmlspecialchars(strip_tags(trim($val ?? '')));
}

// ── Server-side validation ─────────────────────────────────────
$raw_name  = trim($_POST['name']  ?? '');
$raw_email = trim($_POST['email'] ?? '');
$raw_phone = trim($_POST['phone'] ?? '');

if (empty($raw_name) || mb_strlen($raw_name) < 2) {
  header('Location: booking.php?error=invalid_name');
  exit;
}
if (!filter_var($raw_email, FILTER_VALIDATE_EMAIL)) {
  header('Location: booking.php?error=invalid_email');
  exit;
}
if (!empty($raw_phone) && !preg_match('/^[+\d\s\-().]{7,20}$/', $raw_phone)) {
  header('Location: booking.php?error=invalid_phone');
  exit;
}

// ── Generate booking reference ─────────────────────────────────
$year = date('Y');
$rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
$ref  = "LNC-{$year}-{$rand}";

// ── Collect form data ──────────────────────────────────────────
$booking = [
  'ref'           => $ref,
  'submitted_at'  => date('d M Y, H:i') . ' WIB',
  'package_id'    => clean($_POST['package']       ?? ''),
  'dates'         => clean($_POST['dates']         ?? ''),
  'guests'        => clean($_POST['guests']        ?? ''),
  'duration'      => clean($_POST['duration']      ?? ''),
  'flexibility'   => clean($_POST['flexibility']   ?? ''),
  'accommodation' => clean($_POST['accommodation'] ?? ''),
  'name'          => clean($_POST['name']          ?? ''),
  'email'         => clean($_POST['email']         ?? ''),
  'phone'         => clean($_POST['phone']         ?? ''),
  'country'       => clean($_POST['country']       ?? ''),
  'nationality'   => clean($_POST['nationality']   ?? ''),
  'age_range'     => clean($_POST['age_range']     ?? ''),
  'source'        => clean($_POST['source']        ?? ''),
  'message'       => clean($_POST['message']       ?? ''),
  'special'       => clean($_POST['special']       ?? ''),
  'budget'        => clean($_POST['budget']        ?? ''),
];

// ── Find package details ───────────────────────────────────────
$all_pkgs = array_merge($packages_short, $packages_long, $packages_bali);
$pkg = null;
foreach ($all_pkgs as $p) {
  if ($p['id'] === $booking['package_id']) { $pkg = $p; break; }
}

$booking['package_title']    = $pkg ? $pkg['title']    : $booking['package_id'];
$booking['package_subtitle'] = $pkg ? $pkg['subtitle'] : '';
$booking['package_duration'] = $pkg ? $pkg['duration'] : ($booking['duration'] ?: 'TBC');
$booking['package_price']    = $pkg ? ($pkg['price'] ?? 0) : 0;
$booking['package_category'] = $pkg ? $pkg['category'] : '';
$booking['package_includes'] = $pkg ? $pkg['includes'] : [];

// ── Calculate pricing ─────────────────────────────────────────
$guests_num = (int)($booking['guests'] ?: 1);
if ($booking['package_price'] > 0) {
  $subtotal = $booking['package_price'] * $guests_num;
} else {
  $subtotal = 0; // Request Quote — team will confirm
}
$booking['subtotal']     = $subtotal;
$booking['deposit']      = (int)($subtotal * 0.30);
$booking['balance']      = $subtotal - (int)($subtotal * 0.30);
$booking['deposit_pct']  = 30;

// Payment due dates
$booking['due_deposit']  = date('d M Y', strtotime('+7 days'));
$booking['due_balance']  = !empty($booking['dates'])
  ? 'Before departure'
  : date('d M Y', strtotime('+30 days'));
$booking['issued']       = date('d M Y');
$booking['expiry']       = date('d M Y', strtotime('+14 days'));

// ── Store in session ───────────────────────────────────────────
$_SESSION['lnc_booking'] = $booking;

// ── Send email to LNC team ─────────────────────────────────────
$to      = SITE_EMAIL;
$subject = "[New Request] {$ref} — {$booking['name']} — {$booking['package_title']}";
$body    = "=== NEW BOOKING REQUEST ===\n\n";
$body   .= "Reference:   {$ref}\n";
$body   .= "Submitted:   {$booking['submitted_at']}\n\n";
$body   .= "--- PACKAGE ---\n";
$body   .= "Package:     {$booking['package_id']} — {$booking['package_title']}\n";
$body   .= "Duration:    {$booking['package_duration']}\n";
$body   .= "Dates:       {$booking['dates']}\n";
$body   .= "Guests:      {$booking['guests']}\n";
$body   .= "Flexibility: {$booking['flexibility']}\n";
$body   .= "Hotel:       {$booking['accommodation']}\n";
$body   .= "Budget:      {$booking['budget']}\n\n";
$body   .= "--- GUEST ---\n";
$body   .= "Name:        {$booking['name']}\n";
$body   .= "Email:       {$booking['email']}\n";
$body   .= "Phone:       {$booking['phone']}\n";
$body   .= "Country:     {$booking['country']}\n";
$body   .= "Nationality: {$booking['nationality']}\n";
$body   .= "Age Range:   {$booking['age_range']}\n";
$body   .= "Source:      {$booking['source']}\n\n";
$body   .= "--- VISION ---\n";
$body   .= "Message:\n{$booking['message']}\n\n";
$body   .= "Special:\n{$booking['special']}\n\n";
$body   .= "---\n";
$body   .= "View Proposal: https://lomboknatureculture.com/invoice.php\n";
$body   .= "WhatsApp guest: https://wa.me/" . preg_replace('/[^0-9]/', '', $booking['phone']) . "\n";

$headers  = "From: noreply@lomboknatureculture.com\r\n";
$headers .= "Reply-To: {$booking['email']}\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();
@mail($to, $subject, $body, $headers);

// ── Send confirmation email to guest ──────────────────────────
if (filter_var($booking['email'], FILTER_VALIDATE_EMAIL)) {
  $g_subj  = "Your Journey Request Received — {$ref} | Lombok Nature Culture";
  $g_body  = "Dear {$booking['name']},\n\n";
  $g_body .= "Thank you for reaching out to Lombok Nature Culture!\n\n";
  $g_body .= "We have received your request:\n\n";
  $g_body .= "  Reference:  {$ref}\n";
  $g_body .= "  Package:    {$booking['package_id']} — {$booking['package_title']}\n";
  $g_body .= "  Dates:      {$booking['dates']}\n";
  $g_body .= "  Guests:     {$booking['guests']}\n\n";
  $g_body .= "Our team will review your request and send a bespoke proposal within 24–48 hours via email and WhatsApp.\n\n";
  $g_body .= "Please save your reference number: {$ref}\n\n";
  $g_body .= "Questions? WhatsApp us anytime:\n";
  $g_body .= "https://wa.me/" . SITE_WA . "\n\n";
  $g_body .= "Warm regards,\n";
  $g_body .= "The Lombok Nature Culture Team\n";
  $g_body .= SITE_ADDRESS . "\n";

  $g_headers  = "From: hello@lomboknatureculture.com\r\n";
  $g_headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
  @mail($booking['email'], $g_subj, $g_body, $g_headers);
}

// ── Redirect to thank-you page ─────────────────────────────────
header("Location: thank-you.php?ref=" . urlencode($ref));
exit;
