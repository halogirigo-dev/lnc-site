<?php
// db.php — PDO connection helper (PostgreSQL). Returns null gracefully if DB not configured.

function lnc_db(): ?PDO {
  static $pdo = null;
  static $tried = false;
  if ($tried) return $pdo;
  $tried = true;

  if (!defined('DB_NAME') || DB_NAME === 'PLACEHOLDER' || DB_HOST === 'PLACEHOLDER') {
    return null;
  }

  $connection = defined('DB_CONNECTION') ? DB_CONNECTION : 'pgsql';
  $port       = defined('DB_PORT') ? DB_PORT : '5432';

  try {
    if ($connection === 'pgsql') {
      $dsn = "pgsql:host=" . DB_HOST . ";port={$port};dbname=" . DB_NAME;
    } else {
      // MySQL fallback for legacy installs
      $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }

    $pdo = new PDO(
      $dsn,
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

// Generate a sequential unique booking ref: LNC-YYYY-NNNNN
function lnc_generate_ref(PDO $db): string {
  $year   = date('Y');
  $prefix = "LNC-{$year}-";
  $st     = $db->prepare("SELECT COUNT(*) FROM bookings WHERE ref LIKE $1");
  $st->execute([$prefix . '%']);
  $n = (int)$st->fetchColumn();
  do {
    $n++;
    $ref  = $prefix . str_pad($n, 5, '0', STR_PAD_LEFT);
    $chk  = $db->prepare("SELECT 1 FROM bookings WHERE ref = $1");
    $chk->execute([$ref]);
  } while ($chk->fetchColumn());
  return $ref;
}

// Fetch a single booking row by ref
function lnc_get_booking(string $ref): ?array {
  $db = lnc_db();
  if (!$db) return null;
  $st = $db->prepare('SELECT * FROM bookings WHERE ref = $1 LIMIT 1');
  $st->execute([$ref]);
  return $st->fetch() ?: null;
}

// Fetch all payment rows for a booking ref
function lnc_get_payments(string $ref): array {
  $db = lnc_db();
  if (!$db) return [];
  $st = $db->prepare('SELECT * FROM payments WHERE booking_ref = $1 ORDER BY created_at ASC');
  $st->execute([$ref]);
  return $st->fetchAll();
}

// Fetch the latest snap_token for a ref + payment type
function lnc_get_snap_token_from_db(string $ref, string $type): ?string {
  $db = lnc_db();
  if (!$db) return null;
  $st = $db->prepare(
    'SELECT snap_token FROM payments WHERE booking_ref = $1 AND payment_type = $2 AND snap_token IS NOT NULL ORDER BY created_at DESC LIMIT 1'
  );
  $st->execute([$ref, $type]);
  $row = $st->fetch();
  return $row ? $row['snap_token'] : null;
}

// Fetch all active tour packages from DB (returns PHP array matching data.php structure)
function lnc_get_packages_from_db(): ?array {
  $db = lnc_db();
  if (!$db) return null;
  try {
    $st = $db->query('SELECT * FROM tour_packages WHERE is_active = TRUE ORDER BY sort_order ASC');
    $rows = $st->fetchAll();
    if (empty($rows)) return null;

    $short = [];
    $long  = [];
    $bali  = [];

    foreach ($rows as $row) {
      $pkg = [
        'id'          => $row['package_code'],
        'title'       => $row['title'],
        'subtitle'    => $row['subtitle'] ?? '',
        'duration'    => $row['duration'] ?? '',
        'category'    => $row['category'] ?? 'culture',
        'img'         => $row['image_path'] ?? '',
        'price'       => (int)$row['price_per_pax'],
        'price_label' => $row['price_label'] ?? '',
        'min_pax'     => (int)($row['min_pax'] ?? 2),
        'includes'    => is_string($row['includes'])    ? json_decode($row['includes'], true)    : ($row['includes'] ?? []),
        'excludes'    => is_string($row['excludes'])    ? json_decode($row['excludes'], true)    : ($row['excludes'] ?? []),
        'itinerary'   => is_string($row['itinerary'])   ? json_decode($row['itinerary'], true)   : ($row['itinerary'] ?? []),
      ];

      if (str_starts_with($row['package_code'], 'BALI')) {
        $bali[] = $pkg;
      } elseif ($row['is_long_stay']) {
        $long[] = $pkg;
      } else {
        $short[] = $pkg;
      }
    }

    return ['short' => $short, 'long' => $long, 'bali' => $bali];
  } catch (PDOException $e) {
    return null;
  }
}

// Fetch hotels from DB (returns PHP array matching data.php $hotels structure)
function lnc_get_hotels_from_db(): ?array {
  $db = lnc_db();
  if (!$db) return null;
  try {
    $zones = $db->query(
      'SELECT * FROM hotels WHERE is_active = TRUE ORDER BY sort_order ASC'
    )->fetchAll();

    if (empty($zones)) return null;

    $result = [];
    foreach ($zones as $zone) {
      $st = $db->prepare(
        'SELECT * FROM hotel_properties WHERE hotel_id = $1 AND is_active = TRUE ORDER BY sort_order ASC'
      );
      $st->execute([$zone['id']]);
      $props = $st->fetchAll();

      $result[] = [
        'zone'       => $zone['zone'],
        'area'       => $zone['area'] ?? '',
        'color'      => $zone['zone_color'] ?? '#2cb896',
        'properties' => array_map(fn($p) => [
          'img'     => $p['image_path'] ?? '',
          'name'    => $p['name'],
          'type'    => $p['type'] ?? '',
          'room'    => $p['room_type'] ?? '',
          'features'=> $p['features'] ?? '',
          'low'     => $p['price_low'] ?? '0',
          'high'    => $p['price_high'] ?? '0',
          'bf'      => $p['breakfast'] ?? '',
          'rating'  => $p['rating'] ?? '',
          'review'  => $p['review_text'] ?? '',
          'contact' => $p['contact'] ?? '',
        ], $props),
      ];
    }
    return $result;
  } catch (PDOException $e) {
    return null;
  }
}

// Fetch testimonials from DB
function lnc_get_testimonials_from_db(): ?array {
  $db = lnc_db();
  if (!$db) return null;
  try {
    $rows = $db->query(
      'SELECT * FROM testimonials WHERE is_active = TRUE ORDER BY sort_order ASC'
    )->fetchAll();
    if (empty($rows)) return null;
    return array_map(fn($r) => [
      'quote'      => $r['quote'],
      'name'       => $r['guest_name'] ?? '',
      'origin'     => $r['guest_origin'] ?? '',
      'experience' => $r['experience'] ?? '',
    ], $rows);
  } catch (PDOException $e) {
    return null;
  }
}

// Fetch team from DB
function lnc_get_team_from_db(): ?array {
  $db = lnc_db();
  if (!$db) return null;
  try {
    $rows = $db->query(
      'SELECT * FROM team_members WHERE is_active = TRUE ORDER BY sort_order ASC'
    )->fetchAll();
    if (empty($rows)) return null;
    return array_map(fn($r) => [
      'name'   => $r['name'],
      'role'   => $r['role'] ?? '',
      'spec'   => $r['specialization'] ?? '',
      'years'  => (int)($r['years_experience'] ?? 0),
      'origin' => $r['origin'] ?? '',
      'lang'   => $r['languages'] ?? '',
      'cert'   => $r['certifications'] ?? '',
      'bio'    => $r['bio'] ?? '',
    ], $rows);
  } catch (PDOException $e) {
    return null;
  }
}
