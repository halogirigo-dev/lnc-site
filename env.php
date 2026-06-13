<?php
// env.php — Loads .env and defines constants. Never crash if .env is missing.

$_env_path = __DIR__ . '/.env';
$_env = [];

if (file_exists($_env_path)) {
  foreach (file($_env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
    $_line = trim($_line);
    if ($_line === '' || $_line[0] === '#') continue;
    if (strpos($_line, '=') === false) continue;
    [$_k, $_v] = explode('=', $_line, 2);
    $_env[trim($_k)] = trim($_v);
  }
}

function _env(string $key, string $default): string {
  global $_env;
  return $_env[$key] ?? $default;
}

if (!defined('MIDTRANS_SERVER_KEY'))
  define('MIDTRANS_SERVER_KEY', _env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-PLACEHOLDER'));

if (!defined('MIDTRANS_CLIENT_KEY'))
  define('MIDTRANS_CLIENT_KEY', _env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-PLACEHOLDER'));

if (!defined('MIDTRANS_IS_PRODUCTION'))
  define('MIDTRANS_IS_PRODUCTION', filter_var(_env('MIDTRANS_IS_PRODUCTION', 'false'), FILTER_VALIDATE_BOOLEAN));

if (!defined('MIDTRANS_SNAP_URL'))
  define('MIDTRANS_SNAP_URL', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions');

if (!defined('MIDTRANS_SNAP_JS'))
  define('MIDTRANS_SNAP_JS', MIDTRANS_IS_PRODUCTION
    ? 'https://app.midtrans.com/snap/snap.js'
    : 'https://app.sandbox.midtrans.com/snap/snap.js');

if (!defined('DB_CONNECTION')) define('DB_CONNECTION', _env('DB_CONNECTION', 'pgsql'));
if (!defined('DB_HOST'))       define('DB_HOST',       _env('DB_HOST',       '127.0.0.1'));
if (!defined('DB_PORT'))       define('DB_PORT',       _env('DB_PORT',       '5432'));
if (!defined('DB_NAME'))       define('DB_NAME',       _env('DB_NAME',       'PLACEHOLDER'));
if (!defined('DB_USER'))       define('DB_USER',       _env('DB_USER',       'PLACEHOLDER'));
if (!defined('DB_PASS'))       define('DB_PASS',       _env('DB_PASS',       'PLACEHOLDER'));
if (!defined('SITE_URL'))      define('SITE_URL',      _env('SITE_URL',      'https://lomboknatureculture.com'));
