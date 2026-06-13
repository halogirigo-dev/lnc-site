# Booking Flow Audit
**Lombok Nature Culture — Public Booking Integration**
*Generated: 2026-06-14*

---

## 1. Files Audited

| File | Role |
|------|------|
| `booking.php` | Multi-step booking form (5 steps, JS-controlled) |
| `process-booking.php` | Form processor, validation, DB write, email dispatch |
| `thank-you.php` | Confirmation page with DB/session fallback |
| `db.php` | PDO connection helper + query functions |

---

## 2. booking.php — Form Analysis

### Form Setup
- **Method:** POST
- **Action:** `thank-you.php` (HTML attribute — overridden by JS in `main.js` to `process-booking.php`)
- **Encoding:** Default `application/x-www-form-urlencoded`
- **Structure:** 5-step wizard controlled by JavaScript. Steps rendered simultaneously in DOM; JS shows/hides.

### Form Fields

| Field Name | Type | Step | Required | Notes |
|-----------|------|------|----------|-------|
| `csrf_token` | hidden | — | implicit | Session-generated, rotated per submission |
| `website` | text (hidden via CSS) | — | — | Honeypot field for bot detection |
| `package` | radio | 1 | soft | Package code (e.g. `LNC-3D`, `LNC-7D`) |
| `dates` | text | 2 | — | Free-text preferred dates (e.g. "Aug 10–20, 2026") |
| `guests` | select | 2 | — | 1–10 numeric |
| `duration` | text | 2 | — | Free-text duration (e.g. "7 days") |
| `flexibility` | select | 2 | — | Fixed / ±1 week / Fully flexible |
| `accommodation` | radio | 2 | — | Eco (2–3★) / Comfort (3–4★) / Luxury (5★) |
| `name` | text | 3 | **YES** | Full name, min 2 chars |
| `email` | email | 3 | **YES** | Email address, validated format |
| `phone` | text | 3 | — | Phone/WhatsApp, validated format if provided |
| `country` | text | 3 | — | Country of residence |
| `nationality` | text | 3 | — | Nationality |
| `age_range` | select | 3 | — | 18–25 / 26–35 / 36–45 / 46–55 / 56+ |
| `source` | radio | 3 | — | Instagram / Google / TripAdvisor / Friend / Travel Agent / Other |
| `message` | textarea | 4 | — | Dream journey description |
| `special` | textarea | 4 | — | Special requirements / dietary / accessibility |
| `budget` | select | 4 | — | Budget range per person in IDR |

### Pre-fill Support
`booking.php` accepts URL query params: `?package=`, `?name=`, `?email=`, `?dates=`, `?guests=`, `?experience=`, `?message=` — all HTML-escaped before insertion into form fields.

---

## 3. process-booking.php — Submission Flow

### Security Layer (runs before any data collection)

```
POST received
  │
  ├─ [method check] POST only → else redirect booking.php
  ├─ [CSRF check]   SESSION['csrf_token'] == POST['csrf_token'] → else redirect booking.php?error=invalid_request
  ├─ [CSRF rotate]  New token issued immediately after check
  ├─ [honeypot]     POST['website'] non-empty → silent redirect thank-you.php?ref=LNC-BOT-BLOCKED
  └─ [rate limit]   >5 submissions per IP per session hour → redirect booking.php?error=invalid_request
```

### Input Sanitization
All values passed through `clean()`:
```php
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val ?? '')));
}
```

### Validation Rules

| Field | Rule |
|-------|------|
| `name` | Non-empty, `mb_strlen >= 2` |
| `email` | `filter_var(FILTER_VALIDATE_EMAIL)` |
| `phone` | Optional; if present: `/^[+\d\s\-().]{7,20}$/` |

### Data Collection
After validation, 18 fields collected into `$b[]` array. Package looked up from `$packages_short`, `$packages_long`, `$packages_bali` (from `data.php` — hardcoded arrays with DB-override support).

### Pricing Calculation
```
total   = package_price_per_pax × guests (min 1)
deposit = round(total × 0.30 / 1000) × 1000   ← rounded to nearest 1,000 IDR
balance = total − deposit
```
If no package price: total = deposit = balance = 0.

### Reference Generation (current)
```php
$year = date('Y');
$rand = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));
$ref  = "LNC-{$year}-{$rand}";
// Example: LNC-2026-A3F7B
```
**Issue:** Random 5-char hex, not sequential. Phase 4 changes this to `LNC-2026-00001`.

### Storage

#### Session (always, DB-less fallback)
```php
$_SESSION['lnc_booking'] = $b;  // Full booking array
```

