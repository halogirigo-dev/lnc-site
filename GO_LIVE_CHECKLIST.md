# GO_LIVE_CHECKLIST — Lombok Nature Culture
**Audit Date:** 2026-06-14  
**Auditor:** Production Readiness Review  
**Scope:** All customer journeys simulated end-to-end

---

## Summary

| Severity | Count | Blocks Go-Live? |
|---|---|---|
| Critical | 7 | YES — site broken without these |
| High | 9 | YES — data loss or security exposure |
| Medium | 9 | NO — degraded experience |
| Low | 6 | NO — minor polish |

---

## Critical Issues

> These prevent the site from functioning at all. Nothing works until these are resolved.

---

### C-01 · `public_html/.env` — All credentials are PLACEHOLDER
**Severity:** Critical  
**Journey affected:** Every journey (booking, payment, admin, DB)

**Finding:** The production `.env` file has 6 placeholder values:
```
MIDTRANS_SERVER_KEY=SB-Mid-server-PLACEHOLDER   ← demo mode stays on
MIDTRANS_CLIENT_KEY=SB-Mid-client-PLACEHOLDER
DB_NAME=PLACEHOLDER                              ← DB never connects
DB_USER=PLACEHOLDER
DB_PASS=PLACEHOLDER
DEPLOY_SECRET=PLACEHOLDER                        ← CI/CD deploy fails
```

**Impact chain:**
- `lnc_is_demo_mode()` returns `true` because `MIDTRANS_SERVER_KEY` contains 'PLACEHOLDER'
- Demo mode: payment button is disabled, Snap tokens are fake (`DEMO-xxxx`), signature verification is bypassed
- `lnc_db()` returns `null` because `DB_NAME = 'PLACEHOLDER'` — all DB operations silently no-op
- `check-status.php` always returns `{"status":"unknown"}` — payment-finish.php shows the timeout/WhatsApp fallback for every real payment

**Fix:** Replace all PLACEHOLDERs with real production values before any live testing.

---

### C-02 · `backend/.env` — Missing entirely
**Severity:** Critical  
**Journey affected:** Admin panel, API endpoints, all Laravel routes

**Finding:** `backend/.env` does not exist. Only `backend/.env.example` is present. Laravel refuses to boot without `.env`. The admin panel at `/admin`, all REST API endpoints at `/api/v1/*`, and the queue worker are completely non-functional.

**Fix:**
```bash
cp backend/.env.example backend/.env
# Then fill in DB_PASSWORD, MIDTRANS keys, MAIL_PASSWORD, APP_KEY
php artisan key:generate
```

---

### C-03 · `backend/vendor/` — Composer packages not installed
**Severity:** Critical  
**Journey affected:** Admin panel, API endpoints

**Finding:** `backend/vendor/` directory is absent. Laravel and Filament have not been installed via Composer. The autoloader does not exist. Any HTTP request to `/admin` or `/api/v1/` will result in a 500 error or blank page.

**Fix:**
```bash
cd backend/
composer install --no-dev --optimize-autoloader
```

---

### C-04 · Database migrations not run
**Severity:** Critical  
**Journey affected:** Booking creation, payment processing, invoice generation, admin panel data

**Finding:** `php artisan migrate` has never been run. None of the 13 tables (`bookings`, `payments`, `tour_packages`, `hotels`, etc.) exist in PostgreSQL. Additionally:
- `SESSION_DRIVER=database` in `backend/.env.example` requires a `sessions` table (`php artisan session:table && php artisan migrate`)
- `CACHE_STORE=database` requires a `cache` table (`php artisan cache:table && php artisan migrate`)
- Neither of these tables has a migration file in `database/migrations/`

**Fix:**
```bash
# Generate missing core Laravel table migrations
php artisan session:table
php artisan cache:table
# Run all migrations
php artisan migrate --force
php artisan db:seed --force
```

---

### C-05 · No Filament admin user exists
**Severity:** Critical  
**Journey affected:** Admin panel — all admin visibility of bookings, payments, content

