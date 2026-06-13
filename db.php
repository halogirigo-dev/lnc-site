<?php
// db.php — PDO connection helper. Returns null gracefully if DB not configured.

function lnc_db(): ?PDO {
  static $pdo = null;
  static $tried = false;
  if ($tried) return $pdo;
  $tried = true;

  if (!defined('DB_NAME') || DB_NAME === 'PLACEHOLDER' || DB_HOST === 'PLACEHOLDER') {
    return null;
  }

  try {
    $pdo = new PDO(
      'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
      DB_USER,
      DB_PASS,
      [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ]
    );
  } catch (PDOException $e) {
    $pdo = null;
  }
  return $pdo;
}

// Fetch a single booking row by ref
function lnc_get_booking(string $ref): ?array {
  $db = lnc_db();
  if (!$db) return null;
  $st = $db->prepare('SELECT * FROM bookings WHERE ref = ? LIMIT 1');
  $st->execute([$ref]);
  return $st->fetch() ?: null;
}

// Fetch all payment rows for a booking ref
function lnc_get_payments(string $ref): array {
  $db = lnc_db();
  if (!$db) return [];
  $st = $db->prepare('SELECT * FROM payments WHERE booking_ref = ? ORDER BY created_at ASC');
  $st->execute([$ref]);
  return $st->fetchAll();
}

// Fetch the latest pending snap_token for a ref + type
function lnc_get_snap_token_from_db(string $ref, string $type): ?string {
  $db = lnc_db();
  if (!$db) return null;
  $st = $db->prepare(
    'SELECT snap_token FROM payments WHERE booking_ref = ? AND payment_type = ? AND snap_token IS NOT NULL ORDER BY created_at DESC LIMIT 1'
  );
  $st->execute([$ref, $type]);
  $row = $st->fetch();
  return $row ? $row['snap_token'] : null;
}
