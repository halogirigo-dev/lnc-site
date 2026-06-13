<?php
// lib/midtrans.php — Midtrans Snap helper. No Composer required.

function lnc_is_demo_mode(): bool {
  return strpos(MIDTRANS_SERVER_KEY, 'PLACEHOLDER') !== false;
}

function lnc_get_snap_token(array $params): array {
  if (lnc_is_demo_mode()) {
    return ['token' => 'DEMO-' . strtoupper(substr(md5(uniqid('', true)), 0, 12)), 'demo' => true];
  }

  $ch = curl_init(MIDTRANS_SNAP_URL);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($params),
    CURLOPT_HTTPHEADER     => [
      'Content-Type: application/json',
      'Accept: application/json',
      'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
  ]);

  $response = curl_exec($ch);
  $errno    = curl_errno($ch);
  $err_msg  = curl_error($ch);
  $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($errno) {
    return ['error' => 'Could not connect to payment gateway: ' . $err_msg];
  }

  $data = json_decode($response, true);
  if (!isset($data['token'])) {
    $msg = $data['error_messages'][0] ?? ($data['message'] ?? 'Unknown Midtrans error');
    return ['error' => $msg];
  }

  return ['token' => $data['token']];
}

function lnc_verify_callback_signature(array $notif): bool {
  if (lnc_is_demo_mode()) return true;

  $expected = hash('sha512',
    ($notif['order_id']     ?? '') .
    ($notif['status_code']  ?? '') .
    ($notif['gross_amount'] ?? '') .
    MIDTRANS_SERVER_KEY
  );
  return hash_equals($expected, $notif['signature_key'] ?? '');
}

function lnc_format_snap_params(array $booking, string $type): array {
  $is_deposit = ($type === 'deposit');
  $amount     = $is_deposit ? ($booking['deposit_amount'] ?? 0) : ($booking['balance_amount'] ?? 0);
  $order_id   = ($booking['ref'] ?? '') . ($is_deposit ? '-DEP' : '-BAL');
  $label      = $is_deposit
    ? 'Deposit 30% — ' . ($booking['package_title'] ?? 'LNC Package')
    : 'Balance 70% — ' . ($booking['package_title'] ?? 'LNC Package');

  return [
    'transaction_details' => [
      'order_id'    => $order_id,
      'gross_amount' => (int)$amount,
    ],
    'customer_details' => [
      'first_name' => $booking['name'] ?? '',
      'email'      => $booking['email'] ?? '',
      'phone'      => $booking['phone'] ?? '',
    ],
    'item_details' => [[
      'id'       => $order_id,
      'price'    => (int)$amount,
      'quantity' => 1,
      'name'     => substr($label, 0, 50),
    ]],
    'callbacks' => [
      'finish' => SITE_URL . '/payment-finish.php?ref=' . urlencode($booking['ref'] ?? ''),
    ],
    'expiry' => [
      'start_time' => date('Y-m-d H:i:s O'),
      'unit'       => 'hours',
      'duration'   => 24,
    ],
  ];
}