**Finding:** `php artisan db:seed` only seeds content tables (packages, hotels, team, etc.). No admin `users` table row exists. The admin panel login at `/admin/login` will reject all attempts because the `users` table is empty.

**Fix:**
```bash
php artisan make:filament-user
# Or set ADMIN_EMAIL/ADMIN_PASSWORD in .env and add a UserSeeder
```

---

### C-06 · Midtrans webhook URL not configured in Midtrans dashboard
**Severity:** Critical  
**Journey affected:** Payment status updates, deposit/balance confirmation emails, booking status transitions

**Finding:** The Midtrans dashboard must have the webhook URL registered for payment notifications to arrive at `payment-callback.php`. Without this registration:
- `payment-callback.php` never receives POST notifications
- Booking status never transitions from `pending_payment` → `deposit_paid` → `confirmed`
- Confirmation emails (`lnc_send_deposit_confirmed`, `lnc_send_balance_confirmed`) are never triggered
- Admin never receives payment alert emails
- `payment-finish.php` polls `check-status.php` for 30 seconds, times out, and shows "WhatsApp us to confirm"

**Fix:** Log into Midtrans dashboard → Settings → Configuration → set Payment Notification URL to:
```
https://lomboknatureculture.com/payment-callback.php
```

---

### C-07 · `MIDTRANS_IS_PRODUCTION` must be `true` before go-live
**Severity:** Critical  
**Journey affected:** All payment flows

**Finding:** `public_html/.env` has `MIDTRANS_IS_PRODUCTION=false`. This routes all Snap API calls to `app.sandbox.midtrans.com` and loads the sandbox Snap JS. Real production keys will be rejected by the sandbox endpoint. All payment attempts on production with real keys will fail silently.

Additionally, when `MIDTRANS_IS_PRODUCTION=false`:
- `lnc_verify_callback_signature()` always returns `true` (no HMAC check) — any POST to `payment-callback.php` would update booking status
- Snap JS loads from sandbox CDN

**Fix:**
```
# In public_html/.env
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SNAP_URL=https://app.midtrans.com/snap/v1/transactions
```
(These are set automatically by `env.php` when `MIDTRANS_IS_PRODUCTION=true` — just set the flag.)

---

## High Issues

> These cause data loss, security exposure, or severely broken UX for real customers.

---

### H-01 · WordPress blocking rules absent from `.htaccess`
**Severity:** High  
**Journey affected:** Site security

**Finding:** The `SECURITY_REPORT.md` states "Block all WordPress URLs via .htaccess ✅ Applied" but the actual `public_html/.htaccess` file contains **no WordPress-specific blocking rules**. There are no `RewriteRule` blocks for `/wp-admin/`, `/wp-login.php`, `/xmlrpc.php`, or `wp-*.php` files.

If WordPress files remain on the server (as they do — the report notes they were never deleted), these paths are actively accessible and exploitable. `xmlrpc.php` alone is a DDoS amplification vector that attracts automated attacks 24/7.

**Fix — add to `.htaccess` before the Pretty URLs section:**
```apache
# ── Block WordPress attack vectors ────────────────────────────────────────────
RewriteRule ^wp-login\.php           - [F,L]
RewriteRule ^xmlrpc\.php             - [F,L]
RewriteRule ^wp-admin(/.*)?$         - [F,L]
RewriteRule ^wp-includes/(.*)        - [F,L]
RewriteRule ^wp-content/(.*)         - [F,L]
<FilesMatch "^wp-.*\.php$">
  Require all denied
</FilesMatch>
```
**Or (preferred):** Delete all WordPress files from the server entirely.

---

### H-02 · `lnc_tmp/` directory blocking absent from `.htaccess`
**Severity:** High  
**Journey affected:** Site security

**Finding:** The Security Report lists "Block `lnc_tmp/` directory ✅ Applied" but there is no such rule in the `.htaccess`. If the `lnc_tmp/` directory exists on the server and contains uploaded or cached files, it is publicly browsable (directory listing is disabled via `Options -Indexes` but files remain directly accessible by URL).

**Fix — add to `.htaccess`:**
```apache
RewriteRule ^lnc_tmp/.*$ - [F,L]
```

