<?php
if (file_exists(__DIR__ . '/.env')) require_once __DIR__ . '/env.php';

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

// Bank transfer details (update with real account before go-live)
define('BANK_NAME',    'Bank Central Asia (BCA)');
define('BANK_ACCOUNT', '1234567890');
define('BANK_HOLDER',  SITE_COMPANY);

// Fallback constants in case env.php was not loaded
if (!defined('SITE_URL'))             define('SITE_URL',             'https://lomboknatureculture.com');
if (!defined('MIDTRANS_SERVER_KEY'))  define('MIDTRANS_SERVER_KEY',  'SB-Mid-server-PLACEHOLDER');
if (!defined('MIDTRANS_CLIENT_KEY'))  define('MIDTRANS_CLIENT_KEY',  'SB-Mid-client-PLACEHOLDER');
if (!defined('MIDTRANS_IS_PRODUCTION')) define('MIDTRANS_IS_PRODUCTION', false);
if (!defined('MIDTRANS_SNAP_URL'))    define('MIDTRANS_SNAP_URL',    'https://app.sandbox.midtrans.com/snap/v1/transactions');
if (!defined('MIDTRANS_SNAP_JS'))     define('MIDTRANS_SNAP_JS',     'https://app.sandbox.midtrans.com/snap/snap.js');
if (!defined('DB_HOST'))              define('DB_HOST',              '127.0.0.1');
if (!defined('DB_NAME'))              define('DB_NAME',              'PLACEHOLDER');
if (!defined('DB_USER'))              define('DB_USER',              'PLACEHOLDER');
if (!defined('DB_PASS'))              define('DB_PASS',              'PLACEHOLDER');
