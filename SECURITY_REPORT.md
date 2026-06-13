# SECURITY REPORT — Lombok Nature Culture
**Date:** 2026-06-13 (updated)  
**Scope:** Full security audit post-migration
**Previous:** Pre-migration audit (June 2026)

---

## Critical Findings

### 1. WordPress Database Credentials Exposed — CRITICAL
**File:** `public_html/wp-config.php`  
**Exposed:** DB_NAME, DB_USER, DB_PASSWORD, AUTH_KEY, all WordPress secret salts  
**Risk:** Any visitor could read raw PHP source if PHP execution failed; attackers scanning for wp-config.php could access the database directly.  
**Action Taken:** `wp-config.php` now blocked via `.htaccess` `RewriteRule`. **Recommended:** Delete `wp-config.php` and all WordPress files from the server entirely.

### 2. WordPress Admin Panel Exposed
**Paths:** `/wp-admin/`, `/wp-login.php`, `/xmlrpc.php`  
**Risk:** `wp-login.php` receives automated brute-force attacks continuously once indexed. `xmlrpc.php` enables DDoS amplification.  
**Action Taken:** All WordPress URLs blocked via `.htaccess`.  
**Recommended:** Delete entire WordPress installation from `public_html/`.

### 3. Autologin File
**File:** `public_html/create_autologin_ldqtx6y1zvlniijufvtyvvg68229jhqh.php`  
**Analysis:** Confirmed as Hostinger's legitimate auto-login tool. Script self-deletes via `unlink(__FILE__)` after first execution. However, if PHP execution has failed in the past, the file remains and is a security exposure.  
**Action Taken:** Blocked via `.htaccess` `<FilesMatch>` pattern. **Recommended:** Verify file is absent from live server; delete manually if present.

### 4. Backup File Publicly Readable
**File:** `public_html/single-lnc_package.php.bak`  
**Risk:** PHP files served as `.bak` are not executed — the raw PHP source code is returned to the browser, exposing all logic.  
**Action Taken:** `.bak` files blocked via `.htaccess` `<FilesMatch>`.

### 5. Missing CSRF Protection on All Forms — FIXED
**Files:** `booking.php`, `inquiry-cta.php`, `process-booking.php`  
**Risk:** Cross-Site Request Forgery — any malicious site could silently submit forms on behalf of a logged-in user.  
**Fix Applied:**
- `config.php` now starts session securely (HttpOnly, SameSite=Lax, Secure cookies)
- `booking.php` and `inquiry-cta.php` include `<input type="hidden" name="csrf_token">`
- `process-booking.php` validates CSRF token using `hash_equals()` before processing

### 6. Missing Server-Side Validation — FIXED
**File:** `process-booking.php`  
**Risk:** Submissions with invalid email or empty name would pass through and generate orphaned email notifications.  
**Fix Applied:** Added `filter_var(FILTER_VALIDATE_EMAIL)`, name length check, phone format regex. Redirects with error code on failure.

### 7. Trivial Content Security Policy — FIXED
**File:** `public_html/.htaccess`  
**Previous CSP:** `upgrade-insecure-requests` only (no actual content restrictions)  
**Risk:** No protection against XSS — inline scripts, external scripts, or framing from any origin were all permitted.  
**Fix Applied:** Full CSP header:
- `default-src 'self'`
- `script-src 'self' 'unsafe-inline' https://app.midtrans.com https://api.midtrans.com` (Midtrans payment SDK requires inline)
- `style-src 'self' 'unsafe-inline' https://fonts.googleapis.com`
- `font-src 'self' https://fonts.gstatic.com`
- `img-src 'self' data: https:`
- `connect-src 'self' https://api.midtrans.com https://app.midtrans.com`
- `frame-src https://app.midtrans.com`

### 8. Private Pages Publicly Indexed — FIXED
**Pages:** `thank-you.php`, `invoice.php`, `payment.php`, `payment-finish.php`, `payment-balance.php`  
**Risk:** Payment confirmation pages and invoice pages appearing in Google search results expose booking reference IDs to crawlers and potentially other users.  
**Fix Applied:** `<meta name="robots" content="noindex, nofollow">` added to all 5 pages. Corresponding URLs added to `robots.txt` disallow list.

### 9. Environment/Database Files Publicly Accessible — FIXED
**Files:** `env.php`, `db.php`, `create-tables.php`  
**Risk:** These files contain database credentials and schema. If PHP execution fails on the host, raw source would be returned.  
**Fix Applied:** Blocked via `.htaccess` `<Files>` directives. `*.env` and `.env.local` patterns also blocked.

---

## Security Improvements Applied

| Item | Status |
|---|---|
| Block all WordPress URLs via .htaccess | ✅ Applied |
| Block `.bak`, `.tmp`, `.log`, `.sql` files | ✅ Applied |
| Block `.private/` directory | ✅ Applied |
| Block `lnc_tmp/` directory | ✅ Applied |
| Block autologin file pattern | ✅ Applied |
| Block `env.php`, `db.php`, `create-tables.php` | ✅ Applied |
| Block `*.env` / `.env.local` patterns | ✅ Applied |
| Add CSRF tokens to booking form | ✅ Applied |
| Add CSRF tokens to inquiry form | ✅ Applied |
| CSRF validation in process-booking.php | ✅ Applied |
| Server-side input validation | ✅ Applied |
| Secure session cookie configuration | ✅ Applied |
| Full Content Security Policy | ✅ Applied |
| Security response headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, X-XSS-Protection, Permissions-Policy, XCPDP, COOP) | ✅ Applied (8 total) |
| noindex on payment/utility pages | ✅ Applied |
| Gzip compression enabled | ✅ Applied |
| Static asset caching (1 year for images/fonts) | ✅ Applied |

---

## Remaining Recommendations (Require Server Action)

1. **Delete all WordPress files** from `public_html/` — `wp-admin/`, `wp-content/`, `wp-includes/`, `wp-*.php`, `xmlrpc.php`, `readme.html`, `license.txt`
2. **Delete** `single-lnc_package.php.bak` from the server
3. **Verify deletion** of `create_autologin_*.php` (should have self-deleted)
4. **Enable HSTS** — The `Strict-Transport-Security` header is in `.htaccess` but commented out. Enable once HTTPS is confirmed stable: `Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"`
5. **Consider PHPMailer** or Hostinger SMTP for reliable, authenticated email delivery
6. **Rotate WordPress credentials** — The DB_PASSWORD in `wp-config.php` was publicly readable. If WordPress was actively used, rotate the DB user password.