---

### H-03 · `config.php` — placeholder contact details on every customer-facing page
**Severity:** High  
**Journey affected:** All customer journeys — booking, email notifications, thank-you, invoice, WhatsApp CTAs

**Finding:** `config.php` contains placeholder values that appear in customer emails, invoice PDFs, and WhatsApp links:
```php
define('SITE_PHONE', '+62 812-000-0000');   // ← fake number
define('SITE_WA',    '6281200000000');       // ← fake WhatsApp
define('BANK_NAME',  'Bank Central Asia (BCA)');
define('BANK_ACCOUNT', '1234567890');       // ← PLACEHOLDER account
define('BANK_HOLDER', SITE_COMPANY);
```

Every "WhatsApp Us" button on the site (booking page, thank-you, invoice, payment pages) links to a non-existent WhatsApp number. Bank transfer details shown on the invoice Stage 2 are fake. Customers cannot pay via bank transfer.

**Fix:** Update `config.php` with real phone, WhatsApp, and bank account details before any public-facing testing.

---

### H-04 · `config.php` `SITE_EMAIL` domain mismatch
**Severity:** High  
**Journey affected:** All admin email notifications (booking requests, payment confirmations)

**Finding:**
```php
define('SITE_EMAIL', 'hello@lnc-travel.com');  // ← in config.php
```
But `backend/.env.example` uses `hello@lomboknatureculture.com` for `MAIL_FROM_ADDRESS`. The domains are different: `lnc-travel.com` vs `lomboknatureculture.com`.

**Impact:**
- All admin notification emails (new booking requests, deposit paid, balance paid) are sent to `hello@lnc-travel.com`
- If `lnc-travel.com` is not a real inbox the team monitors, no admin will know when bookings arrive

Additionally, the from-address in `process-booking.php` quote emails is `noreply@lomboknatureculture.com` — if `lomboknatureculture.com` doesn't have SPF/DKIM configured on Hostinger, all outgoing emails land in spam.

**Fix:** Verify which email the team uses. Set `SITE_EMAIL` to the monitored inbox. Configure SPF/DKIM for `lomboknatureculture.com` in DNS.

---

### H-05 · Failed Midtrans payments do not update booking status
**Severity:** High  
**Journey affected:** Booking status machine, admin visibility, abandoned bookings

**Finding:** In `payment-callback.php` (line 113–116):
```php
if (!$success) {
  http_response_code(200);
  exit;
}
```
When Midtrans sends a `deny`, `cancel`, `expire`, or `failure` notification, the callback returns HTTP 200 (correctly acknowledging receipt) but **does not update the booking or payment record**. 

**Impact:**
- Bookings with expired or denied Midtrans tokens remain in `pending_payment` status indefinitely
- Admin sees a growing backlog of `pending_payment` bookings with no way to distinguish abandoned from in-progress
- Snap tokens expire after 24 hours (configured in `lnc_format_snap_params`) — after expiry, the same snap_token stored in DB can no longer be used and no new token is generated automatically

**Fix — add before the `if (!$success)` block:**
```php
if ($failed) {
  $db = lnc_db();
  if ($db) {
    $db->prepare("UPDATE payments SET midtrans_status = $1, updated_at = NOW() WHERE booking_ref = $2 AND payment_type = $3")
       ->execute([$transaction_status, $ref, $ptype]);
    $db->prepare("UPDATE bookings SET status = 'payment_failed', updated_at = NOW() WHERE ref = $1")
       ->execute([$ref]);
  }
}
```

---

### H-06 · Midtrans webhook has no idempotency guard
**Severity:** High  
**Journey affected:** Deposit and balance confirmation emails

**Finding:** `payment-callback.php` sends `lnc_send_deposit_confirmed()` or `lnc_send_balance_confirmed()` on every successful webhook call. Midtrans retries webhook delivery up to several times if it doesn't receive a timely HTTP 200 response (e.g., due to slow email dispatch via `mail()`). This causes **duplicate confirmation emails** to be sent to the customer for the same payment.