#### Database (if DB connected)
1. **Upsert customer** — `ON CONFLICT (email) DO UPDATE` with `COALESCE` to preserve existing values
2. **Insert booking** — `status = 'new'`, `ON CONFLICT (ref) DO NOTHING`
3. **Insert status log** — `from_status = NULL`, `to_status = 'new'`, `changed_by = 'system'`

### Email Dispatch
1. **Admin notification** — plain text to `SITE_EMAIL`; includes full booking details + WhatsApp deep link
2. **Guest confirmation** — plain text to guest email; includes ref, package, dates, response timeline

### Final Redirect
```
Location: thank-you.php?ref={ref}&type=quote
```

---

## 4. thank-you.php — Confirmation Page

### Data Loading Priority
```
1. Try lnc_get_booking($ref) from PostgreSQL
2. Fall back to $_SESSION['lnc_booking'] if DB returns null
3. If still no booking → redirect booking.php
```

### Booking Variants Rendered

| Variant | Condition | Shows |
|---------|-----------|-------|
| `quote` | `type=quote` OR `status='quote'` OR `total_amount=0` | Ref, Package, Name |
| `deposit` | `status='deposit_paid'` | Ref, Package, Dates, Deposit paid, Balance due |
| `full` | `status in [balance_paid, confirmed]` | Ref, Package, Total paid |
| `pending` | Fallback | Ref only |

**Gap identified:** `quote` variant does not display `dates` or `guests` despite these being in `$booking`. Fixed in Phase 7.

### Session Usage in thank-you.php
```php
$_SESSION['lnc_booking']  // Read only — used as fallback when DB unavailable
```

---

## 5. db.php — Connection Layer

### `lnc_db()` — PDO Connection
- Returns `PDO|null` — null if DB credentials not configured or connection fails
- Static caching — connects once per PHP process
- PostgreSQL by default (`DB_CONNECTION = pgsql`), MySQL fallback available
- Error mode: `ERRMODE_EXCEPTION`
- Emulated prepares: **disabled** (uses native positional placeholders `$1`, `$2`)

### `lnc_get_booking(string $ref): ?array`
- Queries `bookings` table by `ref`
- Returns assoc array or null
- Used by `thank-you.php`, `invoice.php`, `payment-*.php`

### Gap: No sequential ref generation function
`lnc_generate_ref()` does not exist yet. Process-booking.php uses inline random generation. Fixed in Phase 4 — adding `lnc_generate_ref(PDO $db): string` to db.php.

---

## 6. Redirect Map

```
GET  booking.php                → render form
POST process-booking.php        → validate → DB → email → thank-you.php?ref=...&type=quote

Error redirects:
  invalid method                → booking.php
  CSRF fail / rate limit        → booking.php?error=invalid_request
  Honeypot                      → thank-you.php?ref=LNC-BOT-BLOCKED (silent)
  invalid name                  → booking.php?error=invalid_name
  invalid email                 → booking.php?error=invalid_email
  invalid phone                 → booking.php?error=invalid_phone
```

---

## 7. Identified Gaps (Pre-Integration)

| ID | Gap | Fix Phase |
|----|-----|-----------|
| G1 | Ref uses random 5-char hex, not sequential `LNC-2026-00001` | Phase 4 |
| G2 | `thank-you.php` quote variant omits `dates` and `guests` from display | Phase 7 |
| G3 | No `lnc_generate_ref()` function in `db.php` | Phase 4 |
| G4 | Laravel `Booking::generateRef()` also uses random format | Phase 4 |
| G5 | `thank-you.php` session fallback still sets `status = 'pending_payment'` (old value) | Phase 7 |

---

## 8. Data Flow Diagram

```
[Guest]
  │ submits booking.php (POST)
  ▼
[process-booking.php]
  │ validates + sanitizes
  │ generates ref (LNC-YYYY-NNNNN)
  ├─ writes $_SESSION['lnc_booking']
  ├─ if DB available:
  │   ├─ UPSERT customers (by email)
  │   ├─ INSERT bookings (status='new')
  │   └─ INSERT booking_status_logs (NULL → new)
  │ sends 2 emails
  └─ redirect →
  
[thank-you.php?ref=...&type=quote]
  │ loads booking: DB first, session fallback
  │ renders 'quote' variant
  │   shows: Ref, Package, Name, Dates, Guests
  └─ [Guest reads confirmation]
  
[Filament Admin]
  ← reads from same PostgreSQL DB
  ← sees booking in 'New' tab
  ← sees customer linked to booking
  ← sees status log: NULL → new
```
