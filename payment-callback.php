<?php
require_once 'config.php';
require_once 'db.php';
require_once 'lib/midtrans.php';
require_once 'includes/email-functions.php';

// ── Demo simulation endpoint (GET only, sandbox only) ──────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !MIDTRANS_IS_PRODUCTION) {
  $action = $_GET['simulate'] ?? '';
  $ref    = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($_GET['ref'] ?? ''));

  if ($action === 'paid' && $ref) {
    $db      = lnc_db();
    $booking = $db ? lnc_get_booking($ref) : null;

    // Determine which payment type to simulate based on booking status
    $ptype = 'deposit';
    if ($booking && $booking['status'] === 'deposit_paid') {
      $ptype = 'balance';
    }

    if ($db && $booking) {
      $order_id = $ref . ($ptype === 'deposit' ? '-DEP' : '-BAL');

      // Update payment record
      $db->prepare("
        UPDATE payments
        SET midtrans_status = 'settlement',
            midtrans_order_id = $1,
            midtrans_transaction_id = $2,
            payment_method = 'demo_simulation',
            paid_at = NOW()
        WHERE booking_ref = $3 AND payment_type = $4
      ")->execute([
        $order_id,
        'DEMO-TXN-' . strtoupper(substr(md5($ref . $ptype), 0, 8)),
        $ref,
        $ptype,
      ]);

      // Update booking status
      $new_status = ($ptype === 'deposit') ? 'deposit_paid' : 'confirmed';
      $db->prepare("UPDATE bookings SET status = $1, updated_at = NOW() WHERE ref = $2")
         ->execute([$new_status, $ref]);

      // Reload booking with updated data
      $booking = lnc_get_booking($ref);
    }

    // Send emails
    if ($booking) {
      if ($ptype === 'deposit') {
        lnc_send_deposit_confirmed($booking);
      } else {
        lnc_send_balance_confirmed($booking);
      }
    }

    header('Location: thank-you.php?ref=' . urlencode($ref));
    exit;
  }

  http_response_code(400);
  exit('Invalid simulation request.');
}

// ── Real Midtrans webhook (POST) ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

$raw  = file_get_contents('php://input');
$notif = json_decode($raw, true);

if (!$notif || !is_array($notif)) {
  http_response_code(200); // Always 200 to Midtrans
  exit;
}

// Signature verification
if (!lnc_verify_callback_signature($notif)) {
  http_response_code(200);
  exit;
}

$order_id          = $notif['order_id']          ?? '';
$transaction_status = $notif['transaction_status'] ?? '';
$fraud_status      = $notif['fraud_status']       ?? 'accept';
$payment_type      = $notif['payment_type']       ?? '';
$transaction_id    = $notif['transaction_id']     ?? '';

// Parse ref and type from order_id (e.g. LNC-2026-ABCDE-DEP)
if (!preg_match('/^(LNC-\d{4}-[A-Z0-9]{5})-(DEP|BAL)$/', $order_id, $m)) {
  http_response_code(200);
  exit;
}
$ref   = $m[1];
$ptype = $m[2] === 'DEP' ? 'deposit' : 'balance';

// Map Midtrans status to success/pending/failed
$success = false;
$failed  = false;

if ($transaction_status === 'capture' && $fraud_status === 'accept') {
  $success = true;
} elseif ($transaction_status === 'settlement') {
  $success = true;
} elseif (in_array($transaction_status, ['deny', 'cancel', 'expire', 'failure'])) {
  $failed = true;
}

if (!$success) {
  http_response_code(200);
  exit;
}

// Update DB
$db = lnc_db();
if (!$db) {
  http_response_code(200);
  exit;
}

// Update payment record
$db->prepare("
  UPDATE payments
  SET midtrans_status = $1,
      midtrans_transaction_id = $2,
      payment_method = $3,
      paid_at = NOW()
  WHERE booking_ref = $4 AND payment_type = $5
")->execute([
  $transaction_status,
  $transaction_id,
  $payment_type,
  $ref,
  $ptype,
]);

// Update booking status
$new_status = ($ptype === 'deposit') ? 'deposit_paid' : 'confirmed';
$db->prepare("UPDATE bookings SET status = $1, updated_at = NOW() WHERE ref = $2")
   ->execute([$new_status, $ref]);

// Send confirmation emails
$booking = lnc_get_booking($ref);
if ($booking) {
  if ($ptype === 'deposit') {
    lnc_send_deposit_confirmed($booking);
  } else {
    lnc_send_balance_confirmed($booking);
  }
}

http_response_code(200);
exit;