**Fix — check payment status before sending email:**
```php
// Before sending emails, check if already processed
$existing = $db->prepare("SELECT midtrans_status FROM payments WHERE booking_ref = $1 AND payment_type = $2 AND midtrans_status = 'settlement'")
               ->execute([$ref, $ptype]);
$already_settled = $db->fetch();
if (!$already_settled) {
    // update DB, then send emails
}
```

---

### H-07 · `backend/.env.example` — `APP_KEY` is blank, `SESSION_DRIVER=database` requires missing migration
**Severity:** High  
**Journey affected:** Admin panel login, all Filament sessions

**Finding:** Two specific issues in `backend/.env.example`:

1. `APP_KEY=` is empty — Laravel won't encrypt sessions or run CSRF without this. `php artisan key:generate` must be run immediately after copying `.env.example` to `.env`.

2. `SESSION_DRIVER=database` and `CACHE_STORE=database` — both require Laravel migration tables (`sessions`, `cache`) that are **not** in the existing 13 migration files. The admin panel will throw a database error on every login attempt because the `sessions` table doesn't exist.

**Fix:**
```bash
php artisan session:table
php artisan cache:table
php artisan migrate --force
```

---

### H-08 · `public_html/create-tables.php` and `.bak` file remain in webroot
**Severity:** High  
**Journey affected:** Security

**Finding:** Two files that should be removed post-setup remain in the webroot:
- `create-tables.php` — blocked by `.htaccess` but only if `mod_headers` is active. If the Apache module is inactive, direct access executes the DDL script with a predictable token (`LNC-DB-SETUP-2026`). This is a data-loss risk.
- `single-lnc_package.php.bak` — blocked by `.htaccess` `.bak` extension rule, but exposes raw PHP source if that rule fails

**Fix:** Delete both files from the server after initial DB setup:
```bash
rm public_html/create-tables.php
rm public_html/single-lnc_package.php.bak
```

---

### H-09 · No SPF / DKIM / DMARC configured for outgoing `mail()` calls
**Severity:** High  
**Journey affected:** All customer email notifications (booking confirmation, deposit confirmed, balance confirmed)

**Finding:** All transactional emails from the PHP frontend are sent via PHP `mail()` using `From: noreply@lomboknatureculture.com` and `From: hello@lomboknatureculture.com`. These go through Hostinger's sendmail. Without SPF/DKIM records on the `lomboknatureculture.com` DNS zone, major email providers (Gmail, Outlook, Yahoo) classify these as spam or reject them outright.

**Impact:** Customers never receive booking confirmations. Deposit/balance confirmation emails go to spam. Admin never receives new booking alerts.

**Fix:**
1. In Hostinger DNS, add SPF record: `v=spf1 include:hostinger.com ~all`
2. Enable DKIM in Hostinger Email settings and add the DKIM DNS record
3. Add DMARC: `v=DMARC1; p=none; rua=mailto:dmarc@lomboknatureculture.com`

---

## Medium Issues

> These don't block go-live but cause customer confusion or degraded experience.

---

### M-01 · `booking.php` form `action` attribute points to wrong URL
**Severity:** Medium  
**Journey affected:** Booking creation — non-JS users

**Finding:** `booking.php` line 66:
```html
<form id="booking-form" method="POST" action="thank-you.php">
```
The JavaScript in `main.js` (line 348) overrides the action to `process-booking.php` immediately before submit:
```javascript
bookingForm.action = 'process-booking.php';
bookingForm.submit();
```
This works when JS is running. If JS fails to load (slow network, content blocker, error in an earlier JS section), the form submits to `thank-you.php` which renders the page without processing the booking — the customer sees a confirmation screen but no booking record was created.

**Fix:**
```html
<form id="booking-form" method="POST" action="process-booking.php">
```
The JS override is harmless to keep as a belt-and-suspenders safeguard.

---

### M-02 · `invoice.php` hardcodes agent name
**Severity:** Medium  
**Journey affected:** Invoice / Proposal document (all 3 stages)

