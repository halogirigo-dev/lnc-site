<?php
require_once 'config.php';
require_once 'data.php';
require_once 'db.php';
require_once 'lib/midtrans.php';
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
$year = date('Y');
$rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
$ref  = "LNC-{$year}-{$rand}";

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
    $db->prepare("
      INSERT INTO bookings
        (ref, status, package_id, package_title, package_duration, package_price_per_pax,
         total_amount, deposit_amount, balance_amount,
         guests, dates, flexibility, accommodation,
         name, email, phone, country, nationality, age_range, source,
         message, special, budget)
      VALUES
        (:ref, 'pending_payment', :pkg_id, :pkg_title, :pkg_dur, :price_pax,
         :total, :deposit, :balance,
         :guests, :dates, :flex, :hotel,
         :name, :email, :phone, :country, :nationality, :age, :source,
         :msg, :special, :budget)
    ")->execute([
      ':ref'       => $ref,
      ':pkg_id'    => $b['package_id'],
      ':pkg_title' => $b['package_title'],
      ':pkg_dur'   => $b['package_duration'],
      ':price_pax' => $b['package_price_per_pax'],
      ':total'     => $total,
      ':deposit'   => $deposit,
      ':balance'   => $balance,
      ':guests'    => $guests_num,
      ':dates'     => $b['dates'],
      ':flex'      => $b['flexibility'],
      ':hotel'     => $b['accommodation'],
      ':name'      => $b['name'],
      ':email'     => $b['email'],
      ':phone'     => $b['phone'],
      ':country'   => $b['country'],
      ':nationality' => $b['nationality'],
      ':age'       => $b['age_range'],
      ':source'    => $b['source'],
      ':msg'       => $b['message'],
      ':special'   => $b['special'],
      ':budget'    => $b['budget'],
    ]);
  } catch (PDOException $e) {
    // Non-fatal — continue without DB
    $db = null;
  }
}

// ── Route based on price ───────────────────────────────────────
if ($b['package_price'] > 0 && $db) {
  // Get Snap token for deposit
  $snap_params = lnc_format_snap_params($b, 'deposit');
  $snap_result = lnc_get_snap_token($snap_params);

  if (!isset($snap_result['error'])) {
    $token = $snap_result['token'];
    $_SESSION['lnc_snap_token'] = $token;

    // Save to payments table
    try {
      $db->prepare("
        INSERT INTO payments (booking_ref, payment_type, amount, midtrans_order_id, snap_token)
        VALUES (:ref, 'deposit', :amount, :order_id, :token)
      ")->execute([
        ':ref'      => $ref,
        ':amount'   => $deposit,
        ':order_id' => $ref . '-DEP',
        ':token'    => $token,
      ]);
    } catch (PDOException $e) { /* non-fatal */ }

    header("Location: payment.php?ref=" . urlencode($ref));
    exit;
  }
  // Snap token failed — fall through to quote flow
}

// ── Quote / no-DB flow — send emails + redirect ────────────────
_send_new_request_emails($b, $ref);

$type = $b['package_price'] > 0 ? 'pending' : 'quote';
header("Location: thank-you.php?ref=" . urlencode($ref) . "&type={$type}");
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
