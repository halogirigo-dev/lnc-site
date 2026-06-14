<?php
require_once 'config.php';
require_once 'data.php';
require_once 'db.php';
require_once 'includes/email-functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: booking.php');
  exit;
}

// ── CSRF ───────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
  header('Location: booking.php?error=invalid_request');
  exit;
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ── Honeypot (bots fill hidden "website" field; humans do not) ──
if (!empty($_POST['website'])) {
  // Silent redirect — don't alert bots
  header('Location: thank-you.php?ref=LNC-BOT-BLOCKED&type=quote');
  exit;
}

// ── Basic rate limiting (max 5 submissions per IP per hour) ─────
$_ip_key = 'lnc_rate_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$_rate   = $_SESSION[$_ip_key] ?? ['count' => 0, 'window' => time()];
if (time() - $_rate['window'] > 3600) {
  $_rate = ['count' => 0, 'window' => time()];
}
$_rate['count']++;
$_SESSION[$_ip_key] = $_rate;
if ($_rate['count'] > 5) {
  header('Location: booking.php?error=invalid_request');
  exit;
}

// ── Sanitise ───────────────────────────────────────────────────
function clean($val) {
  return htmlspecialchars(strip_tags(trim($val ?? '')));
}

// ── Validate ───────────────────────────────────────────────────
$raw_name  = trim($_POST['name']  ?? '');
$raw_email = trim($_POST['email'] ?? '');
$raw_phone = trim($_POST['phone'] ?? '');

if (empty($raw_name) || mb_strlen($raw_name) < 2) {
  header('Location: booking.php?error=invalid_name'); exit;
}
if (!filter_var($raw_email, FILTER_VALIDATE_EMAIL)) {
  header('Location: booking.php?error=invalid_email'); exit;
}
if (!empty($raw_phone) && !preg_match('/^[+\d\s\-().]{7,20}$/', $raw_phone)) {
  header('Location: booking.php?error=invalid_phone'); exit;
}

// ── Generate ref ───────────────────────────────────────────────
// Sequential when DB is available (LNC-YYYY-NNNNN), random fallback for session-only mode
$_precheck_db = lnc_db();
if ($_precheck_db) {
  $ref = lnc_generate_ref($_precheck_db);
} else {
  $year = date('Y');
  $rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
  $ref  = "LNC-{$year}-{$rand}";
}

// ── Collect form data ──────────────────────────────────────────
$b = [
  'ref'           => $ref,
  'submitted_at'  => date('d M Y, H:i') . ' WIB',
  'package_id'    => clean($_POST['package']       ?? ''),
  'dates'         => clean($_POST['dates']         ?? ''),
  'guests'        => clean($_POST['guests']        ?? '1'),
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

// ── Find package ───────────────────────────────────────────────
$all_pkgs = array_merge($packages_short, $packages_long, $packages_bali);
$pkg = null;
foreach ($all_pkgs as $p) {
  if ($p['id'] === $b['package_id']) { $pkg = $p; break; }
}

$b['package_title']    = $pkg ? $pkg['title']    : $b['package_id'];
$b['package_subtitle'] = $pkg ? $pkg['subtitle'] : '';
$b['package_duration'] = $pkg ? $pkg['duration'] : ($b['duration'] ?: 'TBC');
$b['package_price']    = $pkg ? ($pkg['price'] ?? 0) : 0;
$b['package_category'] = $pkg ? $pkg['category'] : '';
$b['package_includes'] = $pkg ? $pkg['includes'] : [];

// ── Calculate pricing ──────────────────────────────────────────
$guests_num = max(1, (int)$b['guests']);

if ($b['package_price'] > 0) {
  $total   = $b['package_price'] * $guests_num;
  $deposit = (int)(round($total * 0.30 / 1000) * 1000);
  $balance = $total - $deposit;
} else {
  $total = $deposit = $balance = 0;
}

$b['total_amount']        = $total;
$b['deposit_amount']      = $deposit;
$b['balance_amount']      = $balance;
$b['package_price_per_pax'] = $b['package_price'];

// Legacy keys for session / invoice compatibility
$b['subtotal']    = $total;
$b['deposit']     = $deposit;
$b['balance']     = $balance;
$b['deposit_pct'] = 30;
$b['due_deposit'] = date('d M Y', strtotime('+7 days'));
$b['due_balance'] = !empty($b['dates']) ? 'Before departure' : date('d M Y', strtotime('+30 days'));
$b['issued']      = date('d M Y');
$b['expiry']      = date('d M Y', strtotime('+14 days'));

// ── Store in session (always — fallback for DB-less mode) ──────
$_SESSION['lnc_booking'] = $b;

// ── Try DB ─────────────────────────────────────────────────────
$db = lnc_db();

if ($db) {
  try {
    // Upsert customer record
    $customer_id = null;
    if (!empty($b['email'])) {
      $db->prepare("
        INSERT INTO customers (name, email, phone, country, nationality, age_range, source, last_booking_at, created_at, updated_at)
        VALUES ($1, $2, $3, $4, $5, $6, $7, NOW(), NOW(), NOW())
        ON CONFLICT (email) DO UPDATE SET
          name             = COALESCE(EXCLUDED.name, customers.name),
          phone            = COALESCE(EXCLUDED.phone, customers.phone),
          country          = COALESCE(EXCLUDED.country, customers.country),
          nationality      = COALESCE(EXCLUDED.nationality, customers.nationality),
          age_range        = COALESCE(EXCLUDED.age_range, customers.age_range),
          source           = COALESCE(EXCLUDED.source, customers.source),
          last_booking_at  = NOW(),
          updated_at       = NOW()
      ")->execute([
        $b['name'], $b['email'], $b['phone'], $b['country'],
        $b['nationality'], $b['age_range'], $b['source'],
      ]);
      $customer_id = $db->query("SELECT id FROM customers WHERE email = " . $db->quote($b['email']))->fetchColumn();
    }

    // Insert booking with status 'new'
    $db->prepare("
      INSERT INTO bookings
        (ref, customer_id, status, package_id, package_title, package_duration, package_price_per_pax,
         total_amount, deposit_amount, balance_amount,
         guests, dates, flexibility, accommodation,
         name, email, phone, country, nationality, age_range, source,
         message, special, budget, created_at, updated_at)
      VALUES
        ($1, $2, 'new', $3, $4, $5, $6,
         $7, $8, $9,
         $10, $11, $12, $13,
         $14, $15, $16, $17, $18, $19, $20,
         $21, $22, $23, NOW(), NOW())
      ON CONFLICT (ref) DO NOTHING
    ")->execute([
      $ref,
      $customer_id,
      $b['package_id'],
      $b['package_title'],
      $b['package_duration'],
      $b['package_price_per_pax'],
      $total,
      $deposit,
      $balance,
      $guests_num,
      $b['dates'],
      $b['flexibility'],
      $b['accommodation'],
      $b['name'],
      $b['email'],
      $b['phone'],
      $b['country'],
      $b['nationality'],
      $b['age_range'],
      $b['source'],
      $b['message'],
      $b['special'],
      $b['budget'],
    ]);

    // Audit log: record initial 'new' status
    $db->prepare("
      INSERT INTO booking_status_logs (booking_ref, from_status, to_status, changed_by, notes, created_at, updated_at)
      VALUES ($1, NULL, 'new', 'system', 'Booking submitted via website.', NOW(), NOW())
    ")->execute([$ref]);

  } catch (PDOException $e) {
    // Non-fatal — continue without DB
    $db = null;
  }
}