**Finding:** `invoice.php` line 86:
```php
'agent' => 'Arief Hidayat',
```
This name appears on every customer-facing proposal ("Prepared by Arief Hidayat — Lead Guide & Founder") and all three invoice stages. If Arief Hidayat is not the correct person, or if the company wants a different signatory, all customer documents are wrong.

**Fix:** Either confirm this is the correct name and title, or make it a constant in `config.php`:
```php
define('SITE_FOUNDER', 'Arief Hidayat');
define('SITE_FOUNDER_TITLE', 'Lead Guide & Founder');
```

---

### M-03 · CSP hardcodes sandbox Midtrans domain
**Severity:** Medium  
**Journey affected:** Payment pages in production

**Finding:** `.htaccess` Content-Security-Policy header (line 60) statically includes:
```
script-src 'self' 'unsafe-inline' https://app.sandbox.midtrans.com https://app.midtrans.com;
connect-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com;
frame-src https://app.sandbox.midtrans.com https://app.midtrans.com;
```
In production (`MIDTRANS_IS_PRODUCTION=true`), all Snap JS and API calls go to `app.midtrans.com`. The sandbox domains are unnecessary and slightly widen the CSP attack surface.

**Fix:** Remove `https://app.sandbox.midtrans.com` from all CSP directives for production. Replace the hardcoded CSP in `.htaccess` with:
```apache
script-src 'self' 'unsafe-inline' https://app.midtrans.com;
connect-src 'self' https://api.midtrans.com https://app.midtrans.com;
frame-src https://app.midtrans.com;
```

---

### M-04 · HSTS header is commented out
**Severity:** Medium  
**Journey affected:** HTTPS enforcement

**Finding:** `.htaccess` line 64:
```apache
# Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```
HSTS is disabled. Browsers will not remember to upgrade HTTP → HTTPS connections, leaving users vulnerable to SSL-stripping attacks on first visits.

