<?php
// Start session globally (safe to call multiple times)
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

// ─── SITE CONFIGURATION ───────────────────────────────────────
define('SITE_NAME',      'Lombok Nature Culture');
define('SITE_TAGLINE',   'Your Ethical Partner for Authentic Private Journeys');
define('SITE_COMPANY',   'PT Lombok Nature Culture');
define('SITE_EMAIL',     'hello@lnc-travel.com');
define('SITE_PHONE',     '+62 812-000-0000');
define('SITE_WA',        '6281200000000');
define('SITE_ADDRESS',   'Lombok, West Nusa Tenggara, Indonesia');
define('SITE_YEAR',      '2026');

// Asset paths (adjust if installed in subfolder e.g. /lnc)
define('BASE_URL',    '');
define('ASSETS_URL',  BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// Currency
define('CURRENCY',    'IDR');
define('USD_RATE',    16000); // 1 USD ≈ IDR 16,000