// ── Send notification emails + redirect to thank-you ──────────
_send_new_request_emails($b, $ref);

header("Location: thank-you.php?ref=" . urlencode($ref) . "&type=quote");
exit;

// ── Email helpers (quote/request flow) ────────────────────────
function _send_new_request_emails(array $b, string $ref): void {
  // Admin email (plain text for reliability)
  $to      = SITE_EMAIL;
  $subject = "[New Request] {$ref} — {$b['name']} — {$b['package_title']}";
  $body    = "=== NEW BOOKING REQUEST ===\n\n"
    . "Reference:   {$ref}\n"
    . "Submitted:   {$b['submitted_at']}\n\n"
    . "--- PACKAGE ---\n"
    . "Package:     {$b['package_id']} — {$b['package_title']}\n"
    . "Duration:    {$b['package_duration']}\n"
    . "Dates:       {$b['dates']}\n"
    . "Guests:      {$b['guests']}\n"
    . "Flexibility: {$b['flexibility']}\n"
    . "Hotel:       {$b['accommodation']}\n"
    . "Budget:      {$b['budget']}\n\n"
    . "--- GUEST ---\n"
    . "Name:        {$b['name']}\n"
    . "Email:       {$b['email']}\n"
    . "Phone:       {$b['phone']}\n"
    . "Country:     {$b['country']}\n"
    . "Nationality: {$b['nationality']}\n"
    . "Age Range:   {$b['age_range']}\n"
    . "Source:      {$b['source']}\n\n"
    . "--- VISION ---\n"
    . "Message:\n{$b['message']}\n\n"
    . "Special:\n{$b['special']}\n\n"
    . "WhatsApp guest: https://wa.me/" . preg_replace('/[^0-9]/', '', $b['phone']) . "\n";

  $hdrs  = "From: noreply@lomboknatureculture.com\r\nReply-To: {$b['email']}\r\nX-Mailer: PHP/" . phpversion();
  @mail($to, $subject, $body, $hdrs);

  // Guest confirmation
  if (filter_var($b['email'], FILTER_VALIDATE_EMAIL)) {
    $g_sub  = "Your Journey Request Received — {$ref} | Lombok Nature Culture";
    $g_body = "Dear {$b['name']},\n\n"
      . "Thank you for reaching out to Lombok Nature Culture!\n\n"
      . "Reference:  {$ref}\n"
      . "Package:    {$b['package_id']} — {$b['package_title']}\n"
      . "Dates:      {$b['dates']}\n"
      . "Guests:     {$b['guests']}\n\n"
      . "Our team will reply with a bespoke proposal within 24–48 hours.\n\n"
      . "Questions? WhatsApp: https://wa.me/" . SITE_WA . "\n\n"
      . "Warm regards,\nThe Lombok Nature Culture Team\n" . SITE_ADDRESS;
    $g_hdrs = "From: hello@lomboknatureculture.com\r\nReply-To: " . SITE_EMAIL . "\r\n";
    @mail($b['email'], $g_sub, $g_body, $g_hdrs);
  }
}