**Fix:** Once SSL certificate is confirmed stable (Let's Encrypt via Certbot), uncomment this line.

---

### M-05 · `payment-finish.php` — `$ref` not regex-validated before use in page output
**Severity:** Medium  
**Journey affected:** Payment finish page

**Finding:** `payment-finish.php` line 3:
```php
$ref = trim($_GET['ref'] ?? '');
```
Unlike all other payment pages which apply `preg_replace('/[^A-Z0-9\-]/', '', strtoupper(...))`, this file only trims whitespace. The `$ref` then appears in:
- The WA link `href` (low risk — WhatsApp escapes it)
- `json_encode($ref)` in the JS (safe — json_encode escapes)
- An `href` attribute for `invoice.php?ref=...` (low risk)

Not an active exploit path due to `json_encode` and `urlencode` wrappers, but inconsistent with all other files.

**Fix:**
```php
$ref = preg_replace('/[^A-Z0-9\-]/', '', strtoupper(trim($_GET['ref'] ?? '')));
```

---

### M-06 · `process-booking.php` — Snap token API failure silently degrades to quote flow
**Severity:** Medium  
**Journey affected:** Booking with price → payment flow

**Finding:** In `process-booking.php` lines 181–208:
```php
if ($b['package_price'] > 0 && $db) {
    $snap_result = lnc_get_snap_token($snap_params);
    if (!isset($snap_result['error'])) {
        // ... redirect to payment.php
    }
    // Snap token failed — fall through to quote flow
}
_send_new_request_emails($b, $ref);
header("Location: thank-you.php?...&type=pending");
```
If the Midtrans API call fails (network error, invalid credentials, API downtime), a booking with price is silently treated as a quote: admin gets a "new request" email, guest sees the quote thank-you page with no payment link. The booking record is in the DB with `status='pending_payment'` but no payment record exists.

**Fix:** Show the customer an explicit error and the WhatsApp fallback rather than silently redirecting to the quote page. Add an error flag:
```php
header("Location: booking.php?error=payment_unavailable");
```

---

### M-07 · WordPress files on server — never deleted
**Severity:** Medium  
**Journey affected:** Security / attack surface

**Finding:** The Security Report noted WordPress files should be deleted from the server but this is an outstanding server-side action. The `.htaccess` blocks are the only mitigation, and H-01 above shows those blocks are also missing. Full WordPress installation (wp-admin, wp-includes, wp-content, xmlrpc.php) remains present and unprotected.

**Fix:** SSH into the VPS and delete:
```bash
rm -rf public_html/wp-admin public_html/wp-includes public_html/wp-content
rm -f public_html/wp-*.php public_html/xmlrpc.php public_html/readme.html public_html/license.txt
```

---

### M-08 · Admin notifications not sent for new paid bookings until webhook fires
**Severity:** Medium  
**Journey affected:** Admin awareness of new bookings

**Finding:** When a customer submits booking form with a package price:
1. Booking is created in DB (`status=pending_payment`) — **no admin email sent**
2. Customer is redirected to `payment.php`
3. Customer pays → Midtrans webhook fires → **admin email sent** (`lnc_send_admin_alert`)

If the customer abandons the payment page (common e-commerce behavior), admin never knows a booking attempt was made. The Filament admin panel shows it, but only if admins actively check.

**Fix (recommended):** Add a brief admin notification in `process-booking.php` after DB insert for priced bookings:
```php
// After DB insert succeeds, notify admin
mail(SITE_EMAIL, "[New Booking Pending Payment] {$ref}", "Guest {$b['name']} has started a booking...", $hdrs);
```

---

### M-09 · `USD_RATE` is hardcoded and will become stale
**Severity:** Medium  
**Journey affected:** USD price display on package pages

**Finding:** `config.php` line 34:
```php
define('USD_RATE', 16000); // 1 USD ≈ IDR 16,000
```
Currency rates fluctuate. If the IDR/USD rate moves significantly, displayed USD prices will be wrong, potentially misleading international customers.

**Fix:** Either remove USD display entirely (prices shown in IDR only), or load the rate from an environment variable so it can be updated without a code deploy:
```php
define('USD_RATE', (int)_env('USD_RATE', '16000'));
```

---

## Low Issues

> Minor polish items that don't affect functionality.

---

### L-01 · `booking.php` — Bali packages excluded from booking form
**Severity:** Low  
**Journey affected:** Booking — Bali package customers

**Finding:** `booking.php` line 74:
```php
foreach (array_merge($packages_short, $packages_long) as $pkg) {
```
`$packages_bali` is **not included** in the booking form package list. Customers who discover Bali packages from the experiences page and click "Book Now" would see all packages except the Bali one in the booking form.

**Fix:**
```php
foreach (array_merge($packages_short, $packages_long, $packages_bali) as $pkg) {
```

---

### L-02 · `check-status.php` — no CORS headers for fetch polyfill environments
**Severity:** Low  
**Journey affected:** `payment-finish.php` payment status poll on older browsers

**Finding:** `payment-finish.php` uses `fetch()` to poll `check-status.php`. The `Cache-Control: no-store` header is set but no explicit CORS headers are needed (same-origin). However, if the site is ever served via a CDN that modifies request origins, the fetch would silently fail.

No immediate action required; just document as a future consideration.

---

### L-03 · `backend/` Laravel `config/services.php` not created
**Severity:** Low  
**Journey affected:** Any future Laravel-side Midtrans integration

**Finding:** A `config/services.php` file for Midtrans credentials was not created in the backend. The existing API controllers read Midtrans keys from the PHP frontend `.env` (via `config.php`). If Laravel-side payment processing is ever needed, the service configuration must be added explicitly.

No immediate impact — all current Midtrans calls go through PHP frontend.

---

### L-04 · Email `From` address not consistent across files
**Severity:** Low  
**Journey affected:** Email deliverability / professionalism

**Finding:** Three different from-addresses are used across email sending code:
- `process-booking.php` quote email: `From: noreply@lomboknatureculture.com`
- `process-booking.php` guest confirmation: `From: hello@lomboknatureculture.com`
- `email-functions.php` all payment emails: `From: PT Lombok Nature Culture <noreply@lomboknatureculture.com>`

Inconsistency in From: addresses can cause spam classification differences and confuses customers who try to reply.

**Fix:** Standardise to one from-address across all outgoing mail.

---

### L-05 · `SITE_COMPANY` legal entity not verified
**Severity:** Low  
**Journey affected:** Invoice documents, email footers

**Finding:** `config.php`:
```php
define('SITE_COMPANY', 'PT Lombok Nature Culture');
```
"PT" is an Indonesian limited company (Perseroan Terbatas). This name appears on all invoices and legal documents sent to customers. Verify this matches the legal registration exactly before customer-facing use.

---

### L-06 · `create_autologin_*.php` Hostinger file not confirmed deleted
**Severity:** Low  
**Journey affected:** Security

**Finding:** The Security Report noted Hostinger's autologin file `create_autologin_ldqtx6y1zvlniijufvtyvvg68229jhqh.php` self-deletes on first execution, but if PHP execution ever failed, the file may remain. The `.htaccess` blocks the filename pattern. Verify on the server:
```bash
ls public_html/create_autologin_*.php 2>/dev/null && echo "DELETE IT" || echo "Already gone"
```

---

## Pre-Launch Execution Order

Perform these steps in strict sequence:

### Phase 1 — Server Setup (VPS SSH)
```bash
# 1. Install PHP extensions
sudo apt install php8.2-pgsql php8.2-curl php8.2-mbstring php8.2-xml -y

# 2. Install Composer
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer

# 3. Clone/upload repo to /var/www/lnc/

# 4. Delete WordPress files (M-07)
rm -rf public_html/wp-admin public_html/wp-includes public_html/wp-content
rm -f public_html/wp-*.php public_html/xmlrpc.php

# 5. Delete sensitive webroot files (H-08)
rm public_html/create-tables.php
rm public_html/single-lnc_package.php.bak
```

### Phase 2 — Configuration
```bash
# 6. Set public_html/.env (C-01, C-07, H-03, H-04)
nano public_html/.env
# Set: DB_NAME, DB_USER, DB_PASS, MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY
# Set: MIDTRANS_IS_PRODUCTION=true, DEPLOY_SECRET

# 7. Set backend/.env (C-02)
cp backend/.env.example backend/.env
nano backend/.env
# Set: DB_PASSWORD, MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY, MAIL_PASSWORD

# 8. Install Laravel (C-03)
cd backend/
composer install --no-dev --optimize-autoloader
php artisan key:generate   # (C-07, H-07)

# 9. Create sessions and cache tables, run all migrations (C-04, H-07)
php artisan session:table
php artisan cache:table
php artisan migrate --force
php artisan db:seed --force

# 10. Create admin user (C-05)
php artisan make:filament-user

# 11. Laravel optimise
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link
```

### Phase 3 — DNS & Security
```bash
# 12. Configure Nginx (deploy/nginx.conf)
# 13. Install SSL (Certbot)
sudo certbot --nginx -d lomboknatureculture.com -d www.lomboknatureculture.com

# 14. Enable HSTS in .htaccess (M-04) — after confirming HTTPS works
# Uncomment: Header always set Strict-Transport-Security ...

# 15. Add WordPress blocking rules to .htaccess (H-01, H-02)

# 16. Configure DNS: SPF, DKIM, DMARC for lomboknatureculture.com (H-09)
```

### Phase 4 — Real Credentials & External Services
```bash
# 17. Update config.php with real phone/WA/bank details (H-03)
# 18. Verify SITE_EMAIL is the correct monitored inbox (H-04)
# 19. Register webhook URL in Midtrans dashboard (C-06)
#     URL: https://lomboknatureculture.com/payment-callback.php
# 20. Fix booking.php form action (M-01)
```

### Phase 5 — Smoke Test (before public launch)
```bash
# 21. Submit a test booking for a free/quote-only package
#     → Confirm DB row in bookings table
#     → Confirm admin email arrives at SITE_EMAIL
#     → Confirm guest confirmation email arrives
#     → Confirm thank-you page variant='quote' shows

# 22. Submit a test booking for a paid package (sandbox Midtrans)
#     → Confirm redirect to payment.php with real Snap token
#     → Simulate payment in Midtrans sandbox
#     → Confirm webhook fires to payment-callback.php
#     → Confirm booking.status = 'deposit_paid'
#     → Confirm lnc_send_deposit_confirmed email arrives
#     → Confirm thank-you page variant='deposit' shows
#     → Confirm invoice.php Stage 2 shows "PAID ✓"

# 23. Test balance payment flow
#     → payment-balance.php renders for deposit_paid booking
#     → Simulate balance payment
#     → Confirm booking.status = 'confirmed'
#     → Confirm lnc_send_balance_confirmed email arrives
#     → Confirm invoice.php Stage 3 shows "PAID IN FULL"

# 24. Test admin panel at /admin
#     → Login works
#     → Bookings resource shows test bookings
#     → Payments resource shows payment records
#     → Status update works (pending → confirmed)

# 25. Flip MIDTRANS_IS_PRODUCTION=true, set production keys, re-test step 22
```

---

## Issue Register (Machine-Readable)

| ID | Severity | File | Issue | Status |
|---|---|---|---|---|
| C-01 | Critical | `public_html/.env` | All credentials PLACEHOLDER | ❌ Open |
| C-02 | Critical | `backend/.env` | Missing entirely | ❌ Open |
| C-03 | Critical | `backend/vendor/` | Composer packages not installed | ❌ Open |
| C-04 | Critical | PostgreSQL | Migrations never run | ❌ Open |
| C-05 | Critical | Filament users | No admin user exists | ❌ Open |
| C-06 | Critical | Midtrans dashboard | Webhook URL not registered | ❌ Open |
| C-07 | Critical | `public_html/.env` | `MIDTRANS_IS_PRODUCTION=false` | ❌ Open |
| H-01 | High | `.htaccess` | WordPress blocking rules missing | ❌ Open |
| H-02 | High | `.htaccess` | `lnc_tmp/` blocking missing | ❌ Open |
| H-03 | High | `config.php` | Phone, WA, bank account placeholders | ❌ Open |
| H-04 | High | `config.php` | `SITE_EMAIL` domain mismatch | ❌ Open |
| H-05 | High | `payment-callback.php` | Failed payment status not recorded | ❌ Open |
| H-06 | High | `payment-callback.php` | No idempotency guard on emails | ❌ Open |
| H-07 | High | `backend/.env.example` | `APP_KEY` blank, sessions table missing | ❌ Open |
| H-08 | High | `public_html/` | `create-tables.php` and `.bak` in webroot | ❌ Open |
| H-09 | High | DNS | SPF/DKIM/DMARC not configured | ❌ Open |
| M-01 | Medium | `booking.php` | Form action is `thank-you.php` not `process-booking.php` | ❌ Open |
| M-02 | Medium | `invoice.php` | Agent name hardcoded | ❌ Open |
| M-03 | Medium | `.htaccess` | CSP includes sandbox Midtrans domain | ❌ Open |
| M-04 | Medium | `.htaccess` | HSTS commented out | ❌ Open |
| M-05 | Medium | `payment-finish.php` | `$ref` not regex-sanitized | ❌ Open |
| M-06 | Medium | `process-booking.php` | Snap API failure silently → quote flow | ❌ Open |
| M-07 | Medium | VPS server | WordPress files not deleted | ❌ Open |
| M-08 | Medium | `process-booking.php` | No admin notification for new priced bookings | ❌ Open |
| M-09 | Medium | `config.php` | `USD_RATE` hardcoded | ❌ Open |
| L-01 | Low | `booking.php` | Bali packages excluded from booking form | ❌ Open |
| L-02 | Low | `check-status.php` | No CORS headers documented | ❌ Open |
| L-03 | Low | `backend/config/` | No `services.php` for Midtrans | ❌ Open |
| L-04 | Low | Email functions | Inconsistent From: addresses | ❌ Open |
| L-05 | Low | `config.php` | Legal entity name unverified | ❌ Open |
| L-06 | Low | VPS server | Autologin file removal unconfirmed | ❌ Open |
